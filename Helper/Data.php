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

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED  = 'conversionpro/general_settings/enabled';
    const XML_PATH_HOST     = 'conversionpro/general_settings/host';
    const XML_PATH_PORT     = 'conversionpro/general_settings/port';
    const XML_PATH_SITE_KEY = 'conversionpro/general_settings/sitekey';
    
    const XML_PATH_FILTER_MULTISELECT_ENABLED = 'conversionpro/display_settings/filter_multiselect_enabled';
    const XML_PATH_CAMPAIGNS_ENABLED          = 'conversionpro/display_settings/campaigns_enabled';
    const XML_PATH_PROFILE_NAME               = 'conversionpro/display_settings/profile_name';
    const XML_PATH_PRICE_FILTER_TYPE          = 'conversionpro/display_settings/filter_price_type';
    
    const XML_PATH_IS_COLLAPSED = 'conversionpro/display_settings/collapse';
    const XML_PATH_COLLAPSE_QTY = 'conversionpro/display_settings/collapse_qty';
    
    const XML_PATH_NAV_TO_SEARCH_ENABLED           = 'conversionpro/nav_to_search/enabled';
    const XML_PATH_NAV_TO_SEARCH_BLACKLIST_ENABLED = 'conversionpro/nav_to_search/blacklist_enabled';
    const XML_PATH_NAV_TO_SEARCH_BLACKLIST         = 'conversionpro/nav_to_search/blacklist';
    const XML_PATH_CATEGORY_QUERY_TYPE             = 'conversionpro/nav_to_search/category_query_type';
    const XML_PATH_NAV2SEARCH_BY                   = 'conversionpro/nav_to_search/nav_to_search_search_by';
    const XML_PATH_NAV2SEARCH_RELEVANCE            = 'conversionpro/nav_to_search/relevance_rename';
    
    const XML_PATH_ANALYTICS_CUST_ID = 'conversionpro/anlx_settings/cid';
    const XML_PATH_ANALYTICS_HOST    = 'conversionpro/anlx_settings/host';
    
    const XML_PATH_CROSSSELL = 'conversionpro/crosssell_settings/crosssell_enabled';
    const XML_PATH_CROSSSELL_LIMIT = 'conversionpro/crosssell_settings/crosssell_limit';
    
    const XML_PATH_UPSELL = 'conversionpro/crosssell_settings/upsell_enabled';
    const XML_PATH_UPSELL_LIMIT = 'conversionpro/crosssell_settings/upsell_limit';
    
    const XML_PATH_DEBUG_REQUEST = 'conversionpro/advanced/request_show';
    
    const PRICE_RANGE_TEMPLATE = 'PRICE_RANGE';
    
    protected $_permittedHandles = [
        'catalog_category',
        'catalogsearch_result'
    ];
    
    protected $engineStatus = null;
    
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
      * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    
    public function isEnabled($store = null)
    {
        $isEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
        if ($isEnabled && $this->getCurrentCategory())
            $isEnabled = $this->isEnabledForCategory($this->getCurrentCategory(), $store);
        return $isEnabled;
    }
    
    /**
     * Check if Celebros engine is available
     *
     * @return bool
     */
    public function isActiveEngine()
    {
        if ($this->engineStatus === null) {
            $engineStatus = false;
            if ($this->isEnabled()) {
                if ($this->getCurrentWorkHanlde() == 'catalog_category'
                && $this->isNavToSearchEnabled()) {
                    if ($this->isNavToSearchBlacklistEnabled()) {
                        $categoryId = (int)$this->_request->getParam('id', FALSE);
                        if ($categoryId) {
                            $blacklist = $this->getNavToSearchBlacklist();
                            if (!in_array($categoryId, $blacklist)) {
                                $engineStatus = true;
                            }
                        }
                    } else {
                        $engineStatus = true;
                    }
                } elseif ($this->getCurrentWorkHanlde() == 'catalogsearch_result') { 
                    $engineStatus = true;
                }
                
            }
            
            if ($this->isRequestDebug()) {
                $this->messageManager->addWarning(__('Celebros Search Engine') . ($engineStatus ? ' Enabled' : ' Disabled'));
            }
            $this->engineStatus = $engineStatus;
        }
        
        return $this->engineStatus;
    }
    
    public function getCurrentWorkHanlde()
    {
        return $this->_request->getModuleName() . '_' . $this->_request->getControllerName();
    }
    
    public function isPermittedHandle()
    {
        $currentHandle = $this->getCurrentWorkHanlde();
        return in_array($currentHandle, $this->_permittedHandles);
    }
    
    public function getPort($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PORT, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getHost($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HOST, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getSiteKey($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SITE_KEY, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function isMultiselectEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FILTER_MULTISELECT_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getProfileName($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PROFILE_NAME, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function isCampaignsEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CAMPAIGNS_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function isNavToSearchEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NAV_TO_SEARCH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function isNavToSearchBlacklistEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NAV_TO_SEARCH_BLACKLIST_ENABLED,
            ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getNavToSearchBlacklist($store = null)
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_NAV_TO_SEARCH_BLACKLIST,
            ScopeInterface::SCOPE_STORE, $store);
        $value = empty($value) ? [] : explode(',', $value);
        return $value;
    }
    
    public function getCategoryQueryType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_QUERY_TYPE, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function isEnabledForCategory(Category $category, $store = null)
    {
        return $this->isNavToSearchEnabled($store)
            && (!$this->isNavToSearchBlacklistEnabled()
                || !in_array($category->getId(), $this->getNavToSearchBlacklist($store)));
    }
    
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }
    
    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }
    
    public function getAnalyticsCustId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ANALYTICS_CUST_ID, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getAnalyticsHost($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ANALYTICS_HOST, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getNav2SearchBy($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NAV2SEARCH_BY, ScopeInterface::SCOPE_STORE, $store);    
    }
    
    public function isTextualNav2Search()
    {
        return ($this->getNav2SearchBy() == 'textual') ? true : false;
    }
    
    public function isCrosssellEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL, ScopeInterface::SCOPE_STORE, $store);    
    }
    
    public function getCrosssellLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL_LIMIT, ScopeInterface::SCOPE_STORE, $store);   
    }
    
    public function isUpsellEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_UPSELL, ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getUpsellLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_UPSELL_LIMIT, ScopeInterface::SCOPE_STORE, $store);   
    }
    
    public function isCollapsed($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IS_COLLAPSED, ScopeInterface::SCOPE_STORE, $store);   
    }
    
    public function collapseQty($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COLLAPSE_QTY, ScopeInterface::SCOPE_STORE, $store);   
    }
    
    public function isRequestDebug($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG_REQUEST, ScopeInterface::SCOPE_STORE, $store);     
    }
    
    public function isRelevanceNav2Search($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NAV2SEARCH_RELEVANCE,
            ScopeInterface::SCOPE_STORE,
            $store
        );    
    }
    
    public function getFilterType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRICE_FILTER_TYPE,
            ScopeInterface::SCOPE_STORE,
            $store
        );    
    }
    
    public function getPriceUrlTemplate()
    {
        $query = [
            'price' => self::PRICE_RANGE_TEMPLATE];
        return $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);    
    }
}
