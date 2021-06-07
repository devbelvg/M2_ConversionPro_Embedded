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
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Api\Search\DocumentInterface;

class DocumentFactory
{
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(XmlElement $rawDocument, $score = 0)
    {
        $attributes = [];
        $documentId = null;
        $entityId = 'mag_id';
        foreach ($rawDocument->Fields->children() as $rawField) {
            $name = $rawField->getAttribute('name');
            $value = $rawField->getAttribute('value');
            if ($name == $entityId) {
                $documentId = $rawDocument->getAttribute('EntityId');
            }
        }

        $attributes['score'] = new AttributeValue(
            [
                AttributeInterface::ATTRIBUTE_CODE => '_score',
                AttributeInterface::VALUE => $score
            ]
        );
                
        return new Document(
            [
                DocumentInterface::ID => $documentId,
                CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $attributes,
            ]
        );
    }
}
