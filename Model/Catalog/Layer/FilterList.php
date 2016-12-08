<?php
namespace Celebros\ConversionPro\Model\Catalog\Layer;

use \Magento\Catalog\Model\Layer;
use \Magento\Framework\DataObject;
use \Magento\Framework\Simplexml\Element as XmlElement;

class FilterList extends \Magento\Catalog\Model\Layer\FilterList
{
    const QUESTION_FILTER = 'question';

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
     * @var Magento\Framework\Simplexml\Element
     */
    protected $response;
    
    public $appliedFilters = [];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        Layer\FilterableAttributeListInterface $filterableAttributes,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        array $filters = [])
    {
        $this->filterTypes[self::QUESTION_FILTER] =
            'Celebros\ConversionPro\Model\Catalog\Layer\Filter\Question';
        $this->request = $request;
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        parent::__construct($objectManager, $filterableAttributes, $filters);
    }

    public function getFilters(Layer $layer)
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::getFilters($layer);
        }

        $priceQuestion = null;
        
        if (!count($this->filters)) {
            $this->filters = [];
            // $response = $this->_getResponse($layer);
            $response = $this->searchHelper->getCustomResults();
            $questions = $response->QwiserSearchResults->Questions;
//print_r($questions);die;
            foreach ($questions->children() as $question) {
                $this->filters[] = $this->createQuestionFilter($question, $layer);
                $this->appliedFilters[] = $question->getAttribute('Text');
            }
        }

        $remFilters = array_diff($this->searchHelper->getFilterRequestVars(), $this->appliedFilters);
        foreach ($remFilters as $fltr) {
            $remFilters = array_merge($this->searchHelper->getAltRequestVars($fltr), $remFilters);
        }
        $remFilters = array_unique($remFilters);
        foreach ($this->request->getParams() as $var => $value) {
            if (in_array($var, $remFilters)) {
                $question = $this->searchHelper->getQuestionByField($var, 'Text');
                if ($question) {
                    if (!$priceQuestion) {
                        $priceQuestion = $question;
                    }
                    $var = $question->getAttribute('Text');
                    $this->createQuestionFilter($question, $layer)->apply($this->request);
                    $this->appliedFilters[] = $var;    
                }
            }
            
            if ($var == 'price' && !in_array($var, $this->appliedFilters)) {
                $priceQuestion = $this->searchHelper->getPriceQuestionMock();
//print_r($priceQuestion);die;
                //print_r($priceQuestion);die;
                $this->createQuestionFilter($priceQuestion, $layer)->apply($this->request);
                $this->appliedFilters[] = $var; 
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
            $filterClassName, [
                'data' => [
                    'question' => $question,
                    'answers' => $answers,
                    'eanswers' => $extraAnswers],
                'layer' => $layer]);

        return $filter;
    }
}
