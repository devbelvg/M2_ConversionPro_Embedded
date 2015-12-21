<?php
namespace Celebros\ConversionPro\Model\Search\Adapter\Celebros;

use Magento\Framework\Object;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\Request\Query\Bool as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;

class Mapper
{
    protected $searchHelper;

    public function __construct(
        \Celebros\ConversionPro\Helper\Search $searchHelper)
    {
        $this->searchHelper = $searchHelper;
    }

    public function buildQuery(RequestInterface $request)
    {
        // $params = new Object();
        $params = $this->searchHelper->getSearchParams();
        $this->processQuery(
            $request->getQuery(),
            $params,
            BoolQuery::QUERY_CONDITION_MUST);
        return $params;
    }

    protected function processQuery(
        RequestQueryInterface $query, Object $params, $conditionType)
    {
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                $this->processMatchQuery($query, $params, $conditionType);
                break;
            case RequestQueryInterface::TYPE_BOOL:
                $this->processBoolQuery($query, $params, $conditionType);
                break;
            case RequestQueryInterface::TYPE_FILTER:
                $this->processFilterQuery($query, $params, $conditionType);
                break;
        }
    }

    protected function processMatchQuery(
        MatchQuery $query, Object $params, $conditionType /* ignored */)
    {
        $queryText = $params->hasQueryText() ? $params->getQueryText() . ' ' : '';
        $queryText .= $query->getValue();
        $params->setQuery($queryText);
    }

    protected function processBoolQuery(
        BoolQuery $query, Object $params, $conditionType)
    {
        $this->processBoolQueryCondition(
            $query->getMust(),
            $params,
            BoolQuery::QUERY_CONDITION_MUST);

        $this->processBoolQueryCondition(
            $query->getShould(),
            $params,
            BoolQuery::QUERY_CONDITION_SHOULD);

        $this->processBoolQueryCondition(
            $query->getMustNot(),
            $params,
            BoolQuery::QUERY_CONDITION_NOT);
    }

    protected function processFilterQuery(
        FilterQuery $query, Object $params, $conditionType)
    {
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $this->processQuery($query->getReference(), $params, $conditionType);
                break;
            case FilterQuery::REFERENCE_FILTER:
                $this->processFilter($query->getReference(), $params, $conditionType);
                break;
        }
    }

    protected function processBoolQueryCondition(
        array $subQueryList, Object $params, $conditionType)
    {
        foreach ($subQueryList as $subQuery) {
            $this->processQuery($subQuery, $params, $conditionType);
        }
    }

    // see Magento/Framework/Search/Adapter/Mysql/Filter/Builder.php
    protected function processFilter(
        RequestFilterInterface $filter, Object $params, $conditionType /* ignored */)
    {
        if ($filter->getType() == RequestFilterInterface::TYPE_TERM) {
            $filters = $params->hasFilters() ? $params->getFilters() : [];
            $filters[$filter->getField()] = $filter->getValue();
            $params->setFilters($filters);
        } /* ignore otherwise */
    }
}