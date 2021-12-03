<?php

/*
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

declare(strict_types=1);

namespace Celebros\ConversionPro\Plugin\CatalogGraphQl\Model\Config;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Celebros\ConversionPro\Helper\Search;
use Celebros\ConversionPro\Helper\Data;

class FilterAttributeReader
{
    /**
     * @param Search $search
     * @return void
     */
    public function __construct(
        Search $search,
        Data $data
    ) {
        $this->search = $search;
        $this->data = $data;
    }

    /**
     * @param \Magento\CatalogGraphQl\Model\Config\FilterAttributeReader $reader
     * @param array $result
     * @return array
     */
    public function afterRead(
        \Magento\CatalogGraphQl\Model\Config\FilterAttributeReader $reader,
        array $result
    ) : array {
        $allQuestions = $this->search->getAllQuestions()->Questions->Question;
        foreach ($allQuestions as $question) {
            $attributeCode = str_replace(" ", "_", $question->getAttribute('SideText'));
            if ($attributeCode) {
                $result['ProductAttributeFilterInput']['fields'][$attributeCode] = [
                    'name' => $attributeCode,
                    'type' => 'FilterEqualTypeInput',
                    'arguments' => [],
                    'required' => false,
                    'description' => sprintf('Attribute label: %s', $attributeCode)
                ];
            }
        }
        
        $result['ProductAttributeFilterInput']['fields']['price'] = [
            'name' => 'price',
            'type' => 'FilterEqualTypeInput',
            'arguments' => [],
            'required' => false,
            'description' => sprintf('Attribute label: %s', 'price')
        ];

        return $result;
    }
}
