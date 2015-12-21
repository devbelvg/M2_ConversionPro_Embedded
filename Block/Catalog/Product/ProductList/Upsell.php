<?php
/**
 * Celebros
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 ******************************************************************************
 * @category    Celebros
 * @package     Celebros_ConversionPro
 */
namespace Celebros\ConversionPro\Block\Catalog\Product\ProductList;

class Upsell extends \Magento\Catalog\Block\Product\ProductList\Upsell
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 4;
    protected $_items;
    protected $_itemCollection;
    protected $_confModel;
    protected $_prodModel;
    public $helper;
    public $crossSellHelper;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Celebros\ConversionPro\Helper\Data $conversionproHelper,
        \Celebros\ConversionPro\Helper\CrossSell $crossSellHelper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $confModel,
        \Magento\Catalog\Model\Product $prodModel,
        array $data = []
    ) {
        $this->helper = $conversionproHelper;
        $this->crossSellHelper = $crossSellHelper;
        $this->_confModel = $confModel;
        $this->_prodModel = $prodModel;
        parent::__construct(
            $context,
            $checkoutCart,
            $catalogProductVisibility,
            $checkoutSession,
            $moduleManager,
            $data
        );
    }
    
    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getItemCollection()
    {
        if (!$this->helper->isActiveEngine() || !$this->helper->isUpsellEnabled()) {
            return parent::getItemCollection();
        }
        
        $items = $this->_items;
        if (is_null($items)) {
        
            reset($this->_itemCollection);
        
            $lastAdded = null;
        
            //This code path covers the 2 cases - product page and shopping cart
            if($this->getProduct()!=null){
                $lastAdded = $this->getProduct()->getId();
            }
            else{
                $cartProductIds = $this->_getCartProductIds();
                $lastAdded = null;
                for($i=count($cartProductIds) -1; $i >=0 ; $i--){
                    $id =  $cartProductIds[$i];
                    $parentIds = $this->_confModel->getParentIdsByChild($id);
                    if(empty($parentIds)){
                        $lastAdded = $id;
                        break;
                    }
                }
            }
            
            
            
            $crossSellIds = $this->crossSellHelper->getRecommendationsIds($lastAdded);
            
            $this->_maxItemCount = $this->helper->getCrosssellLimit();
            
            $this->_itemCollection = $this->_getCollection()
                ->addAttributeToFilter('entity_id', array('in' => $crossSellIds,));
        }
        
        return $this->_itemCollection;
    }
    
    /**
     * Get crosssell products collection
     */
    protected function _getCollection()
    {
        $collection = $this->_prodModel
            ->getCollection()
            ->addStoreFilter()
            ->setPageSize($this->_maxItemCount);
        $this->_addProductAttributesAndPrices($collection);
        
        /*Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);*/
        
        return $collection;
    }
}
