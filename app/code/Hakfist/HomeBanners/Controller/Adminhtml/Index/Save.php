<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hakfist\HomeBanners\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Hakfist\HomeBanners\Model\HomeBanners;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;


class Save extends \Magento\Backend\App\Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    protected $scopeConfig;
   
    protected $_escaper;
    protected $inlineTranslation;
    protected $_dateFactory;
    //protected $_modelNewsFactory;
  //  protected $collectionFactory;
   //  protected $filter;
    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
    ) {
       // $this->filter = $filter;
       // $this->collectionFactory = $collectionFactory;
        $this->dataPersistor = $dataPersistor;
         $this->scopeConfig = $scopeConfig;
         $this->_escaper = $escaper;
        $this->_dateFactory = $dateFactory;
         $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('homebanners_id');

            if (isset($data['status']) && $data['status'] === 'true') {
                $data['status'] = Block::STATUS_ENABLED;
            }
            if (empty($data['homebanners_id'])) {
                $data['homebanners_id'] = null;
            }

           
            /** @var \Magento\Cms\Model\Block $model */
            $model = $this->_objectManager->create('Hakfist\HomeBanners\Model\HomeBanners')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This Banner no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            
            if (isset($data['homebannersimage'][0]['name']) && isset($data['homebannersimage'][0]['tmp_name'])) {
                $data['homebannersimage'] ='/homebanners/'.$data['homebannersimage'][0]['name'];
            } elseif (isset($data['homebannersimage'][0]['name']) && !isset($data['homebannersimage'][0]['tmp_name'])) {
                $data['homebannersimage'] =$data['homebannersimage'][0]['name'];
            } else {
                $data['homebannersimage'] = null;
            }

            // if (isset($data['homebannersbanner'][0]['name']) && isset($data['homebannersbanner'][0]['tmp_name'])) {
            //     $data['homebannersbanner'] ='/homebanners/'.$data['homebannersbanner'][0]['name'];
            // } elseif (isset($data['homebannersbanner'][0]['name']) && !isset($data['homebannersbanner'][0]['tmp_name'])) {
            //     $data['homebannersbanner'] =$data['homebannersbanner'][0]['name'];
            // } else {
            //     $data['homebannersbanner'] = null;
            // }

            // if (isset($data['homebannershoverimage'][0]['name']) && isset($data['homebannershoverimage'][0]['tmp_name'])) {
            //     $data['homebannershoverimage'] ='/homebanners/'.$data['homebannershoverimage'][0]['name'];
            // } elseif (isset($data['homebannershoverimage'][0]['name']) && !isset($data['homebannershoverimage'][0]['tmp_name'])) {
            //     $data['homebannershoverimage'] =$data['homebannershoverimage'][0]['name'];
            // } else {
            //     $data['homebannershoverimage'] = null;
            // }
           
            $model->setData($data);


            $this->inlineTranslation->suspend();
            try {
                    //////////////////// email
                $model->save();
                $this->messageManager->addSuccess(__('Banner Saved successfully'));
                $this->dataPersistor->clear('Hakfist_homebanners');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['homebanners_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the banner.'));
            }

            $this->dataPersistor->set('Hakfist_homebanners', $data);
            return $resultRedirect->setPath('*/*/edit', ['homebanners_id' => $this->getRequest()->getParam('homebanners_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
