<?php

require_once 'Product.php';

class DVD extends Product {
    private $size;

    public function __construct($sku, $name, $price, $size) {
        parent::__construct($sku, $name, $price);
        $this->size = $size;
    }

    public function getAttributes() {
        return ['Size' => $this->size];
    }

    public function setAttributes($attributes) {
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        }
        
        if (isset($attributes['Size'])) {
            $this->size = $attributes['Size'];
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
