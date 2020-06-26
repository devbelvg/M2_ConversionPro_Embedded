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
namespace Celebros\ConversionPro\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\HTTP\Client\Curl;
use Celebros\ConversionPro\Model\Search;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class TestConnection extends Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Celebros_ConversionPro::config_conversionpro';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    
    /**
     * @var Magento\Framework\Json\Helper\Data
     */
    private $json;

    /**
     * @var StripTags
     */
    private $tagFilter;
    
    /**
     * @var Search
     */
    private $searchModel;
    
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @param Context      $context
     * @param JsonFactory  $resultJsonFactory
     * @param JsonHelper   $tagFilter
     * @param StripTags    $tagFilter
     * @param Search       $searchModel
     * @param Curl         $curl
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        JsonHelper $jsonHelper,
        StripTags $tagFilter,
        Search $searchModel,
        Curl $curl
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->json = $jsonHelper;
        $this->tagFilter = $tagFilter;
        $this->searchModel = $searchModel;
        $this->curl = $curl;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];
        
        $params = $this->getRequest()->getParams();

        try {
            if (empty($params['host'])
            && empty($params['sitekey'])
            && empty($params['port'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Some of connection parameters are missed.')
                );
            }

            $testUrl = $this->prepareTestUrl($params);
            
            $this->curl->setOption(CURLOPT_HTTPHEADER, array('Accept: text/xml'));
            $this->curl->get($testUrl, []);
            
            $responseBody = $this->searchModel->parseXmlResponse($this->curl->getBody());
            if ($responseBody) {
                $result['success'] = true;
                unset($result['errorMessage']);
                $result['responseBody'] = $this->json->jsonEncode(
                    $responseBody
                );    
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $message = __($e->getMessage());
            $result['errorMessage'] = $this->tagFilter->filter($message);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
    
    protected function prepareTestUrl($params)
    {   
        return sprintf(
            '%s:%s/GetAllQuestions?Sitekey=%s',
            $params['host'],
            $params['port'],
            $params['sitekey']
        );
    }
}