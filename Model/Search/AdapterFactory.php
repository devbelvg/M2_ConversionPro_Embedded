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
    public function create(array $data = [])
    {
        $helper = $this->objectManager->get('Celebros\ConversionPro\Helper\Data');
        if ($helper->isActiveEngine(get_class($this))) {
            $adapter = $this->objectManager->create(
                'Celebros\ConversionPro\Model\Search\Adapter\Celebros\Adapter'
            );

            return $adapter;
        }

        return parent::create($data);
    }
}
