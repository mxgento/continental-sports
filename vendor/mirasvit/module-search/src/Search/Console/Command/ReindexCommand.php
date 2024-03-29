<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.42
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State as AppState;

class ReindexCommand extends Command
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        IndexRepositoryInterface $indexRepository,
        AppState $appState,
        Registry $registry,
        ObjectManagerInterface $objectManager
    ) {
        $this->indexRepository = $indexRepository;
        $this->appState = $appState;
        $this->registry = $registry;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:search:reindex')
            ->setDescription('Reindex all search indexes')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ts = microtime(true);

        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $this->registry->register('output', $output); #used for visualize reindex progress in other classes

        $collection = $this->indexRepository->getCollection()
            ->addFieldToFilter('is_active', 1);

        /** @var IndexInterface $index */
        foreach ($collection as $index) {
            $output->write($index->getTitle() . ' [' . $index->getIdentifier() . ']....');

            try {
                $this->indexRepository->getInstance($index)->reindexAll();
                $output->writeln("<info>Done</info>");
            } catch (\Exception $e) {
                echo $e;
            }
        }

        echo PHP_EOL . round(microtime(true) - $ts, 0) . ' seconds' . PHP_EOL;
    }
}
