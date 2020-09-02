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
namespace Celebros\ConversionPro\Helper;

use Magento\Framework\App\Helper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\Category;
use Celebros\ConversionPro\Model\Config\Source\CategoryQueryType;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Simplexml\Element as XmlElement;
use Magento\Store\Model\ScopeInterface;

class Search extends Helper\AbstractHelper
{
    const CATEGORY_QUESTION_TEXT = 'Category';
    const CAT_ID_DYN_PROPERTY = 'MagEntityID';
    const CACHE_TAG = 'CONVERSIONPRO';
    const CACHE_ID = 'conversionpro';
    const REDIRECT_DYNAMIC_PROPERTY_NAME = 'redirection url';
    
    /**
     * @var Data
     */
    protected $helper;
    
    protected $customResultsCache = [];
    protected $allQuestionsCache;
    protected $questionAnswers = [];
    
    protected $currentPage;
    protected $pageSize;
    protected $order;
    protected $cache;
    protected $category;
    protected $productAttributes = [];
    protected $currentSearchParams;
    
    /**
     * @var \Celebros\ConversionPro\Model\Search
     */
    protected $search;
    
    public function __construct(
        Helper\Context $context,
        Data $helper,
        \Celebros\ConversionPro\Helper\Cache $cache,
        \Celebros\ConversionPro\Model\Search $search,
        \Magento\Catalog\Model\Category $category,
        \Magento\Framework\App\ResponseFactory $response
    ) {
        $this->helper = $helper;
        $this->search = $search;
        $this->cache = $cache;
        $this->category = $category;
        $this->response = $response;
        parent::__construct($context);
    }
    
    public function getSearchParams()
    {
        if (!$this->currentSearchParams) {
            $request = $this->_getRequest();
            $params = new DataObject();
            
            $queryText = '';
            
            // search query text
            if ($request->getParam('q')) {
                $queryText = $request->getParam('q');
            }
                
            // category query text
            $category = $this->helper->getCurrentCategory();
            if ($category && $category->getId() != $this->helper->getCurrentStore()->getRootCategoryId()) {
                if (!$this->helper->isTextualNav2Search()) {
                    $queryText = '';
                } else {
                    $queryText = $this->getCategoryQueryTerm($category);
                }
            }
          
            $params->setQuery($queryText);
            
            // filters
            $filters = [];
            foreach ($this->getFilterRequestVars() as $requestVar) {
                $value = $this->getFilterValueAsArray($requestVar);
                if (!empty($value)) {
                    $filters[$requestVar] = $value;
                }
            }
            
            if ($category && $category->getId() != $this->helper->getCurrentStore()->getRootCategoryId()
            && !$this->helper->isTextualNav2Search()) {
                $filters[self::CATEGORY_QUESTION_TEXT][] = $this->getAnswerIdByCategoryId($category);
            }
            
            $params->setFilters($filters);
            
            $this->currentSearchParams = $params;
            return $params;
        }
        
        return $this->currentSearchParams;
    }
    
    public function getCustomResults(DataObject $params = null)
    {
        $params = is_null($params) ? $this->getSearchParams() : clone $params;
        
        // order
        if (!is_null($this->order) && !$params->hasSortBy()) {
            $params->setSortBy($this->order);
        }
        
        // page size
        if (!is_null($this->pageSize) && !$params->hasPageSize()) {
            $params->setPageSize($this->pageSize);
        }
        
        // current page
        if (!is_null($this->currentPage) && !$params->hasCurrentPage()) {
            $params->setCurrentPage($this->currentPage - 1);
        }
       
        $searchHandle = $this->search->createSearchHandle($params);
        if (!isset($this->customResultsCache[$searchHandle])) {
            $this->customResultsCache[$searchHandle] = $this->search->getCustomResults($searchHandle, true, '');
            if ($this->helper->isCampaignsEnabled()) {
                $this->checkRedirects($this->customResultsCache[$searchHandle]);
            }
        }
        return $this->customResultsCache[$searchHandle];
    }
    
    public function checkRedirects($customResults)
    {
        $currentConcepts = $customResults->QwiserSearchResults->QueryConcepts->children();
        foreach ($currentConcepts as $concept) {
            if (!isset($concept->DynamicProperties)) {
                continue;
            }
            
            foreach ($concept->DynamicProperties->children() as $property) {
                if ($property->getAttribute('name') == self::REDIRECT_DYNAMIC_PROPERTY_NAME) {
                    $this->response
                        ->create()
                        ->setRedirect($property->getAttribute('value'))
                        ->sendResponse();
                    die;
                }
            }
        }
    }
    
    public function getAllQuestions()
    {
        if (is_null($this->allQuestionsCache)) {
            $this->allQuestionsCache = $this->search->getAllQuestions();
        }
        
        return $this->allQuestionsCache;
    }
    
    public function getQuestionAnswers($questionId)
    {
        if (!isset($this->questionAnswers[$questionId])) {
            $this->questionAnswers[$questionId] =
                $this->search->getQuestionAnswers($questionId);
        }
        return $this->questionAnswers[$questionId];
    }
    
