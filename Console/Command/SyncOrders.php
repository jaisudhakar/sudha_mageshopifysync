<?php
/**
 * Sudha_Mageshopifysync
 *
 * @category  Sudha
 * @package   Sudha_Mageshopifysync
 * @license   https://opensource.org/licenses/OSL-3.0
 */

declare(strict_types=1);

namespace Sudha\Mageshopifysync\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sudha\Mageshopifysync\Cron\CheckDelayedOrders;

class SyncOrders extends Command
{
    /**
     * @param CheckDelayedOrders $checkDelayedOrders
     */
    public function __construct(
        private readonly CheckDelayedOrders $checkDelayedOrders
    ) {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('sudha:mageshopifysync:check')
             ->setDescription(
                 'Manually trigger the Shopify delayed shipment check and send alert email.'
             );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Sudha_Mageshopifysync: Running delayed order check...</info>');

        try {
            $this->checkDelayedOrders->execute();
            $output->writeln('<info>Done. Check your configured alert email.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
