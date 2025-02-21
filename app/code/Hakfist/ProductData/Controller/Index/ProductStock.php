<?php

namespace Hakfist\ProductData\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ProductStock extends Action
{
    protected $resultPageFactory;
    protected $productRepository;
    protected $getProductSalableQty;
    protected $timezone;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductRepository $productRepository,
        GetProductSalableQtyInterface $getProductSalableQty,
        TimezoneInterface $timezone
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    public function execute()
    {
        $sku = $this->getRequest()->getParam('sku');

        if (!$sku) {
            $this->messageManager->addErrorMessage(__('SKU is required'));
            return $this->_redirect('/');
        }

        try {
            $product = $this->productRepository->get($sku);
            $stockQty = $this->getProductSalableQty->execute($sku, $product->getStore()->getWebsiteId());
            $currentDate = $this->timezone->date()->format('Y-m-d');
            $nextArrivalDate = date('Y-m-d', strtotime($currentDate . ' + 7 days'));

            $resultPage = $this->resultPageFactory->create();

            // Pass data to the block or template
            $resultPage->getLayout()->getBlock('product.stock.info')->setData([
                'sku' => $sku,
                'stock_qty' => $stockQty,
                'next_arrival_date' => $nextArrivalDate,
            ]);

            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('/');
        }
    }
}
