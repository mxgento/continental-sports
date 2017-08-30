<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_HidePrice
 */


namespace Amasty\HidePrice\Model\Source;

class PriceMode implements \Magento\Framework\Option\ArrayInterface
{
    const HIDE = 0;
    const SHOW = 1;
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::HIDE,
                'label' => __('Hide')
            ],
            [
                'value' => self::SHOW,
                'label' => __('Show')
            ]
        ];

        return $options;
    }
}
