<?php
/**
 * Sudha_Mageshopifysync
 *
 * @category  Sudha
 * @package   Sudha_Mageshopifysync
 * @license   https://opensource.org/licenses/OSL-3.0
 */

declare(strict_types=1);

namespace Sudha\Mageshopifysync\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallDefaultConfig implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(): self
    {
        // No database tables required — module uses core_config_data only.
        // Placeholder for future data migrations.
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
