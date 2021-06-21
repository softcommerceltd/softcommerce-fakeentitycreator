<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use SoftCommerce\FakeEntityCreator\Model\InvoiceServiceInterfaceFactory;
use SoftCommerce\FakeEntityCreator\Model\OrderServiceInterfaceFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateOrder used to create
 * fake order entity.
 */
class CreateOrder extends Command
{
    private const COMMAND_NAME = 'fakeentitycreator:create_fake_order';
    private const COUNTER_FLAG = 'counter';
    private const INVOICE_FLAG = 'invoice';

    /**
     * @var State
     */
    private $appState;

    /**
     * @var OrderServiceInterfaceFactory
     */
    private $orderServiceFactory;

    /**
     * @var InvoiceServiceInterfaceFactory
     */
    private $invoiceServiceFactory;

    /**
     * CreateOrder constructor.
     * @param State $appState
     * @param OrderServiceInterfaceFactory $orderServiceFactory
     * @param InvoiceServiceInterfaceFactory $invoiceServiceFactory
     * @param string|null $name
     */
    public function __construct(
        State $appState,
        OrderServiceInterfaceFactory $orderServiceFactory,
        InvoiceServiceInterfaceFactory $invoiceServiceFactory,
        ?string $name = null
    ) {
        $this->appState = $appState;
        $this->orderServiceFactory = $orderServiceFactory;
        $this->invoiceServiceFactory = $invoiceServiceFactory;
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Create Fake Order')
            ->setDefinition([
                new InputOption(
                    self::COUNTER_FLAG,
                    '-c',
                    InputOption::VALUE_REQUIRED,
                    'Count Filter'
                ),
                new InputOption(
                    self::INVOICE_FLAG,
                    '-i',
                    InputOption::VALUE_NONE,
                    'Status Filter'
                )
            ]);

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        $orderService = $this->orderServiceFactory->create();
        $invoiceService = $this->invoiceServiceFactory->create();

        $count = $input->getOption(self::COUNTER_FLAG) ?: 1;
        for ($i=1; $i <= $count; $i++) {
            try {
                $orderService->execute();
                if (!$order = $orderService->getOrder()) {
                    continue;
                }

                $output->writeln(sprintf('<info>New Order: # %s.</info>', $order->getIncrementId()));
                if (!$input->getOption(self::INVOICE_FLAG)) {
                    continue;
                }

                $invoiceService->execute($order);
                if ($invoice = $invoiceService->getInvoice()) {
                    $output->writeln(sprintf('<info>New Invoice: # %s.</info>', $invoice->getIncrementId()));
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }
        $output->writeln(sprintf('<info>A total of %s orders have been created.</info>', $count));
        return Cli::RETURN_SUCCESS;
    }
}
