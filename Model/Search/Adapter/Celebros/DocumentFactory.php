<?php
namespace Celebros\ConversionPro\Model\Search\Adapter\Celebros;

use Magento\Framework\Simplexml\Element as XmlElement;

class DocumentFactory
{
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(XmlElement $rawDocument)
    {
        $documentId = null;
        $entityId = 'mag_id';
        foreach ($rawDocument->Fields->children() as $rawField) {
            $name = $rawField->getAttribute('name');
            $value = $rawField->getAttribute('value');
            if ($name == $entityId) {
                $documentId = $value;
            } else {
                $fields[$name] = $this->objectManager->create(
                    'Magento\Framework\Search\AbstractKeyValuePair',
                    ['name' => $name, 'value' => $value]);
            }
        }
        
        $fields['score'] = $this->objectManager->create(
            'Magento\Framework\Search\AbstractKeyValuePair',
            ['name' => 'score', 'value' => 0]
        );
      
        return $this->objectManager->create(
            'Celebros\ConversionPro\Model\Search\Adapter\Celebros\Document',
            ['documentFields' => $fields, 'documentId' => $documentId]
        );
    }
}