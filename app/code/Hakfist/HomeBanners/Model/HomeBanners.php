<?php

namespace Hakfist\HomeBanners\Model;

class HomeBanners extends \Magento\Framework\Model\AbstractModel
{
        
        
    protected function _construct()
    {
        $this->_init('Hakfist\HomeBanners\Model\ResourceModel\HomeBanners');
    }
        
        
    public function getAvailableStatuses()
    {
                
                
        $availableOptions = ['1' => 'Enable',
                          '0' => 'Disable'];
                
        return $availableOptions;
    }
}
