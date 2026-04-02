<?php
/**
 * Sudha_Mageshopifysync
 *
 * @category  Sudha
 * @package   Sudha_Mageshopifysync
 * @license   https://opensource.org/licenses/OSL-3.0
 */

declare(strict_types=1);

namespace Sudha\Mageshopifysync\Cron;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;
use Sudha\Mageshopifysync\Helper\Config;
use Sudha\Mageshopifysync\Model\AlertSender;
use Sudha\Mageshopifysync\Model\ShopifyClient;

class CheckDelayedOrders
{
    /**
     * @param Config $config
     * @param ShopifyClient $shopifyClient
     * @param AlertSender $alertSender
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly ShopifyClient $shopifyClient,
        private readonly AlertSender $alertSender,
        private readonly State $appState,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Main cron entry point.
     *
     * Fetches Shopify orders in transit, finds those exceeding the threshold,
     * and sends an admin alert email.
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        try {
            $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, function () {
                $this->runCheck();
            });
        } catch (\Exception $e) {
            $this->logger->error('Sudha_Mageshopifysync cron failed: ' . $e->getMessage());
        }
    }

    /**
     * Core logic separated so it runs cleanly inside emulateAreaCode.
     *
     * @return void
     */
    private function runCheck(): void
    {
        $thresholdDays = $this->config->getThresholdDays();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $delayedOrders = [];

        $this->logger->info('Sudha_Mageshopifysync: Starting delayed order check.', [
            'threshold_days' => $thresholdDays,
        ]);

        try {
            $response = $this->shopifyClient->getOrders('shipped');
            $orders = $response['orders'] ?? [];
        } catch (\Exception $e) {
            $this->logger->error('Sudha_Mageshopifysync: Could not fetch orders from Shopify.', [
                'exception' => $e->getMessage(),
            ]);
            return;
        }

        foreach ($orders as $order) {
            foreach ($order['fulfillments'] ?? [] as $fulfillment) {
                if (($fulfillment['shipment_status'] ?? '') !== 'in_transit') {
                    continue;
                }

                $updatedAt = $fulfillment['updated_at'] ?? null;
                if (!$updatedAt) {
                    continue;
                }

                try {
                    $updatedDate = new \DateTime($updatedAt);
                } catch (\Exception $e) {
                    continue;
                }

                $daysInTransit = (int) $now->diff($updatedDate)->days;

                if ($daysInTransit >= $thresholdDays) {
                    $delayedOrders[] = [
                        'order_id' => (string) ($order['id'] ?? ''),
                        'order_name' => (string) ($order['name'] ?? ''),
                        'customer_name' => trim(
                            ($order['customer']['first_name'] ?? '') . ' ' .
                            ($order['customer']['last_name'] ?? '')
                        ),
                        'days_in_transit' => $daysInTransit,
                        'tracking_number' => implode(
                            ', ',
                            $fulfillment['tracking_numbers'] ?? []
                        ),
                    ];

                    $this->logger->info('Sudha_Mageshopifysync: Delayed order found.', [
                        'order_name' => $order['name'] ?? '',
                        'days_in_transit' => $daysInTransit,
                    ]);
                }
            }
        }

        $this->logger->info('Sudha_Mageshopifysync: Check complete.', [
            'total_orders' => count($orders),
            'delayed_count' => count($delayedOrders),
        ]);

        if (!empty($delayedOrders)) {
            try {
                $this->alertSender->send($delayedOrders);
            } catch (\Exception $e) {
                $this->logger->error('Sudha_Mageshopifysync: Alert send failed.', [
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }
}
