<?php

namespace Hakfist\CustomerRegistration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OdooCustomerErrorLog extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('odoo_customer_error_log', 's_no'); // Initialize table and primary key field
    }
}
