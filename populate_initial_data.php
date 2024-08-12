<?php

require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/DVD.php';
require_once __DIR__ . '/Furniture.php';
require_once __DIR__ . '/ProductDatabase.php';

// Function to populate initial data
function populateInitialData(ProductDatabase $db) {
    // Check if the table is empty using the public method
    if (!$db->isTableEmpty()) {
        echo "Table is already populated.";
        return; // Table is not empty
    }

    // Table is empty, insert initial data
    $initialProducts = [
        new DVD('SKU001', 'Sample DVD', 10.99, 700),
        new Book('SKU002', 'Sample Book', 15.99, 1.5),
        new Furniture('SKU003', 'Sample Furniture', 99.99, '100x200x50'),
        new DVD('SKU004', 'Another DVD', 12.99, 800),
        new Book('SKU005', 'Another Book', 18.99, 2.0),
        new Furniture('SKU006', 'Another Furniture', 120.00, '150x250x75')
    ];

    foreach ($initialProducts as $product) {
        try {
            $db->addProduct($product);
        } catch (Exception $e) {
            echo 'Error adding product: ' . $e->getMessage() . "\n";
        }
    }

    echo "Initial data populated successfully.";
}

// Create a ProductDatabase instance and populate initial data
$db = new ProductDatabase();
populateInitialData($db);

?>
