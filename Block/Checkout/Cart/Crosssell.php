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
namespace Celebros\ConversionPro\Block\Checkout\Cart; 
 
class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 4;
    protected $_confModel;
    public $helper;
    public $crossSellHelper;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory,
        \Magento\Quote\Model\Quote\Item\RelatedProducts $itemRelationsList,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Celebros\ConversionPro\Helper\Data $conversionproHelper,
        \Celebros\ConversionPro\Helper\CrossSell $crossSellHelper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $confModel,
        array $data = []
    ) {
        $this->helper = $conversionproHelper;
        $this->crossSellHelper = $crossSellHelper;
        $this->_confModel = $confModel;
        
        parent::__construct(
            $context,
            $checkoutSession,
            $productVisibility,
            $productLinkFactory,
            $itemRelationsList,
            $stockHelper,
            $data
        );
    }
    
    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getItems()
    {
        if (!$this->helper->isActiveEngine()
            || !$this->helper->isCrosssellEnabled()) {
            
            return parent::getItems();
        }
        
        $items = $this->getData('items');
        if (is_null($items)) {
            $lastAdded = null;
            
            //This code path covers the 2 cases - product page and shopping cart
            if ($this->getProduct() != null) {
                $lastAdded = $this->getProduct()->getId();
            } else {
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
            
            $items = $this->_getCollection()
                ->addAttributeToFilter('entity_id', array('in' => $crossSellIds,));
        }
        
        $this->setData('items', $items);
        $this->_itemCollection = $items;
        return $items;
    }
    
}