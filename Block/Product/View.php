<?php

namespace Btm\Fix10150\Block\Product;

class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
     public function getJsonConfig()
     {
         /* @var $product \Magento\Catalog\Model\Product */
         $product = $this->getProduct();
         if (!$this->hasOptions()) {
             $config = [
                 'productId' => $product->getId(),
                 'priceFormat' => $this->_localeFormat->getPriceFormat()
                 ];
             return $this->_jsonEncoder->encode($config);
         }
         $tierPrices = [];
         $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
         foreach ($tierPricesList as $tierPrice) {
             $tierPrices[] = $tierPrice['price']->getValue();
         }
         $config = [
             'productId' => $product->getId(),
             'priceFormat' => $this->_localeFormat->getPriceFormat(),
             'prices' => [
                 'oldPrice' => [
                     'amount' => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),
                     'adjustments' => []
                 ],
                 'basePrice' => [
                     'amount' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount(),
                     'adjustments' => []
                 ],
                 'finalPrice' => [
                     'amount' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                     'adjustments' => []
                 ]
             ],
             'idSuffix' => '_clone',
             'tierPrices' => $tierPrices
         ];
         $responseObject = new \Magento\Framework\DataObject();
         $this->_eventManager->dispatch('catalog_product_view_config', ['response_object' => $responseObject]);
         if (is_array($responseObject->getAdditionalOptions())) {
             foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                 $config[$option] = $value;
             }
         }
         return $this->_jsonEncoder->encode($config);
     }
}