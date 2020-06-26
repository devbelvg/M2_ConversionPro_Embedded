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
namespace Celebros\ConversionPro\Controller\Adminhtml\Config;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;

class LogCleanup extends LogAbstract
{
    public function logFileProcess()
    {
        if ($this->file->isWritable($this->filePath)) {
            $isDeleted = $this->file->deleteFile($this->filePath);
        } else {
            $isDeleted = false;
            $this->messageManager->addError(__("An error occurred. Log file is not exist or not writable")); 
        }
            
        if ($isDeleted) {
            $this->messageManager->addSuccess(__("Log file has been deleted"));
        } else {
            $this->messageManager->addError(__("An error occurred. Log file hasn't been deleted"));
        }
    }
}
