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
use Magento\Framework\App\Response\Http\FileFactory as ResponseFileFactory;

class LogDownload extends LogAbstract
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        File $file,
        DirectoryList $directoryList,
        ResponseFileFactory $fileFactory
    ) {
        parent::__construct($context, $file, $directoryList);
        $this->fileResponseFactory = $fileFactory;
    }
    
    public function logFileProcess()
    {
        if ($this->file->isReadable($this->filePath)) {
            $content = $this->file->fileGetContents($this->filePath);
        } else {
            $this->messageManager->addError(__("An error occurred. Log file is not exist or not readable")); 
        }    

        if (isset($content)) {
            return $this->fileResponseFactory->create(
                $this->fileName,
                $content,
                DirectoryList::TMP
            );
        } else {
            $this->messageManager->addError(__("An error occurred. File content is empty")); 
        }
    }
}
