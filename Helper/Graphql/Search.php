<?php

/*
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

class Search extends \Celebros\ConversionPro\Helper\Search
{
    protected $filterTypes = [
        'Single' => ['eq'],
        'Multi' => ['in'],
        'Range' => [
            'from',
            'to'
        ]
    ];
    
    public function getSearchParams()
    {
        $params = parent::getSearchParams();
        $params->setPageSize(9999);
        $this->currentSearchParams = $params;
        $params->setSortBy(
            $this->extractSortOrder(
                $this->_getRequest()->getParam('variables', false)
            )
        );

        return $this->currentSearchParams;
    }
    
    protected function extractSortOrder(string $variables)
    {
        $variables = json_decode($variables, true);
        $sort = $variables['sort'] ?? [];
        foreach ($sort as $sortOrder => $dir) {
            return [$sortOrder, $dir];
        }
    }
    
    public function getValueFromRequest($requestVar)
    {
        $variables = $this->_getRequest()->getParam('variables', false);
        $variables = json_decode($variables, true);
        $filters = $variables['filters'] ?? [];
        $result = null;
        if (isset($filters[$requestVar])) {
            $result = $filters[$requestVar];
        }

        if (!empty($result)) {
            return $this->parseGraqlFilter($result);
        }

        return null;
    }
    
    public function parseGraqlFilter(array $filterData): ?string
    {
        $filterType = $this->getFilterType($filterData);
        $func = 'checkAndParse' . $filterType;
        if (method_exists($this, $func)) {
            return $this->$func($filterData);
        }
    
        return null;
        /*
        if (isset($filterData['in'])) {
            return implode(",", $filterData['in']);
        }

        if (isset($filterData['eq'])) {
            return $filterData['eq'];
        }

        if (
            isset($filterData['from'])
            && isset($filterData['to'])
        ) {
            return "_P" . $filterData['from'] . "_" . $filterData['to'];
        }

        return null;*/
    }
    
    public function getFilterType(array $filterData): ?string
    {
        
        foreach ($this->filterTypes as $type => $conds) {
            $status = true;
            foreach ($conds as $cond) {
                $status = $status && isset($filterData[$cond]);
            }
            
            if ($status) {
                return $type;
            }
        }
        
        return null;
    }
    
    public function checkAndParseSingle(array $filterData): string
    {
        if (isset($filterData['eq'])) {
            return $filterData['eq'];
        }
    }
    
    public function checkAndParseMulti(array $filterData): string
    {
        if (isset($filterData['in'])) {
            return implode(",", $filterData['in']);
        }
    }
    
    public function checkAndParseRange(array $filterData): string
    {
        if (isset($filterData['from'])
            && isset($filterData['to'])
        ) {
            return "_P" . $filterData['from'] . "_" . $filterData['to'];
        }
    }
}
