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
use Magento\Framework\Search\Response\Bucket;

class BucketFactory
{
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(XmlElement $rawDocument): Bucket
    {
        $documentId = null;
        $values = [];
        $label = $rawDocument->getAttribute('SideText');
        foreach ($rawDocument->Answers->children() as $rawAnswerDocument) {
            $values[] = $this->objectManager->create(
                \Magento\Framework\Search\Response\Aggregation\Value::class,
                [
                    'value' => str_replace("_P", "", $rawAnswerDocument->getAttribute('Id')),
                    'metrics' => [
                        'value' => str_replace("_P", "", $rawAnswerDocument->getAttribute('Id')),
                        'label' => $rawAnswerDocument->getAttribute('Text'),
                        'count' => $rawAnswerDocument->getAttribute('ProductCount')
                    ]
                ]
            );
        }

        return $this->objectManager->create(
            \Magento\Framework\Search\Response\Bucket::class,
            [
                'name' => $label,
                'values' => $values
            ]
        );
    }
}
