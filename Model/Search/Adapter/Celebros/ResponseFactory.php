<?php
/**
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

    public function create($rawResponse)
    {
        $documents = [];
        $products = $rawResponse['documents']->QwiserSearchResults->Products;
        $entityMapping = $this->prepareEntityRowIdMapping($products);
        $score = 0;
        foreach ($products->children() as $rawDocument) {
            $entityId = isset($entityMapping[$rawDocument->getAttribute('MagId')]) ? $entityMapping[$rawDocument->getAttribute('MagId')] : $rawDocument->getAttribute('MagId');
            $rawDocument->setAttribute('EntityId', $entityId);
            /** @var \Magento\Framework\Search\Document[] $documents */
            $documents[] = $this->documentFactory->create($rawDocument, $score++);
        }

        $aggregations = $this->objectManager->create(
            'Magento\Framework\Search\Response\Aggregation',
            ['buckets' => []]);

        return $this->objectManager->create(
            'Magento\Framework\Search\Response\QueryResponse',
            ['documents' => $documents, 'aggregations' => $aggregations]);
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

        $productMetadata = $this->objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        if ($productMetadata->getEdition() != 'Enterprise') {
            return $ids; 
        }
        
        $products = $this->objectManager->create('Magento\Catalog\Model\Product');
        $collection = $products->getCollection()
            ->addFieldToFilter('row_id', $ids);
        
        $mapping = [];    
        foreach ($collection as $item) {
            $mapping[$item->getRowId()] = $item->getEntityId();
        }
        
        return $mapping;   
    }
}