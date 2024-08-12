<?php

require_once 'Product.php';

class Furniture extends Product {
    private $dimensions;

    public function __construct($sku, $name, $price, $dimensions) {
        parent::__construct($sku, $name, $price);
        $this->dimensions = $dimensions;
    }

    public function getAttributes() {
        return ['Dimensions' => $this->dimensions];
    }

    public function setAttributes($attributes) {

         if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        }
        
        if (isset($attributes['Dimensions'])) {
            $this->dimensions = $attributes['Dimensions'];
        }
    }

    public function toArray() {
        return [
            'sku' => $this->sku,
            'name' => $this->name,
            'price' => $this->price,
            'attributes' => $this->getAttributes()
        ];
    }
}

?>
