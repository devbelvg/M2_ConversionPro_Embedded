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
                $fields[] = $this->objectManager->create(
                    'Magento\Framework\Search\DocumentField',
                    ['name' => $name, 'value' => $value]);
            }
        }
        return $this->objectManager->create(
            'Magento\Framework\Search\Document',
            ['documentFields' => $fields, 'documentId' => $documentId]);
    }
}