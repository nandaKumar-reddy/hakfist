<?php

namespace Hakfist\HomeBanners\Model\ResourceModel;

class HomeBanners extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
        
        
           
    protected function _construct()
    {
        $this->_init('Hakfist_homebanners', 'homebanners_id');
    }
}
