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
namespace Celebros\ConversionPro\Model\Catalog\Layer;

use Magento\Catalog\Model\Layer;
use Magento\Framework\DataObject;
use Magento\Framework\Simplexml\Element as XmlElement;
use Magento\Catalog\Model\Config\LayerCategoryConfig;
use Celebros\ConversionPro\Model\Catalog\Layer\Filter\Question;

class FilterList extends \Magento\Catalog\Model\Layer\FilterList
{
    public const QUESTION_FILTER = 'question';
    public const APPLIED_FILTERS_ATTRIBUTE = 'SideText';
    public const PRICE_FILTER_NAME = 'Price';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;

    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;
    
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var Magento\Framework\Simplexml\Element
     */
    protected $response;

    public $appliedFilters = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Layer\FilterableAttributeListInterface $filterableAttributes
     * @param \Celebros\ConversionPro\Helper\Data $helper
     * @param \Celebros\ConversionPro\Helper\Search $searchHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        Layer\FilterableAttributeListInterface $filterableAttributes,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $filters = []
    ) {
        $this->filterTypes[self::QUESTION_FILTER] =
            Question::class;
        $this->request = $request;
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        $this->productMetadata = $productMetadata;
        $this->parentConstruct($objectManager, $filterableAttributes, $filters);
    }

    protected function parentConstruct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Layer\FilterableAttributeListInterface $filterableAttributes,
        array $filters
    ) {
        if (version_compare($this->productMetadata->getVersion(), '2.4.0', '<')) {
            parent::__construct($objectManager, $filterableAttributes, $filters);
        } else {
            $layerCategoryConfig = $objectManager->get(LayerCategoryConfig::class);
            parent::__construct($objectManager, $filterableAttributes, $layerCategoryConfig, $filters);
        }
    }

    public function sortFilters($questions): array
    {
        $priceSortOrder = $this->helper->getPriceFilterPosition();
        $questionsList = [];
        $sort = 1;
        foreach ($questions->children() as $question) {
            if ($priceSortOrder == $sort) {
                $sort++;
            }

            if ($question->getAttribute(self::APPLIED_FILTERS_ATTRIBUTE) == self::PRICE_FILTER_NAME) {
                $questionsList[$priceSortOrder] = $question;
            } else {
                $questionsList[$sort++] = $question;
            }
        }

        ksort($questionsList);

        return $questionsList;
    }

    public function getFilters(Layer $layer)
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::getFilters($layer);
        }

        if (!count($this->filters)) {
            $this->filters = [];
            $response = $this->searchHelper->getCustomResults();
            $questions = $response->QwiserSearchResults->Questions;
            $questionsList = $this->sortFilters($questions);
            foreach ($questionsList as $question) {
                $this->filters[] = $this->createQuestionFilter($question, $layer);
                $this->appliedFilters[] = $question->getAttribute(self::APPLIED_FILTERS_ATTRIBUTE);
            }

            $remFilters = array_diff($this->searchHelper->getFilterRequestVars(), $this->appliedFilters);
            foreach ($remFilters as $fltr) {
                $remFilters = array_merge($this->searchHelper->getAltRequestVars($fltr), $remFilters);
            }

            $priceQuestion = $this->searchHelper->getPriceQuestionMock();

            $remFilters = array_unique($remFilters);
            foreach ($this->request->getParams() as $var => $value) {
                if (in_array($var, $remFilters)
                    && !in_array($var, $this->appliedFilters)
                ) {
                    $question = $this->searchHelper->getQuestionByField($var, self::APPLIED_FILTERS_ATTRIBUTE);
                    if ($question) {
                        $var = $question->getAttribute(self::APPLIED_FILTERS_ATTRIBUTE);
                        $this->createQuestionFilter($question, $layer)->apply($this->request);
                        $this->appliedFilters[] = $var;
                    }
                }

                if ($var == 'price'
                    && !in_array($priceQuestion->getAttribute(self::APPLIED_FILTERS_ATTRIBUTE), $this->appliedFilters)
                ) {
                    $this->createQuestionFilter($priceQuestion, $layer)->apply($this->request);
                    $this->appliedFilters[] = $priceQuestion->getAttribute(self::APPLIED_FILTERS_ATTRIBUTE);
                }
            }
        }

        return $this->filters;
    }

    protected function createQuestionFilter(XmlElement $question, Layer $layer)
    {
        // get answers
        $answers = $question->Answers;
        $extraAnswers = $question->ExtraAnswers;

        // create filter object
        $filterClassName = $this->filterTypes[self::QUESTION_FILTER];
        $filter = $this->objectManager->create(
            $filterClassName,
            [
                'data' => [
                    'question' => $question,
                    'answers' => $answers,
                    'eanswers' => $extraAnswers
                ],
                'layer' => $layer
            ]
        );

        return $filter;
    }
}
