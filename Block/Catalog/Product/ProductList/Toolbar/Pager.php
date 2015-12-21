<?php
namespace Celebros\ConversionPro\Block\Catalog\Product\ProductList\Toolbar;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * @var\Celebros\ConversionPro\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Celebros\ConversionPro\Helper\Data $helper,
        array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getCurrentPage()
    {
        if ($this->hasData('current_page'))
            return $this->getData('current_page');
        return parent::getCurrentPage();
    }

    public function setCollection($collection)
    {
        if (!$this->helper->isEnabled())
            return parent::setCollection($collection);

        // just set collection object
        $this->_collection = $collection;
        return $this;

    }

    public function getFirstNum()
    {
        if ($this->hasData('first_num'))
            return $this->getData('first_num');
        return parent::getFirstNum();
    }

    public function getLastNum()
    {
        if ($this->hasData('last_num'))
            return $this->getData('last_num');
        return parent::getLastNum();
    }

    public function getTotalNum()
    {
        if ($this->hasData('total_num'))
            return $this->getData('total_num');
        return parent::getTotalNum();
    }

    public function isFirstPage()
    {
        if (!$this->helper->isEnabled())
            return parent::isFirstPage();
        return $this->getCurrentPage() == 1;
    }

    public function getLastPageNum()
    {
        if ($this->hasData('last_page_num'))
            return $this->getData('last_page_num');
        return parent::getLastPageNum();
    }

    public function isLastPage()
    {
        if (!$this->helper->isEnabled())
            return parent::isLastPage();
        return $this->getCurrentPage() >= $this->getLastPageNum();
    }

    public function getPages()
    {
        if (!$this->helper->isEnabled())
            return parent::getPages();

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
        if (!$this->helper->isEnabled())
            return parent::getPreviousPageUrl();
        return $this->getPageUrl(max($this->getCurrentPage() - 1, 1));
    }

    public function getNextPageUrl()
    {
        if (!$this->helper->isEnabled())
            return parent::getNextPageUrl();
        return $this->getPageUrl(min($this->getCurrentPage() + 1, $this->getLastPageNum()));
    }

    public function getLastPageUrl()
    {
        if (!$this->helper->isEnabled())
            return parent::getLastPageUrl();
        return $this->getPageUrl($this->getLastPageNum());
    }

    protected function _initFrame()
    {
        if (!$this->helper->isEnabled())
            return parent::_initFrame();

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
                } elseif ($currentPage > $lastPageNum() - $half) {
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