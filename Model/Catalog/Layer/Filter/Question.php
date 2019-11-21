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

use \Magento\Catalog\Model\Layer;
use \Magento\Framework\Simplexml\Element as XmlElement;

class Question extends Layer\Filter\AbstractFilter
{
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;

    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $searchHelper;

    public function __construct(
        Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Layer $layer,
        Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        array $data = [])
    {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = $this->searchHelper->getFilterValue($this->getRequestVar());
        if (!empty($filter)) {
            $this->getLayer()->getProductCollection()->addFieldToFilter(
                $this->getRequestVar(),
                $filter
            );

            $values = $this->searchHelper->filterValueToArray($filter);
            foreach ($values as $value) {
                $text = $this->getOptionText($value);
                $item = $this->_createItem($text, $value);
                $item->setSelectedValues($values);
                $this->getLayer()->getState()->addFilter($item);
            }

            $this->_updateItems($values);
        }
    }

    protected function _updateItems(array $values)
    {
        if (!$this->helper->isMultiselectEnabled()) {
            // remove all items
            $this->_items = [];
        } else {
            $this->_items = $this->getItems();
            // remove selected items
            /*$this->_items = array_filter(
                $this->getItems(),
                function ($item) use (&$values) {
                    return !in_array($item->getValue(), $values);
                });*/
            foreach ($this->_items as $item)
                $item->setSelectedValues($values);
        }
    }

    public function getName()
    {
        if ($this->hasQuestionName()) {
            return $this->getQuestionName();
        }
        
        if (!$this->hasQuestion()) {
            return __('Unknown');
        }
        
        return $this->getQuestion()->getAttribute('Text');
    }

    public function getRequestVar()
    {
        if ($this->hasRequestVar()) {
            return $this->getRequestVar();
        }
        
        if ($this->_isPrice()) {
            return 'price';
        }
        
        if ($this->hasQuestion()) {
            $reqVar = $this->getQuestion()->getAttribute('SideText');
        }
        
        return $this->searchHelper->checkRequestVar($reqVar);
    }

    public function getCurrencySymbol()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }
    
    protected function getOptionText($optionId)
    {
        if ($this->_isPrice()) {
            if (preg_match('@^_P(\d+)_(\d+)$@', $optionId, $matches)) {
                $optionId = str_replace('_P', $this->getCurrencySymbol(), $optionId);
                return str_replace('_', ' - ' . $this->getCurrencySymbol(), $optionId);
            }
        }
        
        if ($this->hasAnswers()) {
            foreach ($this->getAnswers()->children() as $answer) {
                if ($answer->getAttribute('Id') == $optionId)
                    return $this->_prepareAnswerText($answer);
            }
        }
        
        foreach ($this->searchHelper->getQuestionAnswers($this->getQuestion()->getAttribute('Id'))->Answers->Answer as $answer) {
            if ($answer->getAttribute('Id') == $optionId) {
                return $this->_prepareAnswerText($answer); 
            }
        }
        
        return __('Unknown value');
    }

    protected function _getItemsData()
    {
        if (!$this->hasAnswers())
            return [];

        $items = [];
        foreach ($this->getAnswers()->children() as $answer) {
            $items[] = $this->_createItem(
                $this->_prepareAnswerText($answer),
                $answer->getAttribute('Id'),
                $answer->getAttribute('ProductCount'));
        }
        
        foreach ($this->getEanswers()->children() as $answer) {
            $items[] = $this->_createItem(
                $this->_prepareAnswerText($answer),
                $answer->getAttribute('Id'),
                $answer->getAttribute('ProductCount'));
        }
        
        return $items;
    }

    protected function _isPrice()
    {
        return $this->hasQuestion()
            && $this->getQuestion()->getAttribute('Type') == 'Price';
    }

    protected function _prepareAnswerText(XmlElement $answer)
    {
        $text = $answer->getAttribute('Text');
        if ($this->_isPrice()) {
            $id = $answer->getAttribute('Id');
            if (preg_match('@^_P(\d+)_(\d+)$@', $id, $matches)) {
                $text = str_replace('<min>', $matches[1], $text);
                $text = str_replace('<max>', $matches[2], $text);
            }
        }
        return $text;
    }
}