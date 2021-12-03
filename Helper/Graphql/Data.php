<?php

/**
 * Celebros
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_ConversionPro
 */

namespace Celebros\ConversionPro\Helper\Graphql;

use Magento\Store\Model\ScopeInterface;

class Data extends \Celebros\ConversionPro\Helper\Data
{
    public function checkEngineAvConditions(): bool
    {
        return $this->isFilters() || $this->isAutoComplete() || $this->isFilterInputs();
    }

    public function isRequestDebug($store = null): bool
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG_REQUEST,
            ScopeInterface::SCOPE_STORE,
            $store
        ) && !$this->isGraphql();
    }
    
    protected function getOpName()
    {
        return $this->_request->getParam('operationName', false);
    }

    public function isGraphql(): bool
    {
        return ($this->state->getAreaCode() == 'graphql');
    }

    public function getGraphqlCurrentPage(): int
    {
        if ($this->isGraphql()) {
            $vars = $this->_request->getParam('variables', false);
            if ($vars) {
                $vars = json_decode($vars, true);
                if (isset($vars['currentPage'])) {
                    return (int) $vars['currentPage'];
                }
            }
        }

        return 0;
    }

    public function getGraphqlPageSize(): int
    {
        if ($this->isGraphql()) {
            $vars = $this->_request->getParam('variables', false);
            if ($vars) {
                $vars = json_decode($vars, true);
                if (isset($vars['pageSize'])) {
                    return (int) $vars['pageSize'];
                }
            }
        }

        return 0;
    }

    public function isCategory(): bool
    {
        if ($this->isGraphql()) {
            $opName = $this->getOpName();
            return (bool) (strtolower($opName) == 'getcategories');
        }

        return (bool) ($this->getCurrentWorkHandle() == 'catalog_category');
    }

    public function isSearch(): bool
    {
        if ($this->isGraphql()) {
            $opName = $this->getOpName();
            return (bool) (strtolower($opName) == 'productsearch');
        }

        return (bool) ($this->getCurrentWorkHandle() == 'catalogsearch_result');
    }

    public function isFilters(): bool
    {
        if ($this->isGraphql()) {
            $opName = $this->getOpName();
            if ($opName == 'getProductFiltersByCategory'
                && $this->isNavToSearchEnabled()
            ) {
                $vars = $this->_request->getParam('variables', false);
                if ($vars) {
                    $vars = json_decode($vars, true);
                    $catId = $vars['categoryIdFilter']['eq'];
                    return !$this->checkBlackList($catId);
                }
            }

            return (strtolower($opName) == 'getproductfiltersbysearch');
        }

        return false;
    }

    public function isAutoComplete(): bool
    {
        if ($this->isGraphql()) {
            $opName = $this->getOpName();
            return (bool) (strtolower($opName) == 'getautocompleteresults');
        }

        return false;
    }

    public function isFilterInputs(): bool
    {
        if ($this->isGraphql()) {
            $opName = $this->getOpName();
            return (bool) (strtolower($opName) == 'getfilterinputs');
        }

        return false;
    }

    public function getCategoryId(): ?int
    {
        if ($this->isGraphql()) {
            $vars = $this->_request->getParam('variables', false);
            if ($vars) {
                $vars = json_decode($vars, true);
                return (int) $vars['filters']['category_id']['eq'];
            }

            return null;
        }

        return (int) $this->_request->getParam('id', false);
    }
    
    /**
     * @return bool
     */    
    public function isRedirectAvailable(): bool
    {
        return false;
    }
}
