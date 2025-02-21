<?php
namespace Hakfist\ProductMasterApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;
use Hakfist\ProductMasterApi\Model\ProductmasterFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;



class Productdetails extends AbstractHelper
{

	const ODOO_PRODUCTS_LAST_SYNC_DATE_TIME = 'apiconfig/general/SyncTime';
	const ODOO_PRODUCTS_LAST_IMPORT_DATE_TIME = 'apiconfig/general/ImportTime';



	private $connection;
	protected $ProductmasterFactory;
	protected $productCollectionFactory;
	protected $timezoneInterface;
	protected $configWriter;
	protected $eavConfig;
	protected $_eavSetupFactory;
	protected $_storeManager;
	protected $_attributeFactory;
	/**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $attributeValues;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\TableFactory
     */
    protected $tableFactory;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $attributeOptionManagement;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory
     */
    protected $optionLabelFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement
     * @param \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
     */

	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
		\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
		\Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
		ProductmasterFactory $ProductmasterFactory,
		ResourceConnection $resourceConnection,
		TimezoneInterface $timezoneInterface,
		array $data = array()
    )
    {
        $this->connection = $resourceConnection->getConnection();
		$this->ProductmasterFactory = $ProductmasterFactory;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->timezoneInterface = $timezoneInterface;
		$this->scopeConfig = $scopeConfig;
		$this->configWriter = $configWriter;
		$this->eavConfig = $eavConfig;
		$this->_eavSetupFactory = $eavSetupFactory;
		$this->_storeManager = $storeManager;
		$this->_attributeFactory = $attributeFactory;
		$this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
		parent::__construct($context);
    }
	   /**
     * Get attribute by code.
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getAttribute($attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
    }

    /**
     * Find or create a matching attribute option
     *
     * @param string $attributeCode Attribute the option should exist in
     * @param string $label Label to find or add
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrGetId($attributeCode, $label)
    {
        if (strlen($label) < 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Label for %1 must not be empty.', $attributeCode)
            );
        }

        // Does it already exist?
        $optionId = $this->getOptionId($attributeCode, $label);
		
        if (!$optionId) {
            // If no, add it.

            /** @var \Magento\Eav\Model\Entity\Attribute\OptionLabel $optionLabel */
            $optionLabel = $this->optionLabelFactory->create();
            // $optionLabel->setStoreId(0);
            $optionLabel->setLabel($label);
            $option = $this->optionFactory->create();
            $option->setLabel($optionLabel->getLabel());
			// print_r($optionLabel);
			// die();
            $option->setStoreLabels([$optionLabel]);
            $option->setSortOrder(0);
            $option->setIsDefault(false);

            $this->attributeOptionManagement->add(
                \Magento\Catalog\Model\Product::ENTITY,
                $this->getAttribute($attributeCode)->getAttributeId(),
                $option
            );

            // Get the inserted ID. Should be returned from the installer, but it isn't.
            $optionId = $this->getOptionId($attributeCode, $label, true);
        }

        return $optionId;
    }

    /**
     * Find the ID of an option matching $label, if any.
     *
     * @param string $attributeCode Attribute code
     * @param string $label Label to find
     * @param bool $force If true, will fetch the options even if they're already cached.
     * @return int|false
     */
    public function getOptionId($attributeCode, $label, $force = false)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->getAttribute($attributeCode);

        // Build option array if necessary
        if ($force === true || !isset($this->attributeValues[ $attribute->getAttributeId() ])) {
            $this->attributeValues[ $attribute->getAttributeId() ] = [];

            // We have to generate a new sourceModel instance each time through to prevent it from
            // referencing its _options cache. No other way to get it to pick up newly-added values.

            /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table $sourceModel */
            $sourceModel = $this->tableFactory->create();
            $sourceModel->setAttribute($attribute);

            foreach ($sourceModel->getAllOptions() as $option) {
                $this->attributeValues[ $attribute->getAttributeId() ][ $option['label'] ] = $option['value'];
            }
        }

        // Return option ID if exists
        if (isset($this->attributeValues[ $attribute->getAttributeId() ][ $label ])) {
            return $this->attributeValues[ $attribute->getAttributeId() ][ $label ];
        }

        // Return false if does not exist
        return false;
    }
	public function InsertIntoTable($ProductData)
    {
		$totalProducts = count($ProductData);
		$result = [];
		$configbind=[];
		$checkrows = [];
		$tableName = "temp_products_copy";
        if($totalProducts > 0) {
			try{
				$this->connection->truncateTable($tableName);
				foreach($ProductData as $products) {
					if($products['quantity'] == 'Not Available' || $products['quantity'] == ''){
						$products['quantity'] = 100;
					}
					if($products['sku'] != 'Not Available' && $products['sku'] != ""){

						// $sql = "SELECT * FROM " . $tableName . " WHERE request = " . $requestQuery . " ORDER BY date_time DESC";
						$sql = "SELECT sku FROM " . $tableName . " WHERE sku = '" . $products['sku'].'-CONFIG' ."'";
						$checkrows = $this->connection->fetchAll($sql);
						if (count($checkrows) > 0) {
							$result = ['error' => false, 'message' => 'Table has already this item', 'data' => $products['sku'] ];
						}else{
							$SelectedCategory = "";
							$DefaultCategory = "Default Category/Products";
		
							$AssignedCategory = $products['category'];
							foreach($AssignedCategory[0] as $key => $parentcategory){
								$SelectedCategory .= 'Default Category/'.$key.',';
								foreach($parentcategory as $childcategory){
									$SelectedCategory .= 'Default Category/'.$key.'/'.$childcategory.',';
								}
							}
							
							if($SelectedCategory == ""){
								$SelectedCategory = $DefaultCategory;
							}else{
								// $SelectedCategory =str_replace('&','',$SelectedCategory);
								$SelectedCategory = substr($SelectedCategory, 0, -1);
							}
							$childproducts = $products['variants'];
							$configurable_Options = "";
							foreach($childproducts as $childproduct){
								if($childproduct['sku'] !="" && $childproduct['sku'] !="Not Available"){
									$sql = "SELECT sku FROM " . $tableName . " WHERE sku = '" . $childproduct['sku'] ."'";
									$childrows = $this->connection->fetchAll($sql);
									if (count($childrows) > 0) {
										$result = ['error' => false, 'message' => 'Table has already this item', 'data' => $childproduct['sku'] ];
									}else {
										if(!isset($childproduct['Size'])){
											$childproduct['Size'] = "";
										}
										if(!isset($childproduct['Colors'])){
											$childproduct['Colors'] = "";
										}
										if($childproduct['sku'] != '' && $childproduct['Colors'] != '' && $childproduct['Size'] != ''){
											$configurable_Options .= 'sku='.$childproduct['sku'].',color='.$childproduct['Colors'].',size='.$childproduct['Size'].'|';
											
										}else if($childproduct['sku'] != '' && $childproduct['Colors'] != '' && $childproduct['Size'] == ''){
											$configurable_Options .= 'sku='.$childproduct['sku'].',color='.$childproduct['Colors'].'|';
										}
										if($childproduct['quantity'] == 'Not Available' || $childproduct['quantity'] == ''){
											$childproduct['quantity'] = 100; // default quantity
										}
										$bind = [
											'sku' => $childproduct['sku'],
											'name' => $childproduct['name'],
											'category' => $SelectedCategory,
											'producttype' => "simple",
											'uom' => $childproduct['uom'],
											'price' => $childproduct['sale_price'],
											'taxes' => $childproduct['taxes'][0],
											'description' => $childproduct['description'],
											'size' => $childproduct['Size'],
											'color' => $childproduct['Colors'],
											'quantity' => $childproduct['quantity'],
											'visibility' => 'Not Visible Individually',
										];
										$this->connection->insert('temp_products_copy', $bind);
									}
								}
								
							}
							$configurable_Options = substr($configurable_Options, 0, -1);
							$configbind = [
								'sku' => $products['sku'].'-CONFIG',
								'name' => $products['name'],
								'category' => $SelectedCategory,
								'producttype' => "configurable",
								'uom' => $products['uom'],
								'price' => $products['sale_price'],
								'taxes' => $products['taxes'][0],
								'description' => $products['description'],
								'quantity' => $products['quantity'],
								'visibility' => 'Catalog, Search',
								'configs' => $configurable_Options
							];
							$this->connection->insert('temp_products_copy', $configbind);

						}
					}
				}
				$result = ['error' => false, 'message' => 'Table data inserted successfully', 'data' => $totalProducts ];

			} catch (\Exception $e) {
				$result = ['error' => true, 'message' => $e->getMessage(), 'data' => ''];
			} finally {
				return $result;
			}
			
        }

       
        
    }
    public function getProductsfromAPI()
	{
		$result = [];

		try {

			// /Need to access URL & Accesstoken from backend
			$url = "https://dev.hakfist.com/api/inventory"; // ODOO API ENDPOINT
			$accessToken = "c34f4c3a535a6d65b990ae645155df9338cbe11f"; // ODOO ACCESS TOKEN
			$header = ['Content-Type:application/json', 'Authorization:Bearer ' . $accessToken];
			$body_arr = [
			];
			$body = json_encode($body_arr);
			$mage = $this->curlExec($url, 'GET', $header, $body_arr);

			// return $mage;
			$responseCode = $mage['response_code'];
			if ($responseCode == 200) {
				$result_body = json_decode($mage['result'], true);
				$result = ['error' => false, 'message' => 'Data retreived successfully', 'data' => $result_body];
			} else {
				$result = ['error' => true, 'message' => 'An unexpected error occured', 'data' => $responseCode];
			}

		} catch (\Exception $e) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			$result = ['error' => true, 'message' => $e->getMessage(), 'data' => ''];
		} finally {
			return $result;
		}
	}
	public function SyncProductswithOdoo(){

		$allproducts = $this->productCollectionFactory->create();
		$allproducts->addAttributeToSelect(['name','sku']);
		$allproducts->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$allproducts->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		$allproducts->addAttributeToFilter('type_id', array('eq' => 'simple'));
		return $allproducts;
	}
	public function LastSyncDetails(){
		$formatDate = $this->timezoneInterface->formatDate();
        $dateTime = $this->timezoneInterface->date()->format('Y-m-d H:i:s');
		$this->setConfigValue(self::ODOO_PRODUCTS_LAST_SYNC_DATE_TIME, $dateTime);
		return $dateTime;
	}
	public function LastImportDetails(){
		$formatDate = $this->timezoneInterface->formatDate();
        $dateTime = $this->timezoneInterface->date()->format('Y-m-d H:i:s');
		$this->setConfigValue(self::ODOO_PRODUCTS_LAST_IMPORT_DATE_TIME, $dateTime);
		return $dateTime;
	}
	public function setConfigValue($field, $value)
	{
		$this->configWriter->save($field,  $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
	}
	public function ValidateAPIResponce($itemInfo){
        $data = [];
        foreach($itemInfo as $itemkey =>  $itemvalue){
            $dataItems = [];
            if (empty($itemvalue)){
                $dataItems['message'] = "Empty Data";
                $dataItems['status'] = true;
                $dataItems['attribute'] = $itemkey;
                array_push($data, $dataItems);
            } 
        }
        return $data;
    }
	public function getattributedetails($code)
    {
        $attributeCode = "color";
        $attribute = $this->eavConfig->getAttribute('catalog_product', $code);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option['label'];
				// print_r($option['value']);
				// die();
            }
        }
		return $arr;
      }
	public function setattributeOptions($attribute_arr,$attributeCode)
    {
			$attributeInfo=$this->_attributeFactory->getCollection()
			->addFieldToFilter('attribute_code',['eq'=>$attributeCode])
			->getFirstItem();
			$attribute_id = $attributeInfo->getAttributeId();
			$option=array();
			$option['attribute_id'] = $attributeInfo->getAttributeId();
					foreach($attribute_arr as $key=>$value){
						$option['value'][$value][0]=$value;
						}
			$eavSetup = $this->_eavSetupFactory->create();
			$eavSetup->addAttributeOption($option);
			// $eavSetup->save();
		
	}
	private function curlExec($url, $method, $header, $body)
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return array('response_code' => $httpCode, 'result' => $result);
	}
}