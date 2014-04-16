<?php

/**
 * @var $installer Mage_Eav_Model_Entity_Setup
 */
$installer = $this;

$installer->startSetup();

/**
 * Add 'qvc_disable_reviews' attribute to the 'eav/attribute' table
 */
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'is_split_value', array(
    'group'             => 'Prices',
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Is split value',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '0',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => '',
    'is_configurable'   => false,
));

$installer->endSetup();