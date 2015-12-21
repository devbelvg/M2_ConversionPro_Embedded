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
namespace Celebros\ConversionPro\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\ScopeInterface;

class CrossSell extends \Magento\Framework\App\Helper\AbstractHelper
{
    CONST XML_PATH_CROSSSELL_NAME = 'conversionpro/crosssell_settings/crosssell_customer_name';
    CONST XML_PATH_CROSSSELL_ADDRESS = 'conversionpro/crosssell_settings/crosssell_address';
    CONST XML_PATH_CROSSSELL_REQUEST_HANDLE = 'conversionpro/crosssell_settings/crosssell_request_handle';
    
    protected $_serverAddress;
    protected $_siteKey;
    protected $_requestHandle;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        parent::__construct($context);
        if ($this->getName() != '' && $this->getAddress() != '' && $this->getRequestHandle() != '') {
            $this->_serverAddress = $this->getAddress();
            if (preg_match('/http:\/\//',$this->_serverAddress)) {
                $this->_serverAddress = preg_replace('/http::\/\//','', $this->_serverAddress);
            }
            $this->_siteKey = $this->getName();
            $this->_requestHandle = $this->getRequestHandle();
        }
    }
    
    public function getRecommendationsIds($id)
    {
        $arrIds = array();
        if ($this->_serverAddress) {
            $url = "http://{$this->_serverAddress}/JsonEndPoint/ProductsRecommendation.aspx?siteKey={$this->_siteKey}&RequestHandle={$this->_requestHandle}&RequestType=1&SKU={$id}&Encoding=utf-8";
            $jsonData =  $this->_get_data($url);
            $obj = json_decode($jsonData);
            for($i=0; isset($obj->Items) && $i < count($obj->Items); $i++) {
                $arrIds[] = (int) $obj->Items[$i]->Fields->SKU;
            }
        }
        
        return $arrIds; 
    }
    
    protected function _get_data($url)
    {
//var_dump($url);
        $data = null;

        $timeout = 400;
        $conTimeout = 100;
         
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $conTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
            
        $curlError = curl_error($ch);
        curl_close($ch);
        
        return $data;
    }
    
    public function getName($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL_NAME, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getAddress($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL_ADDRESS, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getRequestHandle($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL_REQUEST_HANDLE, ScopeInterface::SCOPE_STORE, $store);
    }
    
}