<?php

namespace Hakfist\ProductData\Block;

use Magento\Framework\View\Element\Template;

class ProductStock extends Template
{
    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getStockQty()
    {
        return $this->getData('stock_qty');
    }

    public function getNextArrivalDate()
    {
        return $this->getData('next_arrival_date');
    }
}
