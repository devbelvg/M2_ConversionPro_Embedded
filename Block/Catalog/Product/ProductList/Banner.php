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
namespace Celebros\ConversionPro\Block\Catalog\Product\ProductList;

use Magento\Framework\Object;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Simplexml\Element as XmlElement;

class Banner extends Template
{
    const BANNER_CAMPAIGN_NAME = 'banners';
    
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;

    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;

    /**
     * @var XmlElement
     */
    protected $response;

    /**
     * @var Object
     */
    protected $bannerImage;

    /**
     * @var Object
     */
    protected $bannerFlash;

    /**
     * @var bool
     */
    protected $isResponseParsed = false;

    public function __construct(
        Template\Context $context,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        array $data = [])
    {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        parent::__construct($context, $data);
    }

    public function hasBannerImage()
    {
        $this->_parseResponse();
        return !is_null($this->bannerImage);
    }

    public function getBannerImage()
    {
        $this->_parseResponse();
        return $this->bannerImage;
    }

    public function hasBannerFlash()
    {
        $this->_parseResponse();
        return !is_null($this->bannerFlash);
    }

    public function getBannerFlash()
    {
        $this->_parseResponse();
        return $this->bannerFlash;
    }

    protected function _getResponse()
    {
        if (is_null($this->response)) {
            $params = $this->searchHelper->getSearchParams();
            $this->response = $this->searchHelper->getCustomResults($params);
        }
        
        return $this->response;
    }

    protected function _parseResponse()
    {
        if (!$this->helper->isCampaignsEnabled(self::BANNER_CAMPAIGN_NAME)) {
            return;
        }
        
        if ($this->isResponseParsed) {
            return;
        }

        $response = $this->_getResponse();
        if (!isset($response->QwiserSearchResults->QueryConcepts)) {
            $this->isResponseParsed = true;
            return;
        }
        
        foreach ($response->QwiserSearchResults->QueryConcepts->children() as $concept) {
            if (!isset($concept->DynamicProperties)) continue;
            $params = new \Magento\Framework\DataObject();
            foreach ($concept->DynamicProperties->children() as $property) {
                $value = $property->getAttribute('value');
                switch ($property->getAttribute('name')) {
                    case 'banner image':
                        $params->setImageUrl($value);
                        $this->bannerImage = $params;
                        break;
                    case 'banner landing page':
                        $params->setUrl($value);
                        $this->bannerImage = $params;
                        break;
                    case 'start datetime':
                        $params->setStartDatetime($value);
                        break;
                    case 'end datetime':
                        $params->setEndDatetime($value);
                        break;
                }
            }
        }

        $this->isResponseParsed = true;
    }
}
