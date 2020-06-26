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
namespace Celebros\ConversionPro\Block\System\Config\Form\Field;

class TestButton extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Celebros_ConversionPro::system/config/test-button.phtml');
        return $this;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('conversionpro/config/testconnection'),
                'field_mapping' => str_replace('"', '\\"', json_encode($this->_getFieldMapping()))
            ]
        );

        return $this->_toHtml();
    }

    protected function _getFieldMapping()
    {
        return [
            'host' => 'conversionpro_general_settings_host',
            'sitekey' => 'conversionpro_general_settings_sitekey',
            'port' => 'conversionpro_general_settings_port'
        ];
    }
}
