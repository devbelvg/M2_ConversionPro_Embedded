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

use Magento\Framework\Api\AbstractSimpleObject;

class Document extends AbstractSimpleObject implements \IteratorAggregate
{
    public const CUSTOM_ATTRIBUTES = 'document_fields';
    public const ID = 'document_id';

    public function __construct(
        $documentId,
        array $documentFields
    ) {
        $this->_data[self::CUSTOM_ATTRIBUTES] = $documentFields;
        $this->_data[self::ID] = $documentId;
    }

    public function getId()
    {
        return isset($this->_data[self::ID]) ? (int)$this->_data[self::ID] : false;
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function getCustomAttribute($attributeCode)
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode])
            ? $this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode]
            : null;
    }

    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $attributes = $this->getCustomAttributes();
        $attributes[$attributeCode] = $attributeValue;
        return $this->setCustomAttributes($attributes);
    }

    public function getCustomAttributes()
    {
        return $this->_get(self::CUSTOM_ATTRIBUTES);
    }

    public function setCustomAttributes(array $attributes)
    {
        return $this->setData(self::CUSTOM_ATTRIBUTES, $attributes);
    }

    public function getIterator()
    {
        $attributes = (array)$this->getCustomAttributes();
        return new \ArrayIterator($attributes);
    }
}
