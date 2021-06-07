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

use Magento\Catalog\Model\Layer;
use Magento\Framework\Simplexml\Element as XmlElement;

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

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $specialTypes = [
        'swatch' => '_checkSwatch'
    ];

    public function __construct(
        Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Layer $layer,
        Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = $this->searchHelper->getFilterValue($this->getRequestVar());
        if (!empty($filter) && !in_array($filter, $this->searchHelper->appliedFilters)) {
            $values = $this->searchHelper->filterValueToArray($filter);
            foreach ($values as $value) {
                $text = $this->getOptionText($value);
                $item = $this->_createItem($text, $value);
                $item->setSelectedValues($values);
                $this->getLayer()->getState()->addFilter($item);
            }

            $this->_updateItems($values);
            $this->searchHelper->appliedFilters[] = $filter;
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
            foreach ($this->_items as $item) {
                $item->setSelectedValues($values);
            }
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

    /**
     * Return question type
     *
     * @return string Question type
     */
    public function getType(): string
    {
        if (!$this->type) {
            if (!$type = $this->_checkSpecialType()) {
                $type = (string) strtolower($this->getQuestion()->getAttribute('Type'));
            }

            $this->type = $type;
        }

        return $this->type;
    }

    /**
     * Check if current question corresponds to any special types
     *
     * @return string|bool
     */
    protected function _checkSpecialType(): ?string
    {
        foreach ($this->specialTypes as $specialType => $methodName) {
            if ($this->$methodName()) {
                return $specialType;
            }
        }

        return null;
    }

    /**
     * Special type check function
     *
     * @return bool
     */
    protected function _checkSwatch(): bool
    {
        if ($swatches = $this->searchHelper->extractDynamicProperty($this->getQuestion(), 'Swatches')) {
            return (bool) ($swatches instanceof XmlElement) ? $swatches->getAttribute('value') : false;
        }

        return false;
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
                if ($answer->getAttribute('Id') == $optionId) {
                    return $this->_prepareAnswerText($answer);
                }
            }
        }

        $qAnswers = $this->searchHelper->getQuestionAnswers($this->getQuestion()
            ->getAttribute('Id'))->Answers->Answer;
        foreach ($qAnswers as $answer) {
            if ($answer->getAttribute('Id') == $optionId) {
                return $this->_prepareAnswerText($answer);
            }
        }

        return __('Unknown value');
    }

    /**
     * Collect answers from response data
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $items = [];
        if (!$this->hasAnswers()) {
            return $items;
        }

        /* collect all regular answers */
        foreach ($this->getAnswers()->children() as $answer) {
            $items[] = $answer;
        }

        /* collect all extra answers */
        foreach ($this->getEanswers()->children() as $answer) {
            $items[] = $answer;
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

    /**
     * Create filter item object
     *
     * @param   string $label
     * @param   mixed $value
     * @param   int $count
     * @param   string $swatchImage
     * @return  \Magento\Catalog\Model\Layer\Filter\Item
     */
    protected function _createItem(
        $label,
        $value,
        $count = 0,
        $swatchImage = null
    ) {
        return $this->_filterItemFactory->create()
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count)
            ->setSwatchImage($swatchImage);
    }

    /**
     * Initialize filter items
     *
     * @return  \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items = [];
        foreach ($data as $itemData) {
            $items[] = $this->_createItem(
                $this->_prepareAnswerText($itemData),
                $itemData->getAttribute('Id'),
                $itemData->getAttribute('ProductCount'),
                $itemData->getAttribute('ImageUrl')
            );
        }

        $this->_items = $items;

        return $this;
    }
}
