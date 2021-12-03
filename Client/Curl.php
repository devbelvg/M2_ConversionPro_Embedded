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
namespace Celebros\ConversionPro\Client;

use Celebros\ConversionPro\Model\Logger;

class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    const CURLOPT_CONNECTTIMEOUT = 100;
    const CURLOPT_TIMEOUT = 400;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     *  Return request headers
     *  @return array
     */
    public function getRequestHeaders() : array
    {
        return $this->_headers;
    }
    
    /**
     * @param int|null $sslVersion
     * @return void
     */
    public function __construct(
        Logger $logger,
        $sslVersion = null
    ) {
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_BINARYTRANSFER, true);
        $this->setOption(CURLOPT_CONNECTTIMEOUT, self::CURLOPT_CONNECTTIMEOUT);
        $this->setOption(CURLOPT_TIMEOUT, self::CURLOPT_TIMEOUT);
        
        $this->logger = $logger;
        parent::__construct($sslVersion);
    }
    
    /**
     * Make request
     *
     * @param string $method
     * @param string $uri
     * @param array|string $params
     *
     * @return void
     */
    protected function makeRequest($method, $uri, $params = [])
    {
        $requestUn = microtime(true);
        $this->logger->logCurrentUrl($requestUn);
        $this->logger->info($requestUn . ' - Request URI: ' . $uri);
        parent::makeRequest($method, $uri, $params);
        $this->logger->info($requestUn . ' - Request Headers: ' . json_encode($this->getRequestHeaders()));
        $this->logger->info($requestUn . ' - Response Headers: ' . json_encode($this->getHeaders()));
        $this->logger->info($requestUn . ' - Response Body: ' . (string)$this->getBody()); 
    }
}