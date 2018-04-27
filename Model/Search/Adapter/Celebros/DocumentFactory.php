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
        $documentId = null;
        $entityId = 'mag_id';
        foreach ($rawDocument->Fields->children() as $rawField) {
            $name = $rawField->getAttribute('name');
            $value = $rawField->getAttribute('value');
            if ($name == $entityId) {
                $documentId = $rawDocument->getAttribute('EntityId');
            } else {
                $fields[$name] = $this->objectManager->create(
                    'Magento\Framework\Search\AbstractKeyValuePair',
                    ['name' => $name, 'value' => $value]);
            }
        }
        
        $fields['score'] = $this->objectManager->create(
            'Magento\Framework\Search\AbstractKeyValuePair',
            ['name' => 'score', 'value' => $score]
        );
      
        return $this->objectManager->create(
            'Celebros\ConversionPro\Model\Search\Adapter\Celebros\Document',
            ['documentFields' => $fields, 'documentId' => $documentId]
        );
    }
}