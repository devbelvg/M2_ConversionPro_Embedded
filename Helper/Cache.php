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
use Magento\Store\Model\ScopeInterface;

class Cache extends Helper\AbstractHelper
{
    const XML_PATH_CACHE_LIFETIME = 'conversionpro/advanced/request_lifetime';
    
    const CACHE_TAG = 'CONVERSIONPRO';
    const CACHE_ID = 'conversionpro';
    const CACHE_LIFETIME = 13600;
    
    /**
     * @var Data
     */
    protected $helper;
    protected $cache;
    protected $cacheState;
    
    /**
     * @var \Celebros\ConversionPro\Model\Search
     */
    protected $search;
    
    public function __construct(
        Helper\Context $context,
        Data $helper,
        \Magento\Framework\App\Cache $cache,
        \Magento\Framework\App\Cache\State $cacheState
    ) {
        $this->helper = $helper;
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        parent::__construct($context);
    }
    
    public function getId($method, $vars = [])
    {
        return sha1($method . '::' . implode('', $vars));
    }
    
    public function load($cacheId)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)
        && ($this->getCacheLifeTime() >= 0)) {
            return $this->cache->load($cacheId);
        }
        
        return false;
    }
    
    public function save($data, $cacheId)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)
        && ($this->getCacheLifeTime() >= 0)) {
            $this->cache->save(
                $data,
                $cacheId,
                [self::CACHE_TAG],
                $this->getCacheLifeTime()
            );
            
            return true;
        }
        
        return false;
    }
    
    protected function getCacheLifeTime($store = null) : int
    {
        $lifeTime = $this->scopeConfig->getValue(
            self::XML_PATH_CACHE_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        
        if ($lifeTime) {
            return (int) $lifeTime;
        }
        
        return self::CACHE_LIFETIME;
    }
}
