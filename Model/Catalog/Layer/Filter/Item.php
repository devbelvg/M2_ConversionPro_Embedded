<?php
namespace Celebros\ConversionPro\Model\Catalog\Layer\Filter;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;
    
    /**
     * Construct
     *
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Theme\Block\Html\Pager $htmlPagerBlock
     * @param \Celebros\ConversionPro\Helper\Search $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Celebros\ConversionPro\Helper\Search $helper,
        array $data = []
    ) {
        parent::__construct($url, $htmlPagerBlock, $data);
        $this->helper = $helper;
    }
    
    public function getUrl()
    {
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
    
    public function getRemoveUrl()
    {
        if (!$this->hasSelectedValues() || empty($this->getSelectedValues()))
            return parent::getRemoveUrl();
            
        /** @var array $values */
        $values = $this->getSelectedValues();
        $values = array_diff($values, [$this->getValue()]);
        if (empty($values))
            return parent::getRemoveUrl();
        
        $query = [
            $this->getFilter()->getRequestVar() => implode(',', $values),
            // exclude current page from urls
            $this->_htmlPagerBlock->getPageVarName() => null];
        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
    
    public function isSelected()
    {
        $previous_search = $this->helper->getFilterValue($this->getFilter()->getRequestVar());
        if ($previous_search && in_array($this->getValue(), explode(',', $previous_search))) { 
            return true;
        }
        
        return false;
    }
    
}