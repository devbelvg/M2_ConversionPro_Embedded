<?php
namespace Celebros\ConversionPro\Model\Search\Adapter\Celebros;

use Magento\Framework\Simplexml\Element as XmlElement;

class ResponseFactory
{
    protected $objectManager;

    protected $documentFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        DocumentFactory $documentFactory)
    {
        $this->objectManager = $objectManager;
        $this->documentFactory = $documentFactory;
    }

    public function create($rawResponse) {
        $documents = [];
        $products = $rawResponse['documents']->QwiserSearchResults->Products;
        foreach ($products->children() as $rawDocument) {
            /** @var \Magento\Framework\Search\Document[] $documents */
            $documents[] = $this->documentFactory->create($rawDocument);
        }

        // TEST
        $aggregations = $this->objectManager->create(
            'Magento\Framework\Search\Response\Aggregation',
            ['buckets' => []]);

        return $this->objectManager->create(
            'Magento\Framework\Search\Response\QueryResponse',
            ['documents' => $documents, 'aggregations' => $aggregations]);
    }

}
