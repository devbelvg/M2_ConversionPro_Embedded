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
namespace Celebros\ConversionPro\Model;

use Celebros\ConversionPro\Helper\Data as Helper;

class Logger extends \Monolog\Logger
{
    /**
     * @var Helper
     */
    protected $helper;
    
    /**
     * @param Helper             $helper     Celebros data helper
     * @param string             $name       The logging channel
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct(
        Helper $helper,
        $name,
        array $handlers = array(),
        array $processors = array()
    ) {
        $this->helper = $helper;
        $this->name = $name;
        $this->setHandlers($handlers);
        $this->processors = $processors;
    }
    
    /**
     * Adds a log record.
     *
     * @param  int     $level   The logging level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array())
    {
        if ($this->helper->isLogEnabled()) {
            return parent::addRecord($level, $message, $context);
        }
        
        return false;
    }
}