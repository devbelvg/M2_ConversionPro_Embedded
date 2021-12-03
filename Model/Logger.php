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

namespace Celebros\ConversionPro\Model;

use Celebros\ConversionPro\Helper\Data as Helper;

class Logger extends \Monolog\Logger
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Helper $helper
     * @param string $name
     * @param HandlerInterface[] $handlers
     * @param callable[] $processors
     */
    public function __construct(
        Helper $helper,
        $name,
        array $handlers = [],
        array $processors = []
    ) {
        $this->helper = $helper;
        $this->name = $name;
        $this->setHandlers($handlers);
        $this->processors = $processors;
    }

    /**
     * Adds a log record.
     *
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addRecord($level, $message, array $context = [])
    {
        if ($this->helper->isLogEnabled()) {
            return parent::addRecord($level, $message, $context);
        }

        return false;
    }
    
    public function logCurrentUrl($requestUn)
    {
        if ($this->helper->isLogEnabled()) {
            $currentUrl = $this->helper->getCurrentUrl();
            return $this->info($requestUn . ' - Frontend Url: ' . $currentUrl);
        }
        
        return false;
    }
}
