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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Sudha\Mageshopifysync\Helper\Config;

class ShopifyClient
{
    /**
     * @param Config $config
     * @param Curl $curl
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Fetch all orders with a given fulfillment status from Shopify.
     *
     * @param string $fulfillmentStatus
     * @param int $limit
     * @return array
     * @throws LocalizedException
     */
    public function getOrders(string $fulfillmentStatus = 'shipped', int $limit = 250): array
    {
        $endpoint = $this->config->getShopifyApiBaseUrl()
            . '/orders.json?fulfillment_status=' . $fulfillmentStatus
            . '&limit=' . $limit . '&status=any';

        return $this->get($endpoint);
    }

    /**
     * Fetch fulfillments for a specific order.
     *
     * @param string $orderId
     * @return array
     * @throws LocalizedException
     */
    public function getFulfillments(string $orderId): array
    {
        $endpoint = $this->config->getShopifyApiBaseUrl()
            . '/orders/' . $orderId . '/fulfillments.json';

        return $this->get($endpoint);
    }

    /**
     * Execute a GET request using Magento's HTTP client.
     *
     * A new Curl instance is injected per request to avoid header-corruption
     * across multiple sequential calls.
     *
     * @param string $url
     * @return array
     * @throws LocalizedException
     */
    private function get(string $url): array
    {
        $apiKey = $this->config->getApiKey();

        try {
            $this->curl->addHeader('X-Shopify-Access-Token', $apiKey);
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->addHeader('Accept', 'application/json');
            $this->curl->setTimeout(30);
            $this->curl->get($url);

            $httpCode = $this->curl->getStatus();
            $response = $this->curl->getBody();
        } catch (\Exception $e) {
            $this->logger->error('Sudha_Mageshopifysync HTTP error: ' . $e->getMessage(), [
                'url' => $url,
            ]);
            throw new LocalizedException(__('Shopify API connection failed: %1', $e->getMessage()));
        }

        if ($httpCode !== 200) {
            $this->logger->error('Sudha_Mageshopifysync API error', [
                'http_code' => $httpCode,
                'url' => $url,
            ]);
            throw new LocalizedException(__('Shopify API returned HTTP %1', $httpCode));
        }

        $data = json_decode((string) $response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LocalizedException(__('Failed to parse Shopify API response.'));
        }

        return $data;
    }
}
