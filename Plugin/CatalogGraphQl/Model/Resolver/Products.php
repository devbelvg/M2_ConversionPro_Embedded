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

namespace Celebros\ConversionPro\Plugin\CatalogGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\Resolver\Products as ProductsResolver;
use Celebros\ConversionPro\Helper\Search as Helper;

class Products
{
    /**
     * @param Helper $helper
     * @return void
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    public function beforeResolve(
        ProductsResolver $resolver,
        $field,
        $context,
        $info,
        $value,
        $args
    ) {
        if (isset($args['filter']['category_id']['eq'])) {
            $queryText = 'CatId' . $args['filter']['category_id']['eq'];
            $args['search'] = $queryText;
        }

        return [$field, $context, $info, $value, $args];
    }
}
