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

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper;

class Analytics extends Helper\AbstractHelper
{
    const ANALYTICS_URL_PATH = 'ai.celebros-analytics.com/AIWriter/WriteLog.ashx';
    
    protected $_urlParams = [];
    
    public function __construct(
        Context $context,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->helper = $helper;
        $this->curl = $curl;
        $this->setUrlParam('type', 'SR');
        $this->setUrlParam('responseType', 'JSON');
        
        parent::__construct($context);
    }
    
    public function getProtocol()
    {
        return $this->_getRequest()->isSecure() ? 'https' : 'http';
    }
    
    public function setUrlParam($name, $value)
    {
        $this->_urlParams[$name] = $value;
    }

    protected function _generateGUID()
    {
        global $SERVER_ADDR;
        
        $long_ip = ip2long($SERVER_ADDR);
        if ($long_ip < 0) {
            $long_ip += pow(2,32);
        }
        
        $time = microtime();
        if ($time < 0) {
            $time += pow(2,32);
        }
        
        $combined = $long_ip . $time;
        $guid = md5($combined);
        $guid = substr($guid, 0, 8) . "-" .
        substr($guid, 8, 4) . "-" .
        substr($guid, 12, 4) . "-" .
        substr($guid, 16, 4) . "-" .
        substr($guid, 20);
        
        return $guid;
    }
    
    public function getParamsToUrl()
    {
        $result = [];
        foreach ($this->_urlParams as $param => $value) {
            $result[] = $param . '=' . $value;
        }
        
        return implode('&', $result);
    }
    
    public function sendAnalyticsRequest(\Magento\Framework\Simplexml\Element $results)
    {
        $host = $this->helper->getAnalyticsHost();
        $this->setUrlParam('cid', $this->helper->getAnalyticsCustId());
        $pageReferrer = $this->_urlBuilder->getUrl('*/*/*', array('_current' => TRUE));
        $this->setUrlParam('ref', $this->_urlBuilder->getBaseUrl());
        $this->setUrlParam('src', $pageReferrer);
        $webSessionId = isset($_SESSION['core']['visitor_data']['session_id']) ? $_SESSION['core']['visitor_data']['session_id'] : session_id();
        $this->setUrlParam('wsid', $webSessionId);
        $this->setUrlParam('ssid', $this->_generateGUID());
        $this->setUrlParam('lh', $this->getQwiserSearchLogHandle($results));
        $this->setUrlParam('dc', '');
        $this->setUrlParam('userid', '');
        $this->curl->get(self::ANALYTICS_URL_PATH . '?' . $this->getParamsToUrl());
        try {
            $response = $this->parseAnalyticsResponse($this->curl->getBody());
            if ($response->Result->success) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
    
    public function parseAnalyticsResponse($body)
    {
        return json_decode(str_replace(array('anlxCallback(',');'), '', $body));
    }
    
    public function getQwiserSearchLogHandle(\Magento\Framework\Simplexml\Element $results)
    {
        return $results->QwiserSearchResults->getAttribute('LogHandle');
    }
}