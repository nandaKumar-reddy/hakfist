<?php

/**
 * @author		Mobikasa
 * @category    Mobikasa
 * @package     Sashas_CustomerAttribute
 * @copyright   Copyright (c) 2015 Mobikasa IT Support Inc. (http://www.extensions.sashas.org)
 */

namespace Hakfist\CustomerAttribute\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

	/**
	 * @var CustomerSetupFactory
	 */
	protected $customerSetupFactory;

	/**
	 * @var AttributeSetFactory
	 */
	private $attributeSetFactory;

	/**
	 * @param CustomerSetupFactory $customerSetupFactory
	 * @param AttributeSetFactory $attributeSetFactory
	 */
	public function __construct(
		CustomerSetupFactory $customerSetupFactory,
		AttributeSetFactory $attributeSetFactory
	) {
		$this->customerSetupFactory = $customerSetupFactory;
		$this->attributeSetFactory = $attributeSetFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{

		/** @var CustomerSetup $customerSetup */
		$customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

		$customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
		$attributeSetId = $customerEntity->getDefaultAttributeSetId();

		/** @var $attributeSet AttributeSet */
		$attributeSet = $this->attributeSetFactory->create();
		$attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

		$customerSetup->addAttribute(Customer::ENTITY, 'odoo_status', [
			'type' => 'varchar',
			'label' => 'Odoo Status',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1000,
			'position' => 1000,
			'system' => 0,
			'is_used_in_grid' => true,
			'is_visible_in_grid' => true,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'odoo_status')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer'],
				'is_used_in_grid' => true,
				'is_visible_in_grid' => true,
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'created', [
			'type' => 'varchar',
			'label' => 'Account From',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1001,
			'position' => 1001,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'created')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'created_in_odoo', [
			'type' => 'varchar',
			'label' => 'Created In Odoo',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => false,
			'sort_order' => 1002,
			'position' => 1002,
			'system' => 0,
			'readonly' => true, // custom parameter

		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'created_in_odoo')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer'],

			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'business_name', [
			'type' => 'varchar',
			'label' => 'Business Name',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => false,
			'sort_order' => 1003,
			'position' => 1003,
			'system' => 0,

		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'business_name')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer'],

			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'company_number', [
			'type' => 'varchar',
			'label' => 'Company Contact Number ',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1004,
			'position' => 1004,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'company_number')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'phone_number', [
			'type' => 'varchar',
			'label' => 'Company Alternative Phone Number ',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1005,
			'position' => 1005,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'phone_number')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(
			Customer::ENTITY,
			'tax_certificate',
			[
				'type' => 'text',
				'label' => 'Trade License Document',
				'input' => 'file',
				'global' => true,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'sort_order' => 1006,
				'position' => 1006,
				'system' => 0,
			]
		);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tax_certificate')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'trn_number', [
			'type' => 'varchar',
			'label' => 'TRN Number',
			'input' => 'text',
			'visible' => true,
			'required' => false,
			'user_defined' => false,
			'sort_order' => 1007,
			'position' => 1007,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'trn_number')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['customer_account_create'], // Exclude admin forms
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'poc_name', [
			'type' => 'varchar',
			'label' => 'POC`s Name',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1008,
			'position' => 1008,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'poc_name')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();


		$customerSetup->addAttribute(Customer::ENTITY, 'reg_number', [
			'type' => 'varchar',
			'label' => 'Registration Number',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1009,
			'position' => 1009,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'reg_number')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'address_line', [
			'type' => 'varchar',
			'label' => 'Address Line1',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1010,
			'position' => 1010,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'address_line')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'address_line2', [
			'type' => 'varchar',
			'label' => 'Address Line 2',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1011,
			'position' => 1011,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'address_line2')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'country_billing', [
			'type' => 'varchar',
			'label' => 'Country',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1012,
			'position' => 1012,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'country_billing')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'city', [
			'type' => 'varchar',
			'label' => 'City',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1013,
			'position' => 1013,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'city')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'poc_email', [
			'type' => 'varchar',
			'label' => 'POC Email',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1014,
			'position' => 1014,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'poc_email')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'poc_number', [
			'type' => 'varchar',
			'label' => 'Point Of Contact Name',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1015,
			'position' => 1015,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'poc_number')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'poc_designation', [
			'type' => 'varchar',
			'label' => 'POC Designation',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1016,
			'position' => 1016,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'poc_designation')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'business_turnover', [
			'type' => 'varchar',
			'label' => 'Approximate Business Turnover Per Year (AED)',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1017,
			'position' => 1017,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'business_turnover')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'company_email', [
			'type' => 'varchar',
			'label' => 'Company Email',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1018,
			'position' => 1018,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'company_email')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'company_website', [
			'type' => 'varchar',
			'label' => 'Company Website',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1019,
			'position' => 1019,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'company_website')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'team_name', [
			'type' => 'varchar',
			'label' => 'Name',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1020,
			'position' => 1020,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'team_name')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'team_number', [
			'type' => 'varchar',
			'label' => 'Mobile Number',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1021,
			'position' => 1021,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'team_number')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'team_email', [
			'type' => 'varchar',
			'label' => 'Email',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1022,
			'position' => 1022,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'team_email')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'team_designation', [
			'type' => 'varchar',
			'label' => 'Designation',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1023,
			'position' => 1023,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'team_designation')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'category', [
			'type' => 'varchar',
			'label' => 'Interested Category Products',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1024,
			'position' => 1024,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'category')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'assigned_manager', [
			'type' => 'varchar',
			'label' => 'Assigned Manager ID',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1025,
			'position' => 1025,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'assigned_manager')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();

		$customerSetup->addAttribute(Customer::ENTITY, 'is_deleted', [
			'type' => 'varchar',
			'label' => 'Active/Inactive',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'sort_order' => 1025,
			'position' => 1025,
			'system' => 0,
		]);

		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_deleted')
			->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
			]);

		$attribute->save();
	}
}
