<?php
namespace Celebros\ConversionPro\Model\Config\Source;

class CategoryQueryType
{
    const NAME                 = 'name';
    const FULL_PATH            = 'full_path';
    const NAME_AND_PARENT_NAME = 'name_and_parent_name';
    const NAME_AND_ROOT_NAME   = 'name_and_root_name';

    public function toArray()
    {
        return [
            self::NAME                 => __('Category Name'),
            self::FULL_PATH            => __('Full category path'),
            self::NAME_AND_PARENT_NAME => __('Category and category parent name'),
            self::NAME_AND_ROOT_NAME   => __('Category and category root name')];
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