<?php

class ProductDatabase {
    private $pdo;
    private $table = 'products';

    public function __construct() {
        $config = include __DIR__ . '/databaseConfig.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
        $this->createTable(); 
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS $this->table (
            SKU VARCHAR(255) NOT NULL UNIQUE,
            Name VARCHAR(255) NOT NULL,
            Price DECIMAL(10, 2) NOT NULL,
            Attributes TEXT,
            PRIMARY KEY (SKU)
        )";

        $this->pdo->exec($sql);
    }

    public function isTableEmpty() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM $this->table");
        return $stmt->fetchColumn() == 0;
    }

    public function addProduct(Product $product) {

        if ($this->getProduct($product->getSku())) {
            throw new Exception("Product with SKU = " . $product->getSku() . " already exists.");
        }

        $data = $product->toArray();

        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $data['attributes'] = json_encode($data['attributes']);
            echo $data['attributes'];
        }

        $sql = "INSERT INTO $this->table (SKU, Name, Price, Attributes)
                VALUES (:sku, :name, :price, :attributes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    public function updateProduct(Product $product) {
        if (!$this->getProduct($product->getSku())) {
            throw new Exception("Product with SKU " . $product->getSku() . " does not exist.");
        }

        $sql = "UPDATE $this->table SET Name = :name, Price = :price, Attributes = :attributes WHERE SKU = :sku";
        $stmt = $this->pdo->prepare($sql);
        $data = $product->toArray();
        $stmt->execute($data);
    }

    public function getProduct($sku) {
        $sql = "SELECT * FROM $this->table WHERE SKU = :sku";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['sku' => $sku]);
        $row = $stmt->fetch();

        if ($row) {
            return $this->createProductFromRow($row);
        }
        return null;
    }

    public function getAllProducts() {
        $sql = "SELECT * FROM $this->table";
        $stmt = $this->pdo->query($sql);
        $products = [];

        while ($row = $stmt->fetch()) {
            $products[] = $this->createProductFromRow($row);
        }

        return $products;
    }

    public function deleteProduct($sku) {
        $sql = "DELETE FROM $this->table WHERE SKU = :sku";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['sku' => $sku]);
    }


    private function createProductFromRow($row) {
    // Decode the attributes from the JSON string
    $attributes = json_decode($row['Attributes'], true);

    // Initialize a variable for the product object
    $product = null;

    // Determine the product type based on the attributes and create the corresponding product object
    if (isset($attributes['Size'])) {
        $product = new DVD($row['SKU'], $row['Name'], $row['Price'], $attributes['Size']);
    } elseif (isset($attributes['Weight'])) {
        $product = new Book($row['SKU'], $row['Name'], $row['Price'], $attributes['Weight']);
    } elseif (isset($attributes['Dimensions'])) {
        $product = new Furniture($row['SKU'], $row['Name'], $row['Price'], $attributes['Dimensions']);
    } else {
        throw new Exception("Unknown product type");
    }

    // Convert the attributes back to JSON format for output
    $product->setAttributes(json_encode($attributes));

    return $product;
    }
}
?>
