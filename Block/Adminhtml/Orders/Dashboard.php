<?php
/**
 * Sudha_Mageshopifysync
 *
 * @category  Sudha
 * @package   Sudha_Mageshopifysync
 * @license   https://opensource.org/licenses/OSL-3.0
 */

declare(strict_types=1);

namespace Sudha\Mageshopifysync\Block\Adminhtml\Orders;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Psr\Log\LoggerInterface;
use Sudha\Mageshopifysync\Helper\Config;
use Sudha\Mageshopifysync\Model\ShopifyClient;

class Dashboard extends Template
{
    /**
     * @param Context $context
     * @param Config $config
     * @param ShopifyClient $shopifyClient
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly Config $config,
        private readonly ShopifyClient $shopifyClient,
        private readonly LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Return delayed orders for display in the admin dashboard.
     *
     * @return array
     */
    public function getDelayedOrders(): array
    {
        if (!$this->config->isEnabled()) {
            return [];
        }

        $thresholdDays = $this->config->getThresholdDays();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $delayed = [];

        try {
            $response = $this->shopifyClient->getOrders('shipped');
            $orders = $response['orders'] ?? [];

            foreach ($orders as $order) {
                foreach ($order['fulfillments'] ?? [] as $fulfillment) {
                    if (($fulfillment['shipment_status'] ?? '') !== 'in_transit') {
                        continue;
                    }
                    try {
                        $updatedDate = new \DateTime($fulfillment['updated_at']);
                        $daysInTransit = (int) $now->diff($updatedDate)->days;

                        if ($daysInTransit >= $thresholdDays) {
                            $delayed[] = [
                                'order_name' => $order['name'] ?? '',
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
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Sudha_Mageshopifysync Dashboard error: ' . $e->getMessage());
        }

        return $delayed;
    }

    /**
     * Get configured delay threshold in days.
     *
     * @return int
     */
    public function getThresholdDays(): int
    {
        return $this->config->getThresholdDays();
    }

    /**
     * Check if the module is enabled.
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Get URL to the module configuration page.
     *
     * @return string
     */
    public function getConfigUrl(): string
    {
        return $this->getUrl('adminhtml/system_config/edit', ['section' => 'sudha_mageshopifysync']);
    }
}
