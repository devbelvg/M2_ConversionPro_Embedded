<?php
/*
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
namespace Celebros\ConversionPro\Plugin;

use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Celebros\ConversionPro\Helper\Data as Helper;

class Collection
{
    /**
     * @param \Celebros\ConversionPro\Helper\Data $helper
     * @param \Magento\Catalog\Model\Category $catModel
     * @return void
     */
    public function __construct(
        Helper $helper,
        \Magento\Catalog\Model\Category $catModel
    ) {
        $this->helper = $helper;
        $this->catModel = $catModel;
    }
    
    public function beforeAddCategoryFilter(
        ProductCollection $collection,
        \Magento\Catalog\Model\Category $category
    ) {
        if ($this->helper->isActiveEngine()) {
            $category = $this->catModel->load(
                $this->helper->getCurrentStore()->getRootCategoryId()
            );
        }

        return [$category];
    }
    
    public function afterAddAttributeToSort(
        ProductCollection $collection,
        $result
    ) {
        if ($this->helper->isActiveEngine() && $this->helper->isPermittedHandle()) {
            $this->applyScoreSorting($collection);
        }       

        return $collection;
    }
    
    /**
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @return bool
     */
    public function applyScoreSorting(ProductCollection $collection) : bool
    {
        $fromPart = $collection->getSelect()->getPart('from');
        if (is_array($fromPart) && array_key_exists('search_result', $fromPart)) {
            $collection->getSelect()->reset(Select::ORDER);
            $collection->getSelect()->columns('search_result.score')->order('score ASC');
            return true;
        }
        
        return false;
    }
}