    public function getQuestionAnswersAsArray($questionId, $keyAttribute = 'Id', $valueAttribute = 'Text')
    {
        $options = [];
        $answers = $this->getQuestionAnswers($questionId);
        foreach ($answers->Answers->Answer as $answer) {
            $options[$answer->getAttribute('Id')] = $answer->getAttribute('Text');
        };
        
        return $options;
    }
    
    public function getCategoryQueryTerm(Category $category, $store = null)
    {
        $queryType = $this->helper->getCategoryQueryType($store);
        if ($queryType == CategoryQueryType::NAME) {
            return $category->getName();
        }
        
        $parents = $category->getParentCategories();
        $parentIds = array_intersect($category->getParentIds(), array_keys($parents));
        switch ($queryType) {
            case CategoryQueryType::FULL_PATH:
                break;
            case CategoryQueryType::NAME_AND_PARENT_NAME:
                $parentId = $category->getParentId();
                $parentIds = in_array($parentId, $parentIds) ? [$parentId] : [];
                break;
            case CategoryQueryType::NAME_AND_ROOT_NAME:
                $parentIds = array_slice($parentIds, 0, 1);
                break;
        }
        
        $names = array_map(
            function ($id) use ($parents) {
                return $parents[$id]->getName();
            },
            $parentIds
        );
        $names[] = $category->getName();
        
        return str_replace(',', ' ', implode(' ', $names));
    }
    
    public function getValueFromRequest($requestVar)
    {
        $vars = $this->getAltRequestVars($requestVar);
        foreach ($vars as $var) {
            if ($value = $this->_getRequest()->getParam($var)) {
                return $value;
            }
        }
        
        return null;
    }
    
    public function checkRequestVar($requestVar)
    {
        $vars = $this->getAltRequestVars($requestVar);
        $params = $this->_getRequest()->getParams();
        foreach ($vars as $var) {
            if (isset($params[$var])) {
                return $var;
            }
        }
        
        return $requestVar;
    }
    
    public function getAltRequestVars($requestVar)
    {
        $requestVar = str_replace('.', '_', $requestVar);
        
        return [
            $requestVar,
            str_replace(' ', '_', $requestVar),
            str_replace(' ', '+', $requestVar)
        ];
    }
    
    public function getFilterValue($requestVar)
    {
        $filterRequestVars  = $this->getFilterRequestVars();
        $value = $this->getValueFromRequest($requestVar);
        
        if (!is_null($value) && !$this->helper->isMultiselectEnabled()) {
            $values = $this->filterValueToArray($value);
            $value = reset($values);
        }
        return $value;
    }
    
    public function getFilterValueAsArray($requestVar)
    {
        $value = $this->getFilterValue($requestVar);
        return is_null($value) ? [] : $this->filterValueToArray($value);
    }
    
    public function filterValueToArray($value)
    {
        return $this->helper->filterValueToArray($value);
    }
    
    public function getFilterRequestVars()
    {
        $questions = $this->getAllQuestions();
        $names = ['price'];
        if (!empty($questions->Questions)) {
            foreach ($questions->Questions->children() as $question) {
                $names[] = $question->getAttribute('Text');
            }
        }
        
        return $names;
    }
    
    public function getLabelByAnswerId($answerId)
    {
        return $this->questionAnswers;
    }
    
    public function setCurrentPage($page)
    {
        $this->currentPage = $page;
        return $this;
    }
    
    public function setPageSize($size)
    {
        $this->pageSize = $size;
        return $this;
    }
    
    public function setOrder($order, $dir)
    {
        $this->order = [$order, $dir];
        return $this;
    }
    
    public function getCurrentCustomResults($handle = null)
    {
        if ($handle) {
            if (isset($this->customResultsCache[$hanlde])) {
                return $this->customResultsCache[$hanlde];
            }
        }
        
        return reset($this->customResultsCache);
    }
    
    /*CONST PERC_SIMILARITY = 90;*/
    
    public function getQuestionByField($value, $field)
    {
        $allQuestions = $this->getAllQuestions()->Questions->Question;
        foreach ($allQuestions as $question) {
            /*similar_text($question->getAttribute($field), $value, $perc);
            if ($perc > self::PERC_SIMILARITY) {*/
            /*if ($question->getAttribute($field) == $value) {*/
            if (in_array($value, $this->getAltRequestVars($question->getAttribute($field)))) {
                return $question;
            }
        }
        
        return false;
    }
    
    public function getPriceQuestionMock()
    {
        $allQuestions = $this->getAllQuestions()->Questions->Question;
        foreach ($allQuestions as $question) {
            $mock = clone $question;
            $mock->setAttribute('Id', 'PriceQuestion');
            $mock->setAttribute('Text', 'By price range');
            $mock->setAttribute('SideText', 'Price');
            $mock->setAttribute('Type', 'Price');
            
            return $mock;
        }
    }
    
