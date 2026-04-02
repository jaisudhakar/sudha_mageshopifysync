<?php
/**
 * Sudha_Mageshopifysync
 *
 * @category  Sudha
 * @package   Sudha_Mageshopifysync
 * @license   https://opensource.org/licenses/OSL-3.0
 */

declare(strict_types=1);

namespace Sudha\Mageshopifysync\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Sudha\Mageshopifysync\Helper\Config;

class AlertSender
{
    /**
     * @param Config $config
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface $inlineTranslation,
        private readonly StoreManagerInterface $storeManager,
        private readonly Escaper $escaper,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Send a delayed shipment alert email.
     *
     * @param array $delayedOrders Each item: ['order_name', 'order_id', 'days_in_transit',
     *                                          'tracking_number', 'customer_name']
     * @return void
     * @throws LocalizedException
     */
    public function send(array $delayedOrders): void
    {
        if (empty($delayedOrders)) {
            return;
        }

        $store = $this->storeManager->getStore();
        $alertEmail = $this->config->getAlertEmail()
            ?: $store->getConfig('trans_email/ident_general/email');
        $storeName = $store->getName();
        $threshold = $this->config->getThresholdDays();

        $orderRowsHtml = '';
        foreach ($delayedOrders as $order) {
            $orderName = $this->escaper->escapeHtml((string) ($order['order_name'] ?? ''));
            $customerName = $this->escaper->escapeHtml((string) ($order['customer_name'] ?? ''));
            $trackingNumber = $this->escaper->escapeHtml((string) ($order['tracking_number'] ?? 'N/A'));
            $daysInTransit = (int) ($order['days_in_transit'] ?? 0);

            $orderRowsHtml .= '<tr>'
                . '<td style="padding:8px;border:1px solid #ddd;">' . $orderName . '</td>'
                . '<td style="padding:8px;border:1px solid #ddd;">' . $customerName . '</td>'
                . '<td style="padding:8px;border:1px solid #ddd;">' . $daysInTransit . ' days</td>'
                . '<td style="padding:8px;border:1px solid #ddd;">' . $trackingNumber . '</td>'
                . '</tr>';
        }

        $this->inlineTranslation->suspend();

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->config->getEmailTemplate())
                ->setTemplateOptions([
                    'area' => Area::AREA_ADMINHTML,
                    'store' => Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars([
                    'order_rows_html' => $orderRowsHtml,
                    'total_delayed' => count($delayedOrders),
                    'threshold_days' => $threshold,
                    'store_name' => $storeName,
                ])
                ->setFromByScope('general')
                ->addTo($alertEmail)
                ->getTransport();

            $transport->sendMessage();

            $this->logger->info('Sudha_Mageshopifysync: Alert email sent.', [
                'to' => $alertEmail,
                'delayed_orders' => count($delayedOrders),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Sudha_Mageshopifysync: Failed to send alert email.', [
                'exception' => $e->getMessage(),
            ]);
            throw new LocalizedException(__('Failed to send alert email: %1', $e->getMessage()));
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
