<?php
namespace Celebros\ConversionPro\Model\CatalogSearch\Search;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

class RequestGenerator extends \Magento\CatalogSearch\Model\Search\RequestGenerator
{
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $qNames = [];
    
    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;

    public function __construct(
        CollectionFactory $productAttributeCollectionFactory,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper
    ) {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        parent::__construct($productAttributeCollectionFactory);
    }

    public function generate()
    {
        $requests = parent::generate();
        if (!$this->helper->isActiveEngine()) {
            return parent::generate();
        }

        $requests['quick_search_container'] = $this->generateQuickSearchRequest();

        return $requests;
    }

    protected function generateQuickSearchRequest()
    {
        $response = $this->searchHelper->getAllQuestions();
        $request = [];
        if (!empty($response->Questions)) {
            foreach ($response->Questions->children() as $question) {
                $name = $question->getAttribute('Text');
                if (!in_array($name, $this->qNames)) {
                    $queryName = $name . '_query';
                    $request['queries']['quick_search_container']['queryReference'][] = [
                        'clause' => 'should',
                        'ref' => $queryName
                    ];
                    $filterName = $name . self::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [
                            ['ref' => $filterName]
                        ]
                    ];
                    $bucketName = $name . self::BUCKET_SUFFIX;
                    $request['filters'][$filterName] = [
                        'type' => FilterInterface::TYPE_TERM,
                        'name' => $filterName,
                        'field' => $name,
                        'value' => '$' . $name . '$'
                    ];
                    $request['aggregations'][$bucketName] = [
                        'type' => BucketInterface::TYPE_TERM,
                        'name' => $bucketName,
                        'field' => $name,
                        'metric' => [
                            ["type" => "count"]
                        ]
                    ];
                    $this->qNames[] = $name;
                }
            }
        }
      
        return $request;
    }
}
