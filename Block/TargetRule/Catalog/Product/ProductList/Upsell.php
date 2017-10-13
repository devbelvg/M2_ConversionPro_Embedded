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
namespace Celebros\ConversionPro\Block\TargetRule\Catalog\Product\ProductList;

class Upsell extends \Magento\TargetRule\Block\Catalog\Product\ProductList\Upsell
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\TargetRule\Model\ResourceModel\Index $index
     * @param \Magento\TargetRule\Helper\Data $targetRuleData
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\TargetRule\Model\IndexFactory $indexFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\TargetRule\Model\ResourceModel\Index $index,
        \Magento\TargetRule\Helper\Data $targetRuleData,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\TargetRule\Model\IndexFactory $indexFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Celebros\ConversionPro\Helper\Data $conversionproHelper,
        \Celebros\ConversionPro\Helper\CrossSell $crossSellHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $index,
            $targetRuleData,
            $productCollectionFactory,
            $visibility,
            $indexFactory,
            $cart,
            $data
        );
        $this->_celHelper = $conversionproHelper;
        $this->_upsellHelper = $crossSellHelper;
    }
    
    public function getItemCollection()
    {
        $recSkus = $this->_upsellHelper->getRecommendationsSkus($this->getProduct()->getSku());
        if ($recSkus) {
            $recSkus = array_slice($recSkus, 0, $this->_celHelper->getCrosssellLimit());          
            $collection = $this->_productCollectionFactory->create();
            $collection->addFieldToFilter('sku', $recSkus)->addAttributeToSelect('*');
            $this->_addProductAttributesAndPrices($collection);
            $collection->addStoreFilter()
                ->setPageSize($this->_celHelper->getCrosssellLimit()); 

            return $collection->getItems();
        }

        return [];    
    }
}
