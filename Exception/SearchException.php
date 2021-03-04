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

namespace Celebros\ConversionPro\Exception;

use Magento\Framework\Simplexml\Element as XmlElement;

class SearchException
{
    public const RESPONSE_ERROR_CLASS = 'Celebros\ConversionPro\Exception\SearchResponseErrorException';
    public const SERVICE_ERROR_CLASS = 'Celebros\ConversionPro\Exception\SearchServiceErrorException';
    
    public $exceptionClass = '\Exception';
    public $message;
    public $code = 0;
    public $previous = null;
    
    public function __construct(
        $message,
        $code = 0,
        \Throwable $previous = null
    ) {
        $this->code = $code;
        $this->previous = $previous;
        
        if ($message instanceof XmlElement) {
            if ($message === false) {
                $this->message = __('Service response is empty');
                $this->exceptionClass = self::SERVICE_ERROR_CLASS;
            }

            // check if error is indicated in response
            if ($message->getAttribute('ErrorOccurred') == 'true') {
                if (isset($message->QwiserError)) {
                    $this->message = '';
                    if (null !== $message->QwiserError->getAttribute('MethodName')) {
                        $this->message .= sprintf(
                            'Error occured in method %s: ',
                            $message->QwiserError->getAttribute('MethodName')
                        );
                    }

                    $this->message .= (null !== $message->QwiserError->getAttribute('ErrorMessage'))
                        ? $message->QwiserError->getAttribute('ErrorMessage')
                        : __('Unknown error');
                }

                $this->exceptionClass = self::RESPONSE_ERROR_CLASS;
            }

            if (
                !isset($message->ReturnValue)
                || !$message->ReturnValue instanceof XmlElement
                || empty($message->ReturnValue)
            ) {
                $this->message = __('No return value in response');
                $this->exceptionClass = self::SERVICE_ERROR_CLASS;
            } 
        } else {
            $this->message = (string)$message;
        }
    }
    
    public function create()
    {
        $exceptionClass = $this->exceptionClass;
        if ($this->message) {
            return new $exceptionClass(
                $this->message,
                $this->code,
                $this->previous
            );
        }
        
        return null;
    }
}
