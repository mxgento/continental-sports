<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_HidePrice
 */


namespace Amasty\HidePrice\Controller\Request;

use Amasty\HidePrice\Model\Request;
use Magento\Framework\Exception\LocalizedException;

class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;
    /**
     * @var \Amasty\HidePrice\Model\RequestFactory
     */
    private $requestFactory;
    /**
     * @var \Amasty\HidePrice\Model\RequestRepository
     */
    private $requestRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;
    /**
     * @var \Amasty\HidePrice\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\Data\Form\Filter\Escapehtml
     */
    private $escapehtml;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Amasty\HidePrice\Model\RequestFactory $requestFactory,
        \Amasty\HidePrice\Model\RequestRepository $requestRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Amasty\HidePrice\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Form\Filter\Escapehtml $escapehtml
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->requestFactory = $requestFactory;
        $this->requestRepository = $requestRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->escapehtml = $escapehtml;
    }

    public function execute()
    {
        $message = [
            'error' => __('Sorry. There is a problem with Your Quote Request.'.
                ' Please try again or use Contact Us link in the menu.'
            )
        ];
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getValidData();

                /** @var  Request $model */
                $model = $this->requestFactory->create();
                $model->addData($data);
                $this->requestRepository->save($model);
                $message = ['success' => __('Thanks for contacting us. We\'ll respond to you as soon as possible. ')];

                $this->sendAdminNotification($model);
                $this->sendAutoReply($model);

            } catch (LocalizedException $e) {
                $message = ['error' => $e->getMessage()];
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        $this->getResponse()->representJson(
            $this->jsonEncoder->encode($message)
        );
    }

    /**
     * Validates all data
     *
     * @throws LocalizedException
     * @return array
     */
    private function getValidData()
    {
        $data = $this->getRequest()->getPostValue();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Form key is not valid. Please try to reload the page.')
            );
        }

        if (!\Zend_Validate::is($data['email'], 'EmailAddress')) {
            throw new LocalizedException(__('Please enter a valid email address.'));
        }

        if (!\Zend_Validate::is(trim($data['name']), 'NotEmpty')) {
            throw new LocalizedException(__('Please enter a name.'));
        }

        if (!\Zend_Validate::is(trim($data['phone']), 'NotEmpty')) {
            throw new LocalizedException(__('Please enter a phone.'));
        }

        if (!\Zend_Validate::is(trim($data['product_id']), 'NotEmpty')
           || !($product = $this->productRepository->getById($data['product_id']))
        ) {
            throw new LocalizedException(__('There are no product for your request.'));
        } else {
            $data['product_name'] = $product->getName();
        }

        if (array_key_exists('comment', $data)) {
            $data['comment'] = $this->escapehtml->outputFilter(trim($data['comment']));
        }

        $data['store_id'] = $this->storeManager->getStore()->getId();

        return $data;
    }

    private function sendAdminNotification(Request $model)
    {
        $emailTo = trim($this->helper->getModuleConfig('admin_email/to'));
        if ($emailTo) {
            $sender = $this->helper->getModuleConfig('admin_email/sender');
            $template = $this->helper->getModuleConfig('admin_email/template');
            $this->sendEmail($model, $sender, $emailTo, $template);
        }
    }

    private function sendAutoReply(Request $model)
    {
        $enabled = $this->helper->getModuleConfig('reply_email/enabled');
        if ($enabled) {
            $emailTo = $model->getEmail();
            $sender = $this->helper->getModuleConfig('reply_email/sender');
            $template = $this->helper->getModuleConfig('reply_email/template');
            $this->sendEmail($model, $sender, $emailTo, $template);
        }
    }

    private function sendEmail(Request $model, $sender, $emailTo, $template)
    {
        try {
            $store = $this->storeManager->getStore();
            $data =  [
                'website_name'  => $store->getWebsite()->getName(),
                'group_name'    => $store->getGroup()->getName(),
                'store_name'    => $store->getName(),
                'request'       => $model,
                'customer_name' => $model->getName()
            ];

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
            )->setTemplateVars(
                $data
            )->setFrom(
                $sender
            )->addTo(
                $emailTo,
                $model->getName()
            )->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
