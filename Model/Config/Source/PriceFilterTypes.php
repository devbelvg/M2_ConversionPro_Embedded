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
namespace Celebros\ConversionPro\Model\Config\Source;

class PriceFilterTypes
{
    const INPUTS = 'inputs';

    public function toArray()
    {
        return [
            0 => __('Default'),
            self::INPUTS => __('Inputs')];
    }

    public function toOptionArray()
    {
        $array = $this->toArray();
        $options = array_map(
            function ($value, $label) { return ['value' => $value, 'label' => $label]; },
            array_keys($array),
            $array);
        return $options;
    }
}