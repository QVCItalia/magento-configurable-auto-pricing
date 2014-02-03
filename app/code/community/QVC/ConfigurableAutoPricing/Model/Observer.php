<?php

class QVC_ConfigurableAutoPricing_Model_Observer
{
    /**
     * Update the product configurable attributes pricing with the price deltas of his children, if any
     *
     * @param $observer
     */
    public function updatePriceDeltas($observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getProduct();

        if (!$product->isConfigurable()) {
            return;
        }

        /** @var QVC_ConfigurableAutoPricing_Helper_Data $helper */
        $helper = Mage::helper('qvc_configurableautopricing');

        $priceDeltas = $helper->getPriceDeltas($product);

        if (!empty($priceDeltas)) {
            $helper->setConfigurableAttributesPricing($product, $priceDeltas);
        }
    }
} 