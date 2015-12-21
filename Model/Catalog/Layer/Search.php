<?php
namespace Celebros\ConversionPro\Model\Catalog\Layer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\ContextInterface;
use Magento\Catalog\Model\ResourceModel;
use Magento\Catalog\Model\Layer\StateFactory;

class Search extends \Magento\Catalog\Model\Layer\Search
{
    /**
     * @param ContextInterface $context
     * @param StateFactory $layerStateFactory
     * @param ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param ResourceModel\Product $catalogProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StateFactory $layerStateFactory,
        ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data);
    }
}