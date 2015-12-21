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
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Setup\ModuleContextInterface;
class Info extends \Magento\Config\Block\System\Config\Form\Field
{
    const MODULE_NAME = 'Celebros_Celexport';
    protected $_helper;
    protected $_module;
    protected $_moduleDb;
    
    public function __construct(
        \Celebros\ConversionPro\Helper\Data $helper,
        \Magento\Framework\Module\ResourceInterface $moduleDb
    ) {
        $this->_helper = $helper;
        $this->_moduleDb = $moduleDb;
    }
    
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $id = $element->getHtmlId();
        $html = '<tr id="row_' . $id . '">';
        $html .= '<td class="label">' . __('Module Version') . '</td><td class="value">' . $this->getModuleVersion() . '</td><td class="scope-label"></td>';
        $html .= '</tr>';
       
        return $html;
    }
    
    public function getModuleVersion()
    {
        return $this->_moduleDb->getDbVersion('Celebros_ConversionPro');
    }
    
}