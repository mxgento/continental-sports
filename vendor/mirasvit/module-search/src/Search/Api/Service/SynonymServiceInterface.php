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



namespace Mirasvit\Search\Api\Service;

use Mirasvit\Search\Api\Data\SynonymInterface;

interface SynonymServiceInterface
{
    /**
     * @param array $terms
     * @param int $storeId
     * @return array
     */
    public function getSynonyms(array $terms, $storeId);

    /**
     * @param $storeId
     * @return SynonymInterface[]
     */
    public function getComplexSynonyms($storeId);

    /**
     * @param string $file
     * @param int|array $storeIds
     * @return \Generator
     */
    public function import($file, $storeIds);
}