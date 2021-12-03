<?php

/**
 * Celebros
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_ConversionPro
 */

namespace Celebros\ConversionPro\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Message\MessageInterface as MessageInterface;
use Celebros\ConversionPro\Model\Config\Source\PriceFilterTypes;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_ENABLED  = 'conversionpro/general_settings/enabled';
    public const XML_PATH_HOST     = 'conversionpro/general_settings/host';
    public const XML_PATH_PORT     = 'conversionpro/general_settings/port';
    public const XML_PATH_SITE_KEY = 'conversionpro/general_settings/sitekey';
    public const XML_PATH_FILTER_MULTISELECT_ENABLED  = 'conversionpro/display_settings/filter_multiselect_enabled';
    public const XML_PATH_CAMPAIGNS_ENABLED           = 'conversionpro/display_settings/campaigns_enabled';
    public const XML_PATH_CAMPAIGNS_TYPE              = 'conversionpro/display_settings/campaigns_type';
    public const XML_PATH_PROFILE_NAME                = 'conversionpro/display_settings/profile_name';
    public const XML_PATH_PRICE_FILTER_TYPE           = 'conversionpro/display_settings/filter_price_type';
    public const XML_PATH_PRIC_FILTER_POSITION        = 'conversionpro/display_settings/filter_price_position';
    public const XML_PATH_GO_TO_PRODUCT_ON_ONE_RESULT = 'conversionpro/display_settings/go_to_product_on_one_result';
    public const XML_PATH_IS_COLLAPSED = 'conversionpro/display_settings/collapse';
    public const XML_PATH_COLLAPSE_QTY = 'conversionpro/display_settings/collapse_qty';
    public const XML_PATH_FILTER_SEARCH = 'conversionpro/display_settings/filter_search';
    public const XML_PATH_FILTER_SEARCH_QTY = 'conversionpro/display_settings/filter_search_min_qty';
    public const XML_PATH_FALLBACK_REDIRECT = 'conversionpro/display_settings/fallback_redirect';
    public const XML_PATH_FALLBACK_REDIRECT_URL = 'conversionpro/display_settings/fallback_redirect_url';
    public const XML_PATH_NAV_TO_SEARCH_ENABLED           = 'conversionpro/nav_to_search/enabled';
    public const XML_PATH_NAV_TO_SEARCH_BLACKLIST_ENABLED = 'conversionpro/nav_to_search/blacklist_enabled';
    public const XML_PATH_NAV_TO_SEARCH_BLACKLIST         = 'conversionpro/nav_to_search/blacklist';
    public const XML_PATH_CATEGORY_QUERY_TYPE             = 'conversionpro/nav_to_search/category_query_type';
    public const XML_PATH_NAV2SEARCH_BY                   = 'conversionpro/nav_to_search/nav_to_search_search_by';
    public const XML_PATH_NAV2SEARCH_RELEVANCE            = 'conversionpro/nav_to_search/relevance_rename';
    public const XML_PATH_ANSWER_ID_PREFIX                = 'conversionpro/nav_to_search/answer_id_prefix';
    public const XML_PATH_ANALYTICS_CUST_ID = 'conversionpro/anlx_settings/cid';
    public const XML_PATH_ANALYTICS_HOST    = 'conversionpro/anlx_settings/host';
    public const XML_PATH_CROSSSELL = 'conversionpro/crosssell_settings/crosssell_enabled';
    public const XML_PATH_CROSSSELL_LIMIT = 'conversionpro/crosssell_settings/crosssell_limit';
    public const XML_PATH_UPSELL = 'conversionpro/crosssell_settings/upsell_enabled';
    public const XML_PATH_UPSELL_LIMIT = 'conversionpro/crosssell_settings/upsell_limit';
    public const XML_PATH_DEBUG_REQUEST = 'conversionpro/advanced/request_show';
    public const XML_PATH_DEBUG_LOG = 'conversionpro/advanced/enable_log';
    public const RESPONSE_XML_LINK_ATTRIBUTE_NAME = 'Link';
    public const RESPONSE_XML_TITLE_ATTRIBUTE_NAME = 'Title';
    public const RESPONSE_XML_PRICE_ATTRIBUTE_NAME = 'Price';
    public const PRICE_RANGE_TEMPLATE = 'PRICE_RANGE';

    protected $permittedHandles = [
        'catalog_category',
        'catalogsearch_result'
    ];

    protected $engineStatus = null;

    protected $campaignsStatus = [];

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->registry = $registry;
        $this->state = $state;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->url = $urlInterface;
        parent::__construct($context);
    }

    public function isEnabled($store = null)
    {
        $isEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($isEnabled && $this->getCurrentCategory()) {
            $isEnabled = $this->isEnabledForCategory($this->getCurrentCategory(), $store);
        }

        return $isEnabled;
    }

    /**
     * Check if Celebros engine is available
     *
     * @return bool
     */
    public function isActiveEngine($source = null)
    {
        if ($this->engineStatus === null) {
            $this->engineStatus = $this->checkEngineStatus();
            if ($this->isRequestDebug()) {
                $this->sendRequestDebugMessages($this->engineStatus, $source);
            }
        }

        return $this->engineStatus;
    }

    protected function checkEngineStatus()
    {
        $engineStatus = false;
        if ($this->isEnabled()) {
            if ($this->isCategory()
                && $this->isNavToSearchEnabled()
            ) {
                $engineStatus = !$this->checkBlackList();
            } elseif ($this->isSearch()
                || $this->checkEngineAvConditions()
            ) {
                $engineStatus = true;
            }
        }

        return $engineStatus;
    }

    public function checkEngineAvConditions(): bool
    {
        return false;
    }

    protected function sendRequestDebugMessages($engineStatus, $source = null)
    {
        $message = [
            'title' => __('Celebros Search Engine'),
            'status' => ($engineStatus ? 'Enabled' : 'Disabled')
        ];

        if ($source) {
            $message['source'] =  $source;
        }

        $this->messageManager->getMessages()->deleteMessageByIdentifier('celebros_engine_status');

        if ($engineStatus) {
            $statusMessage = $this->messageManager->createMessage(
                MessageInterface::TYPE_SUCCESS,
                'celebros_engine_status'
            )->setText($this->prepareDebugMessage($message));
        } else {
            $statusMessage = $this->messageManager->createMessage(
                MessageInterface::TYPE_NOTICE,
                'celebros_engine_status'
            )->setText($this->prepareDebugMessage($message));
        }

        $this->messageManager->addMessage($statusMessage);
    }

    public function isCategory(): bool
    {
        return (bool) ($this->getCurrentWorkHandle() == 'catalog_category');
    }

    public function isSearch(): bool
    {
        return (bool) ($this->getCurrentWorkHandle() == 'catalogsearch_result');
    }

    public function getCategoryId(): ?int
    {
        return (int) $this->_request->getParam('id', false);
    }

    public function checkBlackList(int $catId = null): bool
    {
        if ($this->isNavToSearchBlacklistEnabled()) {
            if (!$catId) {
                $catId = $this->getCategoryId();
            }

            if ($catId) {
                return $this->isCatIdInBlackList($catId);
            }
        }

        return false;
    }

    public function isCatIdInBlackList(int $catId): bool
    {
        $blacklist = $this->getNavToSearchBlacklist();
        if (in_array($catId, $blacklist)) {
            return true;
        }

        return false;
    }

    public function prepareDebugMessage(array $data)
    {
        if (isset($data['title'])) {
            $str = strtoupper(__($data['title']));
            unset($data['title']);
            foreach ($data as $key => $val) {
                if ($val) {
                    $str .= ' >>> ' . ucfirst(__($key)) . ': ' . $val;
                }
            }

            return $str;
        }

        return false;
    }

    public function getCurrentWorkHandle()
    {
        return $this->_request->getModuleName() . '_' . $this->_request->getControllerName();
    }

    public function isPermittedHandle()
    {
        $currentHandle = $this->getCurrentWorkHandle();
        return in_array($currentHandle, $this->permittedHandles);
    }

    public function getPort($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PORT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getHost($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HOST,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getSiteKey($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SITE_KEY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isMultiselectEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FILTER_MULTISELECT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isRedirectToProductEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GO_TO_PRODUCT_ON_ONE_RESULT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getProfileName($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PROFILE_NAME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isCampaignsEnabled($type = null, $store = null): bool
    {
        $campaignsEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_CAMPAIGNS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if (!$type) {
            return $campaignsEnabled;
        }

        $campaignState = false;
        $currentHandle = $this->getFullActionName();
        $currentCampaignType = $currentHandle . ':' . $type;

        if (isset($this->campaignsStatus[$currentCampaignType])) {
            return $this->campaignsStatus[$currentCampaignType];
        }

        $campaignsEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_CAMPAIGNS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $campaignsTypes = explode(',', $this->scopeConfig->getValue(
            self::XML_PATH_CAMPAIGNS_TYPE,
            ScopeInterface::SCOPE_STORE,
            $store
        ));

        $campaignState = ($campaignsEnabled && in_array($currentCampaignType, $campaignsTypes));
        if ($this->isRequestDebug()) {
            $message = [
                'title' => __('Celebros Campaign'),
                'type' => $type,
                'status' => ($campaignState ? ' Enabled' : ' Disabled')
            ];

            if ($campaignState) {
                $this->messageManager->addSuccess($this->prepareDebugMessage($message));
            } else {
                $this->messageManager->addNotice($this->prepareDebugMessage($message));
            }
        }

        $this->campaignsStatus[$currentCampaignType] = $campaignState;

        return $campaignState;
    }

    public function getFullActionName(): string
    {
        return $this->_request->getModuleName()
            . '_' . $this->_request->getControllerName()
            . '_' . $this->_request->getActionName();
    }

    public function isNavToSearchEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NAV_TO_SEARCH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isNavToSearchBlacklistEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NAV_TO_SEARCH_BLACKLIST_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getNavToSearchBlacklist($store = null)
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_NAV_TO_SEARCH_BLACKLIST,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $value = empty($value) ? [] : explode(',', $value);

        return $value;
    }

    public function getCategoryQueryType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_QUERY_TYPE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isEnabledForCategory(
        Category $category,
        $store = null
    ) {
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
            self::XML_PATH_ANALYTICS_CUST_ID,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getAnalyticsHost($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ANALYTICS_HOST,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getNav2SearchBy($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NAV2SEARCH_BY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getAnswerIdPrefix($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ANSWER_ID_PREFIX,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isTextualNav2Search()
    {
        return ($this->getNav2SearchBy() == 'textual') ? true : false;
    }

    public function isCrosssellEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getCrosssellLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isUpsellEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_UPSELL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getUpsellLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_UPSELL_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isCollapsed($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IS_COLLAPSED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function collapseQty($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COLLAPSE_QTY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isRequestDebug($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG_REQUEST,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isLogEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG_LOG,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isRelevanceNav2Search($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NAV2SEARCH_RELEVANCE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getFilterType($store = null): array
    {
        return explode(',', $this->scopeConfig->getValue(
            self::XML_PATH_PRICE_FILTER_TYPE,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
    }

    public function isPriceDefault($store = null): bool
    {
        return in_array(PriceFilterTypes::DEF, $this->getFilterType($store));
    }

    public function isPriceSlider($store = null): bool
    {
        return in_array(PriceFilterTypes::SLIDER, $this->getFilterType($store));
    }

    public function isPriceInputs($store = null): bool
    {
        return in_array(PriceFilterTypes::INPUTS, $this->getFilterType($store));
    }

    public function getPriceUrlTemplate()
    {
        $query = [
            'price' => self::PRICE_RANGE_TEMPLATE
        ];

        return $this->_urlBuilder->getUrl(
            '*/*/*',
            [
                '_current' => true,
                '_use_rewrite' => true,
                '_query' => $query
            ]
        );
    }

    public function isFallbackRedirectEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FALLBACK_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function fallbackRedirectUrl($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FALLBACK_REDIRECT_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function filterValueToArray($value)
    {
        if (!is_array($value)) {
            if ($priceValue = $this->validateAndPreparePriceAnswer($value)) {
                return $priceValue;
            }

            return array_map('intval', explode(',', $value));
        }

        return (array)$value;
    }

    public function validateAndPreparePriceAnswer($value)
    {
        $array = explode("_", $value);
        if (count($array) == 3
            && (bool)$array[0] == false
            && strpos($array[1], "P") !== false
        ) {
            return [implode("_", $array)];
        }

        return false;
    }

    public function getPriceFilterPosition($store = null): int
    {
        $position = $this->scopeConfig->getValue(
            self::XML_PATH_PRIC_FILTER_POSITION,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return (int)$position;
    }

    public function isFilterSearchEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FILTER_SEARCH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getMinQtyForFilterSearch($store = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_FILTER_SEARCH_QTY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return string
     */
    public function toJsBool($value): string
    {
        return ((bool)$value) ? 'true' : 'false';
    }

    /**
     * @return string
     */
    public function getPriceTemplate(): string
    {
        return $this->priceHelper->currency("{price}", true, false);
    }

    /**
     * @return string
     */    
    public function getCurrentUrl(): string
    {
        return $this->url->getCurrentUrl();
    }
    
    /**
     * @return bool
     */    
    public function isRedirectAvailable(): bool
    {
        return true;
    }
}
