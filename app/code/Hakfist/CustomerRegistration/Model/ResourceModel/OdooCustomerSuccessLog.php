<?php

namespace Hakfist\CustomerRegistration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OdooCustomerSuccessLog extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('odoo_customer_success_log', 's_no'); // Initialize table and primary key field
    }
}
