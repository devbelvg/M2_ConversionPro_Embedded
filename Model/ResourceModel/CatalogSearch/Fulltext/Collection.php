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
namespace Celebros\ConversionPro\Model\ResourceModel\CatalogSearch\Fulltext;

use Magento\Framework\DB\Select;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->get('\Celebros\ConversionPro\Helper\Data');
        
        if (!$this->helper->isActiveEngine()) {
            return parent::addCategoryFilter($category);
        }
        
        $this->searchHelper = $objectManager->get('\Celebros\ConversionPro\Helper\Search');
        
        $catModel = $objectManager->get('\Magento\Catalog\Model\Category');
        $category = $catModel->load($this->helper->getCurrentStore()->getRootCategoryId());
        
        return parent::addCategoryFilter($category);
    }
    
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);
        return parent::setVisibility($visibility);
    }
}
