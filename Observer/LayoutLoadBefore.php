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
namespace Celebros\ConversionPro\Observer;

use Magento\Framework\Event\ObserverInterface;

class LayoutLoadBefore implements ObserverInterface
{
    protected $_request;
    protected $_layout;
    
    /**
     * Celebros ConversionPro Data Helper
     *
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $_helper;
    
    public $handleCases;
    
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Celebros\ConversionPro\Helper\Data $helper
    ) {
        $this->_layout = $context->getLayout();
        $this->_request = $context->getRequest();
        $this->_helper = $helper;
        
        $this->addCelHandle('catalog_product_view', 'catalog_product_view_celebros');
    }
    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $currentHandle = $observer->getEvent()->getFullActionName();
        if ($this->_helper->isActiveEngine(get_class($this))) {
            $this->_addHandleToLayout($observer, $currentHandle . '_celebros');
        }
        
        if (isset($this->handles[$currentHandle])) {
            $this->_addHandleToLayout($observer, $this->handles[$currentHandle]);
        }
    }
    
    protected function _addHandleToLayout($observer, $handleName)
    {   
        $layout = $observer->getEvent()->getData('layout');
        $layout->getUpdate()->addHandle($handleName);
       
        return $layout->getUpdate();
    }
    
    public function addCelHandle($handle, $celHandle)
    {
        $this->handles[$handle] = $celHandle;
    }
}
