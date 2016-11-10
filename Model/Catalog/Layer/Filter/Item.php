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
namespace Celebros\ConversionPro\Model\Catalog\Layer\Filter;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;
    
    /**
     * Construct
     *
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Theme\Block\Html\Pager $htmlPagerBlock
     * @param \Celebros\ConversionPro\Helper\Data $helper
     * @param \Celebros\ConversionPro\Helper\Search $searchHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        array $data = []
    ) {
        parent::__construct($url, $htmlPagerBlock, $data);
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
    }
    
    public function getUrl()
    {
        if ($this->helper->isActiveEngine()) {
            if (!$this->hasSelectedValues() || empty($this->getSelectedValues())) {
                return parent::getUrl();
            }
            
            if ($this->isSelected()) {
                return $this->getRemoveUrl();
            }
            
            /** @var array $values */
            $values =  $this->getSelectedValues();
            $values[] = $this->getValue();
            $query = [
                $this->getFilter()->getRequestVar() => implode(',', $values),
                // exclude current page from urls
                $this->_htmlPagerBlock->getPageVarName() => null];
            return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
        }
        
        return parent::getUrl();
    }
    
    public function getRemoveUrl()
    {
        if ($this->helper->isActiveEngine()) {
            if (!$this->hasSelectedValues() || empty($this->getSelectedValues())) {
                return parent::getRemoveUrl();
            }
                
            /** @var array $values */
            $values = $this->getSelectedValues();
            $values = array_diff($values, [$this->getValue()]);
            if (empty($values)) {
                $values = null;
            }

            $requestVar = $this->searchHelper->checkRequestVar($this->getFilter()->getRequestVar());           
            $query = [
                $requestVar => is_array($values) ? implode(',', $values) : $values,
                // exclude current page from urls
                $this->_htmlPagerBlock->getPageVarName() => null
            ];
            
            return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
        }
        
        return parent::getRemoveUrl();
    }
    
    public function checkRequestVar()
    {
        $requestVar = $this->getFilter()->getRequestVar();
        
    }
    
    public function isSelected()
    {
        $previous_search = $this->searchHelper->getFilterValue($this->getFilter()->getRequestVar());
        if ($previous_search && in_array($this->getValue(), explode(',', $previous_search))) { 
            return true;
        }
        
        return false;
    }
    
}