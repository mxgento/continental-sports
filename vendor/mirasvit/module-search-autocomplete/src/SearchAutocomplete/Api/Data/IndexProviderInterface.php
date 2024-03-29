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


namespace Mirasvit\SearchAutocomplete\Api\Data;

interface IndexProviderInterface
{
    /**
     * @return IndexInterface[]
     */
    public function getIndices();

    /**
     * @param IndexInterface $index
     * @return $this
     */
    public function getCollection($index);

    /**
     * @param IndexInterface $index
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse($index);
}