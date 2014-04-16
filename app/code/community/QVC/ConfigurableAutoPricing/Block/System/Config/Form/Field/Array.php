<?php

class QVC_ConfigurableAutoPricing_Block_System_Config_Form_Field_Array extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('field', array(
            'label' => Mage::helper('adminhtml')->__('Field'),
            'size'  => 30,
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add new field');

        parent::__construct();
        $this->setTemplate('system/config/form/field/array.phtml');
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        $rendered = '<input style="width:'.$column['size'].'em" name="'.$inputName.'" value="#{'.$columnName.'}">';


        return $rendered;
    }
}
