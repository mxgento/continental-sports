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
 * @package   mirasvit/module-kb
 * @version   1.0.41
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Kb\Controller\Adminhtml\Article;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Mirasvit\Kb\Controller\Adminhtml\Article
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        //@todo remove all logic and add forward to edit

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initModel();

        $this->initPage($resultPage)->getConfig()->getTitle()
            ->prepend(__('New Article'));

        $this->_addContent($resultPage->getLayout()->createBlock('Mirasvit\Kb\Block\Adminhtml\Article\Edit'))
            ->_addLeft($resultPage->getLayout()->createBlock('Mirasvit\Kb\Block\Adminhtml\Article\Edit\Tabs'));

        return $resultPage;
    }
}
