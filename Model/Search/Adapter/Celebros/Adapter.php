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
namespace Celebros\ConversionPro\Model\Search\Adapter\Celebros;

use Magento\Framework\DataObject;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;

class Adapter implements AdapterInterface
{
    protected $mapper;

    protected $searchHelper;

    protected $responseFactory;

    public function __construct(
        Mapper $mapper,
        \Celebros\ConversionPro\Helper\Search $searchHelper,
        ResponseFactory $responseFactory
        )
    {
        $this->mapper = $mapper;
        $this->searchHelper = $searchHelper;
        $this->responseFactory = $responseFactory;
    }

    public function query(RequestInterface $request)
    {
        $params = $this->mapper->buildQuery($request);
        $documents = $this->executeQuery($params);
        $response = [
            'documents' => $documents,
            'aggregations' => []];
        return $this->responseFactory->create($response);
    }

    public function executeQuery(DataObject $params)
    {
        return $this->searchHelper->getCustomResults($params);
    }


}