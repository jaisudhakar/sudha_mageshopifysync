# Sudha_Mageshopifysync

**Magento 2 module** — Sync Shopify order fulfilment data and alert the admin when shipments are delayed beyond a configurable threshold.

---

## Features

- Connects to your Shopify store via the Admin REST API
- Cron job runs daily (configurable) to check all in-transit Shopify orders
- Sends an admin alert email listing all orders delayed beyond your threshold
- Admin dashboard under **Stores → Mage Shopify Sync → Delayed Orders**
- All configuration in **Stores → Configuration → Sudha Extensions → Mage Shopify Sync**
- API secret stored encrypted in the database
- Manual trigger via CLI: `php bin/magento sudha:mageshopifysync:check`

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.1 / 8.2 / 8.3 |
| Magento Open Source / Adobe Commerce | 2.4.x |

---

## Installation

### Via Composer (recommended)

```bash
composer require sudha/module-mageshopifysync
php bin/magento module:enable Sudha_Mageshopifysync
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

### Manual installation

1. Copy the `Sudha/Mageshopifysync` folder to `app/code/Sudha/Mageshopifysync/`
2. Run:

```bash
php bin/magento module:enable Sudha_Mageshopifysync
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

---

## Configuration

1. Go to **Stores → Configuration → Sudha Extensions → Mage Shopify Sync**
2. Under **Shopify API Settings**, enter:
   - **Shopify Store URL** — e.g. `your-store.myshopify.com`
   - **API Key** — your Shopify Admin API access token
3. Under **Delay Alert Settings**, set:
   - **Delay Threshold (Days)** — how many days in transit before alerting (default: 5)
   - **Alert Email Address** — where to send the alert (defaults to store admin email)
4. Save configuration

---

## Usage

### Automatic (cron)

The module runs automatically once per day at 8:00 AM (configurable). No action needed after setup.

### Manual trigger (CLI)

```bash
php bin/magento sudha:mageshopifysync:check
```

### Admin dashboard

Navigate to **Stores → Mage Shopify Sync → Delayed Orders** to see all currently delayed shipments in real time.

---

## Uninstallation

```bash
php bin/magento module:disable Sudha_Mageshopifysync
php bin/magento setup:upgrade
php bin/magento cache:flush
composer remove sudha/module-mageshopifysync
```

---

## Support

For issues or questions, please contact: support@sudha.com

---

## License

Open Software License (OSL 3.0) — see https://opensource.org/licenses/OSL-3.0
