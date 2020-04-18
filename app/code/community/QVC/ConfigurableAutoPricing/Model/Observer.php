<?php

class QVC_ConfigurableAutoPricing_Model_Observer
{
    /**
     * @var string
     */
    const CONFIG_XPATH_ENABLE = 'qvc_configurableautopricing/general/enable';

    /**
     * Var set in before save to know after commit if the object was new or not
     *
     * @var bool
     */
    protected $_wasObjectNew;

    /**
     * Before save update price deltas just for modifications
     *
     * @param $observer
     */
    public function updatePriceDeltasBeforeSave($observer)
    {
        if (Mage::getStoreConfigFlag(self::CONFIG_XPATH_ENABLE)) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $observer->getProduct();

            if ($product->getIsSplitValue()) {
                $this->_wasObjectNew = $product->isObjectNew();
                if (!$this->_wasObjectNew) {
                    $this->_updatePriceDeltas($product);
                }
            }
        }
    }

    /**
     * After commit, if product has price deltas, re-save product.
     * The price deltas (already calculated in singleton helper) will be applied in before save.
     *
     * @param $observer
     */
    public function updatePriceDeltasAfterSave($observer)
    {
        if (Mage::getStoreConfigFlag(self::CONFIG_XPATH_ENABLE)) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $observer->getProduct();

            if ($this->_wasObjectNew && $product->getIsSplitValue()) {
                $this->_wasObjectNew = false;

                $product->setDataChanges(true);
                $product->getResource()
                    ->save($product);
            }
        }
    }

    /**
     * Update the product configurable attributes pricing with the price deltas of his children, if any
     * Return true if price modifications were applied
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _updatePriceDeltas(Mage_Catalog_Model_Product $product)
    {
        /** @var QVC_ConfigurableAutoPricing_Helper_Data $helper */
        $helper = Mage::helper('qvc_configurableautopricing');

        return $helper->setConfigurableAttributesPricing($product);
    }
} 
