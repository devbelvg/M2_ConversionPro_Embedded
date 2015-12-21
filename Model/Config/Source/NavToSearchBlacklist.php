<?php
namespace Celebros\ConversionPro\Model\Config\Source;

class NavToSearchBlacklist implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Catalog\Model\Resource\Category\Collection
     */
    protected $categoryCollection;

    /**
     * @var array
     */
    protected $options;

    public function __construct(\Magento\Catalog\Model\Resource\Category\Collection $categoryCollection)
    {
        $this->categoryCollection = $categoryCollection;
        $this->categoryCollection->addFieldToSelect('name');
    }

    public function toOptionArray($isMultiselect = false)
    {
        $options = $this->_toOptionArray();
        if (!$isMultiselect)
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        return $options;
    }

    protected function _toOptionArray()
    {
        if (is_null($this->options)) {
            // Create options
            $this->options = array();
            $categories = $this->categoryCollection->getItems();
            foreach ($categories as $category) {
                $pathIds = $category->getPathIds();

                // skip root and default categories
                if (count($pathIds) < 3)
                    continue;

                // remove root and default from path
                $pathIds = array_slice($pathIds, 2);

                $path = [];
                foreach ($pathIds as $pathId) {
                    if (isset($categories[$pathId]))
                        $path[] = $categories[$pathId]->getName();
                }
                $fullName = implode('/', $path);
                $this->options[] = [
                    'value' => $category->getId(),
                    'label' => $fullName];
            }
            // Sort options
            usort(
                $this->options,
                function($option1, $option2) {
                    return strcmp($option1['label'],$option2['label']);
                });
        }
        return $this->options;
    }
}