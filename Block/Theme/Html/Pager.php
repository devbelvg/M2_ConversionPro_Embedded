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
namespace Celebros\ConversionPro\Block\Theme\Html;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * @var\Celebros\ConversionPro\Helper\Data
     */
    protected $helper;

    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        array $data = [])
    {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        parent::__construct($context, $data);       
    }


    protected function _construct()
    {
        parent::_construct();
        if ($this->helper->isActiveEngine()) {
            $blockData = $this->searchHelper->getToolbarData();
            foreach ($blockData->getData() as $key=>$param) {
                $this->setData($key, $param);
            }
        }
    }
    
    public function setCollection($collection)
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::setCollection($collection);
        }
       
        // just set collection object
        $this->_collection = $collection;
        return $this;
    }
    
    public function getCurrentPage()
    {
        if ($this->helper->isActiveEngine()) {
            return (int)$this->getData('current_page') + 1;
        } else {
            return parent::getCurrentPage();
        }
    }
    
    public function getTotalNum()
    {
        if ($this->helper->isActiveEngine()) {
            return (int)$this->getData('total_num');
        } else {
            return parent::getTotalNum();
        }
    }

    public function getFirstNum()
    {
        if ($this->helper->isActiveEngine()) {
            return ($this->getCurrentPage()  - 1) * $this->getLimit() + 1;
        } else {
            return parent::getFirstNum();
        }
    }

    public function getLastNum()
    {
        if ($this->helper->isActiveEngine()) {
            $collection = $this->getCollection();
            return ($this->getFirstNum() - 1) + $collection->count();
        } else {
            return parent::getLastNum();
        }
    }
    
    public function getLastPageNum()
    {
        if ($this->helper->isActiveEngine()) {
            return (int)$this->getData('last_page_num');
        } else {
            return parent::getLastPageNum();
        }
    }

    public function isLastPage()
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::isLastPage();
        }
        
        return $this->getCurrentPage() >= $this->getLastPageNum();
    }

    public function getPages()
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::getPages();
        }
        
        if ($this->getLastPageNum() <= $this->_displayPages) {
            return range(1, $this->getLastPageNum());
        } else {
            $half = ceil($this->_displayPages / 2);
            $currentPage = $this->getCurrentPage();
            $lastPageNum = $this->getLastPageNum();
            if ($currentPage >= $half
                && $currentPage <= $this->getLastPageNum() - $half)
            {
                $start = $currentPage - $half + 1;
                $finish = $start + $this->_displayPages - 1;
            } elseif ($currentPage < $half) {
                $start = 1;
                $finish = $this->_displayPages;
            } elseif ($currentPage > $lastPageNum - $half) {
                $finish = $lastPageNum;
                $start = $finish - $this->_displayPages + 1;
            }
            return range($start, $finish);
        }
    }

    public function getPreviousPageUrl()
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::getPreviousPageUrl();
        }
        
        return $this->getPageUrl(max($this->getCurrentPage() - 1, 1));
    }

    public function getNextPageUrl()
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::getNextPageUrl();
        }
        
        return $this->getPageUrl(min($this->getCurrentPage() + 1, $this->getLastPageNum()));
    }

    public function getLastPageUrl()
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::getLastPageUrl();
        }
        
        return $this->getPageUrl($this->getLastPageNum());
    }

    protected function _initFrame()
    {
        if (!$this->helper->isActiveEngine()) {
            return parent::_initFrame();
        }

        if (!$this->isFrameInitialized()) {
            $start = 0;
            $end = 0;

            $lastPageNum = $this->getLastPageNum();
            $currentPage = $this->getCurrentPage();
            if ($lastPageNum <= $this->getFrameLength()) {
                $start = 1;
                $end = $lastPageNum;
            } else {
                $half = ceil($this->getFrameLength() / 2);
                if ($currentPage >= $half
                    && $currentPage <= $lastPageNum - $half)
                {
                    $start = $currentPage - $half + 1;
                    $end = $start + $this->getFrameLength() - 1;
                } elseif ($currentPage < $half) {
                    $start = 1;
                    $end = $this->getFrameLength();
                } elseif ($currentPage > $lastPageNum - $half) {
                    $end = $lastPageNum;
                    $start = $end - $this->getFrameLength() + 1;
                }
            }
            $this->_frameStart = $start;
            $this->_frameEnd = $end;

            $this->_setFrameInitialized(true);
        }
        return $this;
    }
    
}