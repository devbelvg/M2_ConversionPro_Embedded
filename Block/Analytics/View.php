<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Conversionpro
 */
namespace Celebros\ConversionPro\Block\Analytics;

use Magento\Framework\View\Element\Template;

class View extends Template
{
    CONST ANALYTICS_JS_PATH = '/widgets/CelebrosToolbox.js'; 
    
    public $helper;
    public $searchHelper;
    public $registry;
    public $url;
    
    public function __construct(
        Template\Context $context,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        $this->registry = $registry;
        $this->url = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }
    
    /**
     * Sets parameters for tempalte
     *
     * @return Celebros_Conversionpro_Block_Analytics_View
     */
    protected function _prepareLayout()
    {
        $this->setCustomerId($this->helper->getAnalyticsCustId());
        $this->setHost($this->helper->getAnalyticsHost());
        
        $product = $this->getProduct();
        //Set product click tracking params
        if (isset($product)) {
            $this->setProductSku($product->getSku());
            $this->setProductName(str_replace("'", "\'", $product->getName()));
            $this->setProductPrice($product->getFinalPrice());
            $webSessionId = isset($_SESSION['core']['visitor_data']['session_id']) ? $_SESSION['core']['visitor_data']['session_id'] : session_id();
            $this->setWebsessionId($webSessionId);      
        } else {
            $pageReferrer = $this->url->getUrl('*/*/*', array('_current' => TRUE));
            $this->setPageReferrer($pageReferrer);
            //$this->setQwiserSearchSessionId(Mage::getSingleton('conversionpro/session')->getSearchSessionId());
            $this->setQwiserSearchSessionId($this->_generateGUID());
            $webSessionId = isset($_SESSION['core']['visitor_data']['session_id']) ? $_SESSION['core']['visitor_data']['session_id'] : session_id();
            $this->setWebsessionId($webSessionId);
        }
        
        return parent::_prepareLayout();
    }
    
    protected function _generateGUID()
    {
        global $SERVER_ADDR;
        
        // get the current ip, and convert it to its positive long value
        $long_ip = ip2long($SERVER_ADDR);
        if($long_ip < 0) $long_ip += pow(2,32);
        
        // get the current microtime and make sure it's a positive long value
        $time = microtime();
        if($time < 0)
        {
            $time += pow(2,32);
        }
        
        // put those strings together
        $combined = $long_ip . $time;
        
        // md5 it and throw in some dashes for easy checking
        $guid = md5($combined);
        $guid = substr($guid, 0, 8) . "-" .
        substr($guid, 8, 4) . "-" .
        substr($guid, 12, 4) . "-" .
        substr($guid, 16, 4) . "-" .
        substr($guid, 20);
        
        return $guid;
    }
    
    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }
    
    public function getQwiserSearchLogHandle()
    {
        if (is_object($results = $this->searchHelper->getCurrentCustomResults())) {
            return $results->QwiserSearchResults->getAttribute('LogHandle');
        }
        
        return FALSE;
    }
    
    public function getJsPath()
    {
        return self::ANALYTICS_JS_PATH;
    }
    
}