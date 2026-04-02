<?php
/**
 * Sudha_Mageshopifysync
 *
 * @category  Sudha
 * @package   Sudha_Mageshopifysync
 * @license   https://opensource.org/licenses/OSL-3.0
 */

declare(strict_types=1);

namespace Sudha\Mageshopifysync\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    private const XML_PATH_ENABLED = 'sudha_mageshopifysync/general/enabled';
    private const XML_PATH_STORE_URL = 'sudha_mageshopifysync/shopify/store_url';
    private const XML_PATH_API_KEY = 'sudha_mageshopifysync/shopify/api_key';
    private const XML_PATH_API_SECRET = 'sudha_mageshopifysync/shopify/api_secret';
    private const XML_PATH_API_VERSION = 'sudha_mageshopifysync/shopify/api_version';
    private const XML_PATH_THRESHOLD_DAYS = 'sudha_mageshopifysync/alert/threshold_days';
    private const XML_PATH_ALERT_EMAIL = 'sudha_mageshopifysync/alert/alert_email';
    private const XML_PATH_EMAIL_TEMPLATE = 'sudha_mageshopifysync/alert/email_template';

    /**
     * Check if the module is enabled.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isEnabled(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * Get the configured Shopify store URL.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getStoreUrl(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_STORE_URL, $scopeType, $scopeCode);
    }

    /**
     * Get the Shopify API key (access token).
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getApiKey(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_API_KEY, $scopeType, $scopeCode);
    }

    /**
     * Get the Shopify API secret (encrypted).
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getApiSecret(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_API_SECRET, $scopeType, $scopeCode);
    }

    /**
     * Get the Shopify API version string.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getApiVersion(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_API_VERSION,
            $scopeType,
            $scopeCode
        ) ?: '2024-01';
    }

    /**
     * Get the configured delay alert threshold in days.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return int
     */
    public function getThresholdDays(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): int {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_THRESHOLD_DAYS,
            $scopeType,
            $scopeCode
        ) ?: 5;
    }

    /**
     * Get the configured alert email address.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getAlertEmail(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_ALERT_EMAIL, $scopeType, $scopeCode);
    }

    /**
     * Get the email template identifier.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getEmailTemplate(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $scopeType, $scopeCode);
    }

    /**
     * Build the Shopify Admin API base URL.
     *
     * @return string
     */
    public function getShopifyApiBaseUrl(): string
    {
        $storeUrl = rtrim($this->getStoreUrl(), '/');
        $version = $this->getApiVersion();
        return 'https://' . $storeUrl . '/admin/api/' . $version;
    }
}
