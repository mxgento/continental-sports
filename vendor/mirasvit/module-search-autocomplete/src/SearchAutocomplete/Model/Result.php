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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.17
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\SearchAutocomplete\Model;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\QueryFactory;
use Mirasvit\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;

/**
 * Class Result
 */
class Result
{
    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Search\Model\Query
     */
    private $query;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var bool
     */
    private static $isLayerCreated = false;

    public function __construct(
        IndexRepositoryInterface $indexRepository,
        LayerResolver $layerResolver,
        QueryFactory $queryFactory,
        Config $config,
        SearchHelper $searchHelper
    ) {
        $this->indexRepository = $indexRepository;
        $this->layerResolver = $layerResolver;
        $this->queryFactory = $queryFactory;
        $this->config = $config;
        $this->searchHelper = $searchHelper;
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->query = $this->queryFactory->get();
        if (!self::$isLayerCreated) {
            try {
                $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
            } catch (\Exception $e) {
            } finally {
                self::$isLayerCreated = true;
            }
        }
    }

    /**
     * Convert all results to array
     *
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    public function toArray()
    {
        \Magento\Framework\Profiler::start(__METHOD__);

        $result = [
            'totalItems' => 0,
            'query'      => $this->query->getQueryText(),
            'indices'    => [],
            'noResults'  => false,
            'urlAll'     => $this->searchHelper->getResultUrl($this->query->getQueryText()),
        ];

        foreach ($this->indexRepository->getIndices() as $index) {
            $identifier = $index->getIdentifier();

            if (!$this->config->getIndexOptionValue($identifier, 'is_active')) {
                continue;
            }

            $index->addData($this->config->getIndexOptions($identifier));

            $instance = $this->indexRepository->getInstance($identifier);
            $instance->setIndex($index)
                ->setLimit($this->config->getIndexOptionValue($identifier, 'limit'))
                ->setRepository($this->indexRepository);

            $items = [
                'identifier'   => $identifier == 'catalogsearch_fulltext' ? 'magento_catalog_product' : $identifier,
                'title'        => (string)__($index->getTitle()),
                'order'        => (int)$this->config->getIndexOptionValue($identifier, 'order'),
                'items'        => $instance->getItems(),
                'totalItems'   => $instance->getSize(),
                'isShowTotals' => $identifier == 'magento_search_query' ? false : true,
            ];

            $result['indices'][] = $items;

            $result['totalItems'] += $instance->getSize();
        }

        usort($result['indices'], function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $result['textAll'] = __('Show all %1 results →', $result['totalItems']);
        $result['textEmpty'] = __('Sorry, nothing found for "%1".', $result['query']);

        $result['noResults'] = $result['totalItems'] ? false : true;

        \Magento\Framework\Profiler::stop(__METHOD__);
        return $result;
    }
}
