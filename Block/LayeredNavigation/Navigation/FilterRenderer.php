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

namespace Celebros\ConversionPro\Block\LayeredNavigation\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;

class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer
{
    protected $templateByType = [
        'default' => 'default.phtml',
        'swatch' => 'swatch.phtml',
        'price' => 'price.phtml'
    ];

    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        $type = $filter->getType();
        $this->setTemplate($this->getTemplateByType($type));
        $this->assign('filterItems', $filter->getItems());
        $this->assign('filterType', $filter->getRequestVar());
        $html = $this->_toHtml();
        $this->assign('filterItems', []);

        return $html;
    }

    /**
     * Return template according to question type
     *
     * @param string $type
     * @return string
     */
    protected function getTemplateByType(string $type): string
    {
        $template = isset($this->templateByType[$type])
            ? $this->templateByType[$type]
            : $this->templateByType['default'];
        return sprintf('Celebros_ConversionPro::layered_navigation/layer/filter/%s', $template);
    }
}
