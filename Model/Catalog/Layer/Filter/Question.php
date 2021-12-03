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
use Magento\Store\Model\StoreManagerInterface;
use Celebros\ConversionPro\Helper\Data;
use Celebros\ConversionPro\Helper\Search;
use Magento\Framework\Pricing\Helper\Data\Proxy as PriceHelper;
use Magento\Framework\App\RequestInterface;

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

    /**
     * @param Layer\Filter\ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param Data $helper
     * @param Search $searchHelper
     * @param PriceHelper $priceHelper
     * @param array $data
     */
    public function __construct(
        Layer\Filter\ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        Layer\Filter\Item\DataBuilder $itemDataBuilder,
        Data $helper,
        Search $searchHelper,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        $this->priceHelper = $priceHelper;
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
    }

    /**
     * @param RequestInterface $request
     * @return void
     */
    public function apply(RequestInterface $request)
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

    /**
     * @param array $values
     * @return void
     */
    protected function _updateItems(array $values)
    {
        if (!$this->helper->isMultiselectEnabled()) {
            $this->_items = [];
        } else {
            $this->_items = $this->getItems();
            foreach ($this->_items as $item) {
                $item->setSelectedValues($values);
            }
        }
    }

    /**
     * @return string
     */
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
     * @return string
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

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }

    /**
     * @param int $optionId
     * @return string
     */
    protected function getOptionText($optionId)
    {
        if ($this->_isPrice()) {
            return $this->parseAndPreparePriceLabel($optionId);
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

    /**
     * @return bool
     */
    protected function _isPrice()
    {
        return $this->hasQuestion()
            && $this->getQuestion()->getAttribute('Type') == 'Price';
    }

    /**
     * @param XmlElement $answer
     * @return string
     */
    protected function _prepareAnswerText(
        XmlElement $answer,
        string $position = null
    ): string {
        $text = $answer->getAttribute('Text');
        if ($this->_isPrice()) {
            $id = $answer->getAttribute('Id');
            $text = $this->parseAndPreparePriceLabel($id, $position);
        }

        return $text;
    }

    /**
     * @param string $string
     * @return string|null
     */
    protected function parseAndPreparePriceLabel(
        string $string,
        string $position = null
    ): ?string {
        if (preg_match('@^_P(\d+)_(\d+)$@', $string, $matches)) {
            if (count($matches) == 3) {
                if ($position == 'first') {
                    return __('under') . " " . $this->priceHelper->currency($matches[2], true, false);
                } elseif ($position == 'last') {
                    return __('over') . " " . $this->priceHelper->currency($matches[1], true, false);
                } else {
                    return $this->priceHelper->currency($matches[1], true, false)
                        . " - " . $this->priceHelper->currency($matches[2], true, false);
                }
            }
        }

        return null;
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
        $last = count($data);
        foreach ($data as $index => $itemData) {
            $position = ($index == 0) ? 'first' : null;
            $position = ($index == $last - 1) ? 'last' : $position;
            $items[] = $this->_createItem(
                $this->_prepareAnswerText($itemData, $position),
                $itemData->getAttribute('Id'),
                $itemData->getAttribute('ProductCount'),
                $itemData->getAttribute('ImageUrl')
            );
        }

        $this->_items = $items;

        return $this;
    }
}
