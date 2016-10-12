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
namespace Celebros\ConversionPro\Model\Search;

class AdapterFactory extends \Magento\Search\Model\AdapterFactory
{
    /**
     * @var \Celebros\ConversionPro\Helper\Data
     */
    protected $helper;

    /**
     * @var \Celebros\ConversionPro\Helper\Search
     */
    protected $searchHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $adapters,
        $path,
        $scopeType,
        \Celebros\ConversionPro\Helper\Data $helper,
        \Celebros\ConversionPro\Helper\Search $searchHelper
    ) {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        parent::__construct($objectManager, $scopeConfig, $adapters, $path, $scopeType);
    }

    public function create(array $data = [])
    {
        if ($this->helper->isActiveEngine()) {
            $adapter = $this->objectManager->create(
                'Celebros\ConversionPro\Model\Search\Adapter\Celebros\Adapter');
            return $adapter;
        }
        return parent::create($data);
    }

}