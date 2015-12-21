<?php
namespace Celebros\ConversionPro\Helper;

use Magento\Framework\App\Helper;

class Cache extends Helper\AbstractHelper
{
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
    
    public function getId($method, $vars = array())
    {
        return /*base64_encode(*/'conversionpro_'. $method . implode('', $vars);//);
    }
    
    public function load($cacheId)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)) { 
            return $this->cache->load($cacheId);
        }
        
        return FALSE;
    }
    
    public function save($data, $cacheId)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)) { 
            $this->cache->save($data, $cacheId, array(self::CACHE_TAG), self::CACHE_LIFETIME);
            return TRUE;
        }
        
        return FALSE;
    }
    
}