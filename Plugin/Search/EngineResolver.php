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
namespace Celebros\ConversionPro\Plugin\Search;

use Magento\Search\Model\EngineResolver as Resolver;
use Celebros\ConversionPro\Helper\Data as Helper;

class EngineResolver
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
     * Returns MySQL search engine if Celebros search is enabled 
     *
     * @param \Magento\Search\Model\EngineResolver $resolver
     * @param string $currentSearchEngine
     * @return string
     */
    public function afterGetCurrentSearchEngine(
        Resolver $resolver,
        $currentSearchEngine
    ) {
        if ($this->helper->isActiveEngine() && $this->helper->isPermittedHandle()) {
            $currentSearchEngine = $resolver::CATALOG_SEARCH_MYSQL_ENGINE;
        }       

        return $currentSearchEngine;
    }
}
