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
namespace Celebros\ConversionPro\Block\System\Config\Form\Field;

use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Data\Form\Element\AbstractElement;

class Logs extends \Magento\Config\Block\System\Config\Form\Field
{
    const LOG_FILE_PREFIX = 'celebros_';
    
    /**
     * @var DirectoryList
     */
    protected $directoryList;
    
    /**
     * @var array
     */
    protected $actions = [
        'download' => 'Download', 
        'cleanup' => 'Delete'
    ];
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        DirectoryList $directoryList,
        array $data = []
    ) {
        $this->directoryList = $directoryList;
        parent::__construct($context, $data);
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Celebros_ConversionPro::system/config/logs.phtml');
        
        return $this;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function render(AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        
        return parent::render($element);
    }

    /**
     * Return available actions
     *
     * @return array
     */
    public function getActions() : array
    {
        return $this->actions;
    }
    
    /**
     * Return action url by actioname
     *
     * @param string $actionName
     * @return string|null
     */
    public function getUrlByAction(
        string $actionName,
        string $fileName
    ): ?string {
        if (isset($this->actions[$actionName])) {
            return $this->_urlBuilder->getUrl(
                'conversionpro/config/log' . $actionName,
                ['filename' => $fileName]
            ); 
        }
        
        return null;
    }

    /**
     * Return action label for frontend
     *
     * @param string $actionName
     * @return string|null
     */
    public function getActionLabel(string $actionName): ?string
    {
        return (isset($this->actions['actionName'])) ? __($this->actions['actionName']) : null;
    }

    /**
     * Return list of files in current log folder
     *
     * @return array
     */
    public function getLogFilesList() : array
    {
        $files = scandir($this->directoryList->getPath('log'));
        $result = [];
        foreach ($files as $key => $file) {
            if (strpos($file, ".log") !== false) {
                if (false !== strpos($file, self::LOG_FILE_PREFIX)) {
                    $result[] = $file;
                }
            }
        }
        
        return $result;
    }
}