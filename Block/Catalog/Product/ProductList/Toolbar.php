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
namespace Celebros\ConversionPro\Block\Catalog\Product\ProductList;

use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Framework\Simplexml\Element as XmlElement;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;

    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;

    /**
     * @var XmlElement
     */
    protected $response;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Catalog\Helper\Product\ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Registry $registry,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        array $data = [])
    {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;

        parent::__construct(
            $context,
            $catalogSession,
            $catalogConfig,
            $toolbarModel,
            $urlEncoder,
            $productListHelper,
            $postDataHelper,
            $data
        );

        // set block module name required to use same template as original
        $this->setModuleName('Magento_Catalog');
        // set current page, limit, order to search helper instead of collection
        $this->searchHelper->setCurrentPage($this->getCurrentPage());
        $this->searchHelper->setPageSize($this->getLimit());
        $this->searchHelper->setOrder(
            $this->getCurrentOrder(),
            $this->getCurrentDirection()
        );
        
        $blockData = $this->searchHelper->getToolbarData();
        foreach ($blockData->getData() as $key=>$param) {
            $this->setData($key, $param);
        }
    }
    
    public function getAvailableOrders()
    {
        $avOrders = parent::getAvailableOrders();
        if ($this->helper->isRelevanceNav2Search()) {
            if (isset($avOrders['position'])) {
                unset($avOrders['position']);
            }
            
            $avOrders = array_merge(
                ['relevance' => 'Relevance'],
                $avOrders
            );
        }
        
        return $avOrders;
    }
    
    public function setCollection($collection)
    {
        if ($this->helper->isEnabled()) {
            $this->_collection = $collection;
            // setting current page, limit, order removed, see constructor
            return $this;
        } else {
            return parent::setCollection($collection);
        }
    }

    public function getTotalNum()
    {
        if ($this->helper->isEnabled()) {
            return (int)$this->getData('total_num');
        } else {
            return parent::getTotalNum();
        }
    }

    public function getFirstNum()
    {
        if ($this->helper->isEnabled()) {
            return ($this->getCurrentPage()  - 1) * $this->getLimit() + 1;
        } else {
            return parent::getFirstNum();
        }
    }

    public function getLastNum()
    {
        if ($this->helper->isEnabled()) {
            $collection = $this->getCollection();
            return ($this->getFirstNum() - 1) + $collection->count();
        } else {
            return parent::getLastNum();
        }
    }

    public function getLastPageNum()
    {
        if ($this->helper->isEnabled()) {
            return (int)$this->getData('last_page_num');
        } else {
            return parent::getLastPageNum();
        }
    }
    
}