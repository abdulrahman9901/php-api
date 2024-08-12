<?php

require_once 'Product.php';

class Book extends Product {
    private $weight;

    public function __construct($sku, $name, $price, $weight) {
        parent::__construct($sku, $name, $price);
        $this->weight = $weight;
    }

    public function getAttributes() {
        return ['Weight' => $this->weight];
    }

   public function setAttributes($attributes) {
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        }

        if (isset($attributes['Weight'])) {
            $this->weight = $attributes['Weight'];
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
