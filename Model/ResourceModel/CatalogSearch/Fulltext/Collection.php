<?php
namespace Celebros\ConversionPro\Model\ResourceModel\CatalogSearch\Fulltext;

use Magento\Framework\DB\Select;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    //protected $helper;
    
    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    //protected $searchHelper;
    
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->get('\Celebros\ConversionPro\Helper\Data');
        $this->searchHelper = $objectManager->get('\Celebros\ConversionPro\Helper\Search');
        
        $catModel = $objectManager->get('\Magento\Catalog\Model\Category');
        $category = $catModel->load($this->helper->getCurrentStore()->getRootCategoryId());
        
        if (!$this->helper->isEnabled()) {
            return parent::addCategoryFilter($category);
        }
        
        /*if (!$this->helper->isTextualNav2Search() && ($answerId = $this->searchHelper->getAnswerIdByCategoryId($category))) {
            // search by cat answer id
            //$this->addFieldToFilter(\Celebros\ConversionPro\Helper\Search::CATEGORY_QUESTION_TEXT, $answerId);
            $this->addFieldToFilter('category_ids', $answerId);            
        } else {
            // search by category path
            $this->addSearchFilter($this->searchHelper->getCategoryQueryTerm($category));
        }*/
        
        //return \Magento\Catalog\Model\ResourceModel\Product\Collection::addCategoryFilter($category);
        return parent::addCategoryFilter($category);
        return $this;
    }
    
    /*public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->get('\Celebros\ConversionPro\Helper\Data');
        
        if (!$this->helper->isEnabled()) {
            return parent::setOrder($attribute, $dir);
        }
        
        // ignore order change
        return $this;
    }*/
    
    
    /**
     * Search documents by query
     * Set found ids and number of found results
     *
     * @return Celebros_Conversionpro_Model_Resource_Collection
     */
    /*protected function _beforeLoad()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->get('\Celebros\ConversionPro\Helper\Data');
        $this->searchHelper = $objectManager->get('\Celebros\ConversionPro\Helper\Search');
        $ids = $this->searchHelper->getCustomResults();
        //print_r($ids);die;
echo (string)$this->getSelect();die;   
        return parent::_beforeLoad();
    }*/
}