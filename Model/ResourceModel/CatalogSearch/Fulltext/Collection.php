<?php
namespace Celebros\ConversionPro\Model\ResourceModel\CatalogSearch\Fulltext;

use Magento\Framework\DB\Select;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;
    
    /*public function _construct() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->get('\Celebros\ConversionPro\Helper\Data');
        $this->searchHelper = $objectManager->get('\Celebros\ConversionPro\Helper\Search');       
        if ($this->helper->isEnabled()) {
            // always use same order as Celebros search
            // (setOrder method is disabled)
            parent::setOrder('relevance', Select::SQL_ASC);
        }
    }
    
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        if (!$this->helper->isEnabled()) {
            return parent::addCategoryFilter($category);
        }
        
        if (!$this->helper->isTextualNav2Search() && ($answerId = $this->searchHelper->getAnswerIdByCategoryId($category))) {
            // search by cat answer id
            $this->addFieldToFilter(\Celebros\ConversionPro\Helper\Search::CATEGORY_QUESTION_TEXT, $answerId);
        } else {
            // search by category path
            $this->addSearchFilter($this->searchHelper->getCategoryQueryTerm($category));
        }
        
        return $this;
    }
    
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        if (!$this->helper->isEnabled()) {
            return parent::setOrder($attribute, $dir);
        }
        
        // ignore order change
        return $this;
    }*/
    
}