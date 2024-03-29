<?php
namespace Continental\Products\Helper;

use Magento\Framework\Registry;

/**
 * Custom Module Email helper
 */
class Accessories extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /***
     * @var Registry
     */
    protected $_registry;

    private $_product;

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Amasty\HidePrice\Helper\Data $helper,
        Registry $registry

    )
    {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_categoryFactory = $categoryFactory;
        $this->_listProduct = $listProduct;
        $this->_productRepository = $productRepository;
        $this->_resource = $resource;
        $this->_cart = $cartHelper;
        $this->_helper = $helper;
        $this->_registry = $registry;
    }

    /**
     * @return Product
     */
    private function getProduct()
    {
        if (is_null($this->_product)) {
            $this->_product = $this->_registry->registry('product');

            if (empty($this->_product)) {
                return null;
            }

            if (!$this->_product->getId()) {
                //throw new LocalizedException(__('Failed to initialize product'));
            }
        }

        return $this->_product;
    }

    public function setProduct()
    {
        return $this->getProduct();
    }

    /***
     * Gets Categories id for filtering
     * Note to future developer - This doesn't adhere to DRY principles but we're in plugin land so what the heck..
     * @return mixed
     */
    public function getCategoryId($categoryName) {
        $collection = $this->_categoryFactory->create()->getCollection()->addAttributeToFilter('name',$categoryName)->setPageSize(1);

        if ($collection->getSize()) {
            return $collection->getFirstItem()->getId();
        }
        return false;
    }

    public function getCategory($categoryName)
    {
        $categoryId = $this->getCategoryId($categoryName);
        $category = $this->_categoryFactory->create()->load($categoryId);
        return $category;
    }

    /***
     * Get a list of related products with spares stripped out.
     */
    public function getAccesories()
    {
        return $this->getProduct()->getRelatedProductCollection()
            ->addAttributeToSelect(
                [
                    'price',
                    'short_description',
                    'name',
                    'thumbnail',
                    'id'
                ]);
    }

    /***
     * @param $productId
     * @return mixed
     */
    public function getAddToCartUrl($productId)
    {
        $product = $this->_productRepository->getById($productId);
        return $this->_listProduct->getAddToCartUrl($product);

    }

    public function getProductByID($productId)
    {
        return $this->_productRepository->getById($productId);
    }

    public function formatPrice($str)
    {
        return number_format($str, 2);
    }



    /***
     * Checks basket to get most recently added product and retrieves product id
     * @return int
     */
    protected function getBasket()
    {
        $quote = $this->_cart->getQuote();

        $this->items = $quote->getAllVisibleItems();
    }

    protected function checkPoa()
    {
        $this->getBasket();

        if (count($this->items) > 0) {

            foreach ($this->items as $item) {
                //$item->getId(); // MaxId should be the last item added.
                //$item->getProductId();
                if ( $this->isPoa( $item->getProductId() ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /***
     * Checks if latest product is Poa
     * @return bool
     */
    protected function isPoa($productId) {
        // Get current product
        $product = $this->_product->getProductByID( $productId );
        return  $this->_helper->isNeedHideProduct( $product );
    }


}