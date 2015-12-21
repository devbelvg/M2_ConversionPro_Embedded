<?php
namespace Celebros\ConversionPro\Model\Resource\CatalogSearch\Fulltext;

use Magento\Framework\DB\Select;

class Collection extends \Magento\CatalogSearch\Model\Resource\Fulltext\Collection
{
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;
    
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Search\Model\QueryFactory $catalogSearchData,
        \Magento\CatalogSearch\Model\Fulltext $catalogSearchFulltext,
        \Magento\Framework\Search\Request\Builder $requestBuilder,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        $connection = null)
    {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $catalogSearchData,
            $catalogSearchFulltext,
            $requestBuilder,
            $searchEngine,
            $connection);
            
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
    }
    
}