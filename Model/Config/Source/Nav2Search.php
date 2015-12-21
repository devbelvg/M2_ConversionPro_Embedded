<?php
namespace Celebros\ConversionPro\Model\Config\Source;

class Nav2Search
{
    const TEXTUAL   = 'textual';
    const ANSWER_ID = 'answer_id';

    public function toArray()
    {
        return [
            self::TEXTUAL   => __('Textual Queries'),
            self::ANSWER_ID => __('Answer Ids')];
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