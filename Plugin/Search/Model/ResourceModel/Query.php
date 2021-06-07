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
namespace Celebros\ConversionPro\Plugin\Search\Model\ResourceModel;

use Celebros\ConversionPro\Helper\Data as Helper;
use Magento\Search\Model\ResourceModel\Query as MagentoResourceModelQuery;
use Magento\Framework\Model\AbstractModel;
use Magento\Search\Model\Query as QueryModel;

class Query
{
    /**
     * @param \Celebros\ConversionPro\Helper\Data $helper
     * @return void
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * @param MagentoResourceModelQuery $resourceQuery
     * @param callable $proceed
     * @param AbstractModel $object
     * @param string $value
     * @return MagentoResourceModelQuery
     */
    public function aroundLoadByQueryText(
        MagentoResourceModelQuery $resourceQuery,
        callable $proceed,
        AbstractModel $object,
        $value
    ) {
        if ($this->helper->isActiveEngine() && $this->helper->isPermittedHandle()) {
            return $resourceQuery;
        }
        
        return $proceed($object, $value);
    }
    
    /**
     * @param MagentoResourceModelQuery $resourceQuery
     * @param callable $proceed
     * @param QueryModel $query
     * @return void
     */
    /*public function aroundSaveNumResults(
        MagentoResourceModelQuery $resourceQuery,
        callable $proceed,
        QueryModel $query
    ) {
        if ($this->helper->isActiveEngine() && $this->helper->isPermittedHandle()) {
            return;
        }

        return $proceed($query);
    }*/
}
