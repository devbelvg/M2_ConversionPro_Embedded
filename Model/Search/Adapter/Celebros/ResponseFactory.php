<?php

/**
 * Celebros
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_ConversionPro
 */

namespace Celebros\ConversionPro\Model\Search\Adapter\Celebros;

use Magento\Framework\Simplexml\Element as XmlElement;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Search\Response\{Aggregation, QueryResponse};

class ResponseFactory
{
    protected $objectManager;
    protected $documentFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        DocumentFactory $documentFactory,
        BucketFactory $bucketFactory
    ) {
        $this->objectManager = $objectManager;
        $this->documentFactory = $documentFactory;
        $this->bucketFactory = $bucketFactory;
    }

    public function getSearchResults($rawResponse): ?XmlElement
    {
        return $rawResponse->QwiserSearchResults ?? null;
    }

    public function create($rawResponse)
    {
        $documents = [];
        $searchResult = $this->getSearchResults($rawResponse);
        $total = $searchResult->getAttribute('RelevantProductsCount');
        $products = $searchResult->Products;
        $entityMapping = $this->prepareEntityRowIdMapping($products);
        $score = count($products->children());
        foreach ($products->children() as $rawDocument) {
            $entityId = $entityMapping[$rawDocument->getAttribute('MagId')] ?? false;
            if ($entityId) {
                $rawDocument->setAttribute('EntityId', $entityId);
                $documents[] = $this->documentFactory->create($rawDocument, $score--);
            }
        }
        $questions = $searchResult->Questions;
        $buckets = [];
        foreach ($questions->children() as $rawDocument) {
            $buckets[] = $this->bucketFactory->create($rawDocument);
        }

        $aggregations = $this->objectManager->create(
            Aggregation::class,
            ['buckets' => $buckets]
        );

        return $this->objectManager->create(
            QueryResponse::class,
            [
                'documents' => $documents,
                'aggregations' => $aggregations,
                'total' => $total
            ]
        );
    }

    public function prepareEntityRowIdMapping($products)
    {
        $ids = [];
        foreach ($products->children() as $rawDocument) {
            foreach ($rawDocument->Fields->children() as $rawField) {
                $name = $rawField->getAttribute('name');
                $value = $rawField->getAttribute('value');
                if ($name == 'mag_id') {
                    $ids[$value] = $value;
                    $rawDocument->setAttribute('MagId', $value);
                }
            }
        }

        $productMetadata = $this->objectManager->get(ProductMetadataInterface::class);
        if ($productMetadata->getEdition() == 'Community') {
            return $ids;
        }

        $products = $this->objectManager->create(Product::class);
        $collection = $products->getCollection()
            ->addFieldToFilter('row_id', $ids);

        $mapping = [];
        foreach ($collection as $item) {
            $mapping[$item->getRowId()] = $item->getEntityId();
        }

        return $mapping;
    }
}
