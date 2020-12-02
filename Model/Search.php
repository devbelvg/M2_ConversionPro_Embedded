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
namespace Celebros\ConversionPro\Model;

use \Magento\Framework\DataObject;
use \Magento\Framework\Simplexml\Element as XmlElement;
use Celebros\ConversionPro\Model\Logger;

class SearchException extends \Exception {}
class SearchCurlErrorException extends SearchException {}
class SearchServiceErrorException extends SearchException {}
class SearchResponseErrorException extends SearchException {}

class Search
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;
    
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var Celebros\ConversionPro\Helper\Data
     */
    protected $helper;
    
    /**
     * @var Celebros\ConversionPro\Helper\Analytics
     */
    protected $analytics;
    
    /**
     * @var Celebros\ConversionPro\Helper\Search
     */
    protected $cache;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;

    protected $newSearch = true;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    protected $attributeCollection;
    
    protected $allQuestions;
    
    protected $systemFilters = ['category_ids', 'visibility'];
    
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        Session $session,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Analytics $analytics,
        \Celebros\ConversionPro\Helper\Cache $cache,
        \Magento\Framework\App\Action\Context $context,
        Logger $logger,
        \Celebros\ConversionPro\Client\Curl $curl
    ) {
        $this->session = $session;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->helper = $helper;
        $this->analytics = $analytics;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->curl = $curl;
        $this->context = $context;
        $this->messageManager = $context->getMessageManager();
    }
    
    public function createSearchHandle(DataObject $params = null)
    {
        $searchInfoXml = $this->createSearchInfoXml($params);
        return $this->searchInfoXmlToHandle($searchInfoXml);
    }
    
    public function createSearchInfoXml(DataObject $params = null)
    {
        $this->newSearch = true;
        !is_null($params) or $params = new DataObject();
//print_r('9999');die;       
        // Search string
        $searchInfoXml = new XmlElement('<SearchInformation/>');
        if ($params->hasQuery()) {
            $query = $this->_escapeQueryString($params->getQuery());
            $searchInfoXml->addChild('Query', $query);
            $searchInfoXml->addChild('OriginalQuery', $query);
        }
        
        // Filters
        if ($params->hasFilters() && is_array($params->getFilters())) {
            // create answer container element
            $answersXml = $searchInfoXml->addChild('QwiserAnsweredAnswers');
            $answerCount = 0;
            foreach ($params->getFilters() as $name => $optionIds) {
            $this->newSearch = false;
                if (!in_array($name, $this->systemFilters) && $this->validateRequestVar($name)) {
                    is_array($optionIds) or $optionIds = array($optionIds);
                    foreach ($optionIds as $optionId) {
                        $optionId = $this->helper->filterValueToArray($optionId);
                        foreach ($optionId as $id) {
                            // create answer element
                            $answerXml = $answersXml->addChild('QwiserAnsweredAnswer');
                            $answerXml->setAttribute('AnswerId', $id);
                            $answerXml->setAttribute('EffectOnSearchPath', '0');
                            // add answer element
                            ++$answerCount;
                        }
                    }
                }
            }
            
           
            $answersXml->setAttribute('Count', $answerCount);
        }
     
        // Sorting
        if ($params->hasSortBy() && is_array($params->getSortBy())) {
            // [<field-name>, <order>]
            $sortBy = $params->getSortBy();
            $name = array_shift($sortBy);
            $order = array_shift($sortBy);
            if (!is_null($name)) {
                // create sorting options element
                $fieldName = $this->_getSortingFieldName($name);
                $ascending = ($order == 'desc') ? 'false' : 'true';
                list($method, $isNumeric) = $this->_getSortingMethod($name);
                $sortingOptionsXml = $searchInfoXml->addChild('SortingOptions');
                $sortingOptionsXml->setAttribute('FieldName', $fieldName);
                $sortingOptionsXml->setAttribute('Ascending', $ascending);
                $sortingOptionsXml->setAttribute('Method', $method);
                if (!is_null($isNumeric)) {
                    $sortingOptionsXml->setAttribute(
                        'NumericSort', $isNumeric ? 'true' : 'false');
                }
            }
        }
        
        //Profile Name
        if ($profileName = $this->helper->getProfileName()) {
            $searchInfoXml->setAttribute('IsDefaultSearchProfileName', 'false');
            $searchInfoXml->setAttribute('SearchProfileName', urlencode($profileName));
        }
        
        // Page size
        if ($params->hasPageSize()) {
            $searchInfoXml->setAttribute('IsDefaultPageSize', 'false');
            $searchInfoXml->setAttribute('PageSize', $params->getPageSize());
        }
        
        // Current page
        if ($params->hasCurrentPage()) {
            $searchInfoXml->setAttribute('CurrentPage', $params->getCurrentPage());
        }
        
        // some mandatory arguments
        $searchInfoXml->setAttribute('PriceFieldName', 'Price');
        $searchInfoXml->setAttribute('NumberOfPages', 9999999);
//print_r('lll'); die;       
        return $searchInfoXml;
    }
    
    public function validateRequestVar(string $varName) : bool
    {
        $questions = $this->getAllQuestions();
        $names = ['price'];
        if (!empty($questions->Questions)) {
            foreach ($questions->Questions->children() as $question) {
                $names = array_merge($names, $this->getAltRequestVars($question->getAttribute('Text')));
            }
        }

        return in_array($varName, $names);
    }
    
    public function getAltRequestVars(string $requestVar) : array
    {
        $requestVar = str_replace('.', '_', $requestVar);
        
        return [
            $requestVar,
            str_replace(' ', '_', $requestVar),
            str_replace(' ', '+', $requestVar)
        ];    
    }
    
    public function searchInfoXmlToHandle(XmlElement $xml)
    {
        $handle = '';
        if (isset($xml->Query) && strlen($xml->Query) > 0)
            $handle .= 'A=' . $this->_handleEscape($this->prepareSearchQueryForRequest($xml->Query)) . '~';
        if (isset($xml->OriginalQuery) && strlen($xml->OriginalQuery) > 0)
            $handle .= 'B=' . $this->_handleEscape($this->prepareSearchQueryForRequest($xml->OriginalQuery)) . '~';
        if (!empty($xml->getAttribute('CurrentPage')))
            $handle .= 'C=' . $xml->getAttribute('CurrentPage') . '~';
        if (!empty($xml->getAttribute('IsDefaultPageSize')) && ($xml->getAttribute('IsDefaultPageSize') != 'true'))
            $handle .= 'D=' . $xml->getAttribute('PageSize') . '~';
        if (isset($xml->SortingOptions) && !$this->_isSortingOptionsDefault($xml->SortingOptions))
            $handle .= 'E=' . $this->_handleEscape($this->_sortingOptionsToHandleString($xml->SortingOptions)) . '~';
        if (!empty($xml->getAttribute('FirstQuestionId')))
            $handle .= 'F=' . $this->_handleEscape($xml->getAttribute('FirstQuestionId')) . '~';
        if (isset($xml->QwiserAnsweredAnswers) && !empty($xml->QwiserAnsweredAnswers->getAttribute('Count')))
            $handle .= 'G=' . $this->_handleEscape($this->_answeredAnswersToHandleString($xml->QwiserAnsweredAnswers)) . '~';
        if (!empty($xml->getAttribute('IsDefaultSearchProfileName')) && $xml->getAttribute('IsDefaultSearchProfileName') != 'true')
            $handle .= 'H=' . $this->_handleEscape($xml->getAttribute('SearchProfileName')) . '~';
        if (!empty($xml->getAttribute('PriceFieldName')))
            $handle .= 'I=' . $this->_handleEscape($xml->getAttribute('PriceFieldName')) . '~';
        if (isset($xml->SpecialCasesDetectedInThisSession))
            $handle .= 'J' . $this->_handleEscape($this->_specialCasesToHandleString($xml->SpecialCasesDetectedInThisSession)) . '~';
        if (!empty($xml->getAttribute('MaxMatchClassFound')))
            $handle .= 'K=' . $xml->getAttribute('MaxMatchClassFound') . '~';
        if (!empty($xml->getAttribute('MinMatchClassFound')))
            $handle .= 'L=' . $xml->getAttribute('MinMatchClassFound') . '~';
        if (!empty($xml->getAttribute('NumberOfPages')) && $xml->getAttribute('NumberOfPages') != '1')
            $handle .= 'M=' . $xml->getAttribute('NumberOfPages') . '~';
        if (!empty($xml->getAttribute('Stage')) && $xml->getAttribute('Stage') != '1')
            $handle .= 'N=' . $xml->getAttribute('Stage') . '~';
            
        return $handle;
    }
    
    protected function _handleEscape($string)
    {
        return str_replace('~', '~~', $string);
    }
    
    protected function _isSortingOptionsDefault(XmlElement $xml)
    {
        $isDefault = ($xml->getAttribute('Ascending') != 'true')
            && ($xml->getAttribute('NumericSort') != 'true')
            && empty($xml->getAttribute('FieldName'))
            && ($this->getAttribute('Method') == 'Relevancy');
        return $isDefault;
    }
    
    protected function _sortingOptionsToHandleString(XmlElement $xml)
    {
        $params = array(
            ($xml->getAttribute('Ascending') == 'true') ? '1' : '0',
            ($xml->getAttribute('NumericSort') == "true") ? '1' : '0',
            $this->_sortMethodToInt($xml->getAttribute('Method')),
            $xml->getAttribute('FieldName'));
        return implode('^', $params);
    }
    
    protected function _answeredAnswersToHandleString(XmlElement $xml)
    {
        $handle = '';
        foreach ($xml->children() as $answerXml) {
            $handle .= sprintf(
                '%s^%s^',
                $answerXml->getAttribute('AnswerId'),
                $this->_effectOnSearchPathToInt(
                    $answerXml->getAttribute('EffectOnSearchPath')));
        }
        return $handle;
    }
    
    protected function _specialCasesToHandleString(XmlElement $xml)
    {
        return implode('^', $xml->children());
    }
    
    protected function _sortMethodToInt($method)
    {
        switch ($method) {
            case 'Price':
                return 0;
            case 'Relevancy':
                return 1;
            case 'SpecifiedField':
                return 2;
            default:
                return -1;
        }
    }
    
    protected function _effectOnSearchPathToInt($effect)
    {
        if (is_numeric($effect))
            return $effect;
            
        switch ($effect) {
            case 'Exclude':
                return 0;
            case 'ExactAnswerNode':
                return 1;
            case 'EntireAnswerPath':
                return 2;
            default:
                return -1;
        }
    }
    
    public function search($query)
    {
        $request = sprintf(
            'search?sitekey=%s&Query=%s',
            $this->helper->getSiteKey(), $this->prepareSearchQueryForRequest($query));
        return $this->_request($request);
    }
    
    public function prepareSearchQueryForRequest($query)
    {
        return str_replace("%2B","%20", urlencode($query));
    }
    
    public function getCustomResults($searchHandle, $isNewSearch, $previousSearchHandle = '')
    {
        // use previous search handle if not provided
        if (empty($previousSearchHandle) && $this->session->hasPreviousSearchHandle())
            $previousSearchHandle = $this->session->getPreviousSearchHandle();
            
        $request = sprintf(
            'GetCustomResults?Sitekey=%s&SearchHandle=%s&NewSearch=%s&PreviousSearchHandle=%s',
            $this->helper->getSiteKey(),
            $searchHandle,
            ($this->newSearch ? '1' : '0'),
            (!$this->newSearch ? $previousSearchHandle : '')
        );
        
        $response = $this->_request($request);

        if ($this->helper->isRequestDebug()) {
            $message = [];
            $message['title'] = __('Celebros Search Engine');
            $message['products_sequence'] = $this->_extractProductSequenceFromResponse($response); 
            $this->messageManager->addSuccess($this->helper->prepareDebugMessage($message));
        }

        $this->isFallbackRedirect($response);
        
        $this->isSingleProductsRedirect($response);
        
        // save previous search handle
        $previousSearchHandle = $response->QwiserSearchResults->getAttribute('SearchHandle');
        $this->session->setPreviousSearchHandle($previousSearchHandle);
        
        return $response;
    }
    
    protected function _extractProductSequenceFromResponse(XmlElement $response) : string
    {
        $productSequence = [];
        $products = $response->QwiserSearchResults->Products;
        
        foreach ($products->children() as $rawDocument) {
            foreach ($rawDocument->Fields->children() as $field) {
                if ($field->getAttribute('name') == \Celebros\ConversionPro\Helper\Data::RESPONSE_XML_TITLE_ATTRIBUTE_NAME) {
                    $name = $field->getAttribute('value');
                }
                if ($field->getAttribute('name') == \Celebros\ConversionPro\Helper\Data::RESPONSE_XML_PRICE_ATTRIBUTE_NAME) {
                    $price = $field->getAttribute('value');
                }                
            }

            $productSequence[] = $name . '(' . $price. ')';    
        }

        return implode(", ", $productSequence);
    }
    
    public function isSingleProductsRedirect($results)
    {
        $relevantProductsCount = $results->QwiserSearchResults->getAttribute('RelevantProductsCount');
        $products = $results->QwiserSearchResults->Products;
        if ($relevantProductsCount == 1 && $this->helper->isRedirectToProductEnabled()) {
            foreach ($products->Product->Fields->Field as $field) {
                if ($field->getAttribute('name') == \Celebros\ConversionPro\Helper\Data::RESPONSE_XML_LINK_ATTRIBUTE_NAME) {
                    $this->context->getRedirect()->redirect(
                        $this->context->getResponse(),
                        $this->prepareUrlForRedirect(str_replace('http:', '', $field->getAttribute('value')))
                    );    
                }
            }
        }
    }
    
    public function prepareUrlForRedirect($rawUrl)
    {
        if (strpos($rawUrl, "//") !== false && strpos($rawUrl, "//") == 0) {
            $rawUrl = substr_replace($rawUrl, null, 0, 2);
        }
        
        if (!preg_match("~^(?:f|ht)tps?://~i", $rawUrl)) {
            $rawUrl = "http://" . $rawUrl;
        }
        
        return $rawUrl;
    }
    
    public function isFallbackRedirect($results)
    {
        $maxMatchClassFound = $results->QwiserSearchResults->getAttribute("MaxMatchClassFound");
        $minMatchClassFound = $results->QwiserSearchResults->getAttribute("MinMatchClassFound");
        $searchInfo = $results->QwiserSearchResults->SearchInformation;
        $redirect = FALSE;
        if ($param = $searchInfo->SpecialCasesDetectedInThisSession->asArray()) {
            if (isset($param['Value']) && $param['Value'] == 'NoResultsFallbackEmptyQuery') {
                $redirect = ($this->helper->isFallbackRedirectEnabled() && $this->helper->fallbackRedirectUrl()) ? TRUE : FALSE;
            }
        }
        
        if ($maxMatchClassFound == 'None' && $minMatchClassFound == 'None' && $redirect) {
            $this->analytics->sendAnalyticsRequest($results);
            $this->context->getRedirect()->redirect(
                $this->context->getResponse(),
                $this->helper->fallbackRedirectUrl()
            );
        }
    }
    
    public function getAllQuestions()
    {
        if (!$this->allQuestions) {
            $request = sprintf(
                'GetAllQuestions?Sitekey=%s&Searchprofile=%s',
                $this->helper->getSiteKey(), urlencode($this->helper->getProfileName())
            );
            $this->allQuestions = $this->_request($request);
        }
        
        return $this->allQuestions;
    }
    
    public function getQuestionAnswers($questionId)
    {
        $request = sprintf(
            'GetQuestionAnswers?Sitekey=%s&QuestionId=%s',
            $this->helper->getSiteKey(), $questionId);
        return $this->_request($request);
    }
    
    protected function _request($request, $source = null)
    {
        $requestUrl = $this->_requestUrl($request);
        $startTime = round(microtime(true) * 1000);
        $cacheId = $this->cache->getId(__METHOD__, array($request));
        if ($response = $this->cache->load($cacheId)) {
            if ($this->helper->isRequestDebug()) {
                $stime = round(microtime(true) * 1000) - $startTime;
                $message = [
                    'title' => __('Celebros Search Engine'),
                    'request' => $requestUrl,
                    'cached' => 'TRUE'
                ];
                $this->messageManager->addSuccess($this->helper->prepareDebugMessage($message));
            }
        } else {
            $this->curl->addHeader('Accept', 'text/xml');
            $this->curl->get($requestUrl);
            $response = $this->curl->getBody();
            
            $this->cache->save($response, $cacheId);
            
            if ($this->helper->isRequestDebug()) {
                $stime = round(microtime(true) * 1000) - $startTime;
                $message = [
                    'title' => __('Celebros Search Engine'),
                    'request' => $requestUrl,
                    'cached' => 'FALSE',
                    'duration' => $stime . 'ms'
                ];  
                $this->messageManager->addSuccess($this->helper->prepareDebugMessage($message));
            }
        }
        
        return $this->_parseResponse($response);
    }
    
    protected function _getHostUrl()
    {
        $host = $this->helper->getHost();
        $host = preg_replace('@^http://@', '', $host);
        $host = 'http://' . rtrim($host);
        
        $port = $this->helper->getPort();
        
        return empty($port) ? $host : $host . ':' . $port;
    }
    
    protected function _requestUrl($request)
    {
        return $this->_getHostUrl() . '/' . ltrim($request, '/');
    }
    
    public function parseXmlResponse($response)
    {
        return $this->_parseResponse($response);
    }
    
    protected function _parseResponse($response)
    { 
        try {
            $xml = simplexml_load_string($response, '\Magento\Framework\Simplexml\Element');
        } catch (\Exception $message) {
            $this->_logException(
                $message,
                $response,
                new SearchServiceErrorException($message)
            );
        }
        
        if ($xml === false) {
            $message = __('Service response is empty');
            $this->_logException(
                $message,
                $response,
                new SearchServiceErrorException($message)
            );
        }    
            
        // check if error is indicated in response
        if ($xml->getAttribute('ErrorOccurred') == 'true') {
            if (isset($xml->QwiserError)) {
                $message = '';
                if (null !== $xml->QwiserError->getAttribute('MethodName')) {
                    $message .= sprintf(
                        'Error occured in method %s: ',
                        $xml->QwiserError->getAttribute('MethodName'));
                }
                
                $message .= (null !== $xml->QwiserError->getAttribute('ErrorMessage'))
                    ? $xml->QwiserError->getAttribute('ErrorMessage')
                    : __('Unknown error');
            }
            
            $this->_logException(
                $message,
                $response,
                new SearchResponseErrorException($message)
            );
        }

        if (isset($xml->ReturnValue) 
        && $xml->ReturnValue instanceof \Magento\Framework\Simplexml\Element
        && !empty($xml->ReturnValue)) {
            return $xml->ReturnValue;
        } else {
            $message = __('No return value in response');
            $this->_logException(
                $message,
                $response,
                new SearchServiceErrorException($message)
            );   
        }
        
        return false;
    }
    
    /**
     * @param  string           $message
     * @param  string           $response
     * @param  \Exception|null  $exception
     * @return void
     */
    protected function _logException(
        string $message,
        $response = null,
        $exception = null)
    {
        $this->logger->warning($message);
        if ($response) {
            $this->logger->warning('Response: ' . $response);
        }
        
        if ($exception) {
            throw $exception;
        }
    }
    
    protected function _escapeQueryString($query)
    {
        $query = str_replace(' ', '+', $query);
        $query = str_replace('&', '%26', $query);
        return $query;
    }
    
    protected function _getSortingFieldName($name)
    {
        if ($name == 'name') {
            return 'Title';
        } elseif (in_array($name, ['relevance', 'position'])) {
            return 'Relevancy';
        } else {
            return ucfirst($name);
        }
    }
    
    protected function _getSortingMethod($name)
    {
        if (in_array($name, ['relevance', 'position'])) {
            return ['Relevancy', true];
        } else if ($name == 'price') {
            return ['Price', true];
        } else {
            $attributeCollection = $this->_getAttributeCollection();
            $attribute = $attributeCollection->getItemByColumnValue('code', $name);
            $isNumeric = false;
            if (!is_null($attribute))
                $isNumeric = in_array($attribute->getBackendType(), ['int', 'decimal', 'datetime']);
            return ['SpecifiedField', $isNumeric];
        }
    }
    
    protected function _getAttributeCollection()
    {
        if (is_null($this->attributeCollection))
            $this->attributeCollection = $this->attributeCollectionFactory->create();
        return $this->attributeCollection;
    }
    
}