    public function getAnswerIdByCategoryId($category)
    {
        $cacheId = $this->cache->getId(__METHOD__, [$category->getId()]);
        if ($answerId = $this->cache->load($cacheId)) {
            return $answerId;
        }
        
        $allQuestions = $this->getAllQuestions()->Questions->Question;
        foreach ($allQuestions as $question) {
            if ($question->getAttribute('Text') == self::CATEGORY_QUESTION_TEXT) {
                $catQuestionId = (int)$question->getAttribute('Id');
                continue;
            }
        }
        
        if (isset($catQuestionId)) {
            $catLabel = $category->getName();
            $answers = $this->getQuestionAnswers($catQuestionId);
            foreach ($answers->Answers->Answer as $answer) {
                foreach ($answer->DynamicProperties->children() as $property) {
                    if ($property->getAttribute('name') == self::CAT_ID_DYN_PROPERTY) {
                        if ($property->getAttribute('value') == $category->getId()) {
                            $this->cache->save($answer->getAttribute('Id'), $cacheId);
                            return (int)$answer->getAttribute('Id');
                        }
                    }
                }
                
                /* try to find category by label */
                if ($answer->getAttribute('Text') == $catLabel) {
                    $this->cache->save($answer->getAttribute('Id'), $cacheId);
                    return (int)$answer->getAttribute('Id');
                }
            }
        }
        
        return false;
    }
    
    /**
     * Return product attribute value by another attribute value
     *
     * @param string $resultField
     * @param string $value
     * @param string $searchField
     * @return string
     */
    public function getProductAttributeValue($resultField, $value, $searchField = 'mag_id')
    {
        $cResult = null;
        $cProduct = false;
        $products = $this->getCustomResults()->QwiserSearchResults->Products;
        foreach ($products->children() as $product) {
            foreach ($product->Fields->children() as $field) {
                switch ($field->getAttribute('name')) {
                    case $resultField:
                        $cResult = $field->getAttribute('value');
                        if ($cProduct) {
                            return $cResult;
                        }
                        break;
                    case $searchField:
                        if ($field->getAttribute('value') == $value) {
                            if ($cResult == true) {
                                return $cResult;
                            } else {
                                $cProduct = true;
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }
    
        return false;
    }
    
    public function getToolbarData()
    {
        $searchResults = $this->getCustomResults()->QwiserSearchResults;
        $data = new \Magento\Framework\DataObject();
        $data->setCurrentPage($searchResults->SearchInformation->getAttribute('CurrentPage'));
        $data->setTotalNum($searchResults->getAttribute('RelevantProductsCount'));
        $data->setLastPageNum($searchResults->getAttribute('NumberOfPages'));
        $data->setData(
            '_current_grid_order',
            $this->sortOrderMap(
                $searchResults->SearchInformation
                    ->SortingOptions
                    ->getAttribute('FieldName')
            )
        );
        $data->setData(
            '_current_grid_direction',
            ($searchResults->SearchInformation->SortingOptions->getAttribute('Ascending') == 'true') ? 'asc' : 'desc'
        );
        return $data;
    }
    
    public function sortOrderMap($order)
    {
        switch ($order) {
            case 'Title':
                $result = 'name';
                break;
            case 'Relevancy':
                $result = 'relevance';
                break;
            default:
                $result = strtolower($order);
        }
        
        return $result;
    }
    
    public function getMinMaxPrices($val = null)
    {
        $values = [];
        $allQuestions = $this->getCustomResults()->QwiserSearchResults->Questions->Question;
        foreach ($allQuestions as $question) {
            if ($question->getAttribute('Id') == 'PriceQuestion') {
                foreach ($question->Answers->Answer as $answer) {
                    $id = $answer->getAttribute('Id');
                    if (preg_match('@^_P(\d+)_(\d+)$@', $id, $matches)) {
                        $values[] = $matches[1];
                        $values[] = $matches[2];
                    }
                }
            }
        }
        
        if (count($values) != 0) {
            if ($val == 'max') {
                return (int)max($values);
            } elseif ($val == 'min') {
                return (int)min($values);
            }
            
            return [
                'min' => min($values),
                'max' => max($values)
            ];
        } else {
            $return = $val ? false : ['min' => false, 'max' => false];
            return $return;
        }
    }
    
    /**
     * Extract dynamic property by name from xml element
     *
     * @param  XmlElement $element
     * @param  string $propertyName 
     * @return XmlElement | null
     */
    public function extractDynamicProperty(
        XmlElement $element,
        string $propertyName = null
    ) : ?XmlElement {
        if (!empty($element->DynamicProperties) 
        && $element->DynamicProperties instanceof \Magento\Framework\Simplexml\Element) {
            if (!$propertyName) {
                return $element->DynamicProperties;
            }
            
            foreach ($element->DynamicProperties->children() as $property) {
                if ($property->getAttribute('name') == $propertyName) {
                    return $property;
                }
            }
        }
        
        return null;
    }
}
