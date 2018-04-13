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
namespace Celebros\ConversionPro\Model\Config\Source;

class CampaignTypes
{
    public $campaignTypes = [
        'Banners' => 'banners',
        'Recommended Messages' => 'recommended_messages',
        'Custom Message' => 'custom_message'
    ];
    
    public $handles = [
        'Search Page' => 'catalogsearch_result_index',
        'nav2search' => 'catalog_category_view'
    ];

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->handles as $groupName => $handle) {
            $groupValue = [];
            foreach ($this->campaignTypes as $typeName => $type) {
                $groupValue[] = [
                    'label' => $typeName,
                    'value' => $handle . ':' . $type
                ];
            }
            $options[] = [
                'label' => $groupName,
                'value' => $groupValue
            ];
        }
        
        return $options;
    }
}