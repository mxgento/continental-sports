<?php
namespace Continental\Banners\Block\Adminhtml\System\Config;

class Checkbox extends \Magento\Framework\View\Element\AbstractBlock {

    protected function _toHtml()
    {
        $elId = $this->getInputId();
        $elName = $this->getInputName();
        $colName = $this->getColumnName();
        $column = $this->getColumn();

        return '<input type="checkbox" value="1" id="' . $elId . '"' .
        ' name="' . $elName . '" <%- ' . $colName . ' %> ' .
        ($column['size'] ? 'size="' . $column['size'] . '"' : '') .
        ' class="' .
        (isset($column['class']) ? $column['class'] : 'input-text') . '"' .
        (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
    }
}

/*
namespace Continental\Banners\Block\Adminhtml\System\Config;

use Magento\Framework\Pricing\Render\Layout;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;

class Checkbox extends \Magento\Framework\View\Element\AbstractBlock
{
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
            parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        $elId = $this->getInputId();
        $elName = $this->getInputName();
        $colName = $this->getColumnName();
        $column = $this->getColumn();

        return '<input type="checkbox" value="1" id="' . $elId . '"' .
        ' name="' . $elName . '" <%- ' . $colName . ' %> ' .
        ($column['size'] ? 'size="' . $column['size'] . '"' : '') .
        ' class="' .
        (isset($column['class']) ? $column['class'] : 'input-text') . '"' .
        (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
    }
}
*/
