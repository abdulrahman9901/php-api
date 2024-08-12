<?php

require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/DVD.php';
require_once __DIR__ . '/Furniture.php';
require_once __DIR__ . '/ProductDatabase.php';

header('Content-Type: application/json');

/// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Allow specific methods
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit;
}
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the path info from the query string
$pathInfo = isset($_GET['path_info']) ? $_GET['path_info'] : '';

// Initialize the database
$db = new ProductDatabase();

// Process the request method
$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($pathInfo, '/'));

try {
    switch ($requestMethod) {
        case 'GET':
            if ($path[0] === 'products') {
                if (isset($path[1])) {
                    $sku = $path[1];
                    $product = $db->getProduct($sku);
                    if ($product) {
                        echo json_encode($product->toArray());
                    } else {
                        http_response_code(404);
                        echo json_encode(['message' => 'Product not found']);
                    }
                } else {
                    $products = $db->getAllProducts();
                    $productArray = array_map(fn($product) => $product->toArray(), $products);
                    echo json_encode($productArray);
                }
            }
            break;

        case 'POST':
            if ($path[0] === 'products') {
                $data = json_decode(file_get_contents('php://input'), true);
                if ($data && isset($data['sku']) && isset($data['name']) && isset($data['price']) && isset($data['attributes'])) {
                    $sku = $data['sku'];
                    $name = $data['name'];
                    $price = $data['price'];
                    $attributes = $data['attributes'];

                    $product = createProductFromAttributes($sku, $name, $price, $attributes);
                    if ($product) {
                        $db->addProduct($product);
                        echo json_encode(['message' => 'Product added successfully']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['message' => 'Invalid product attributes']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Invalid input']);
                }
            }
            break;

        case 'PUT':
            if ($path[0] === 'products' && isset($path[1])) {
                $sku = $path[1];
                $data = json_decode(file_get_contents('php://input'), true);
                if ($data && isset($data['name']) && isset($data['price']) && isset($data['attributes'])) {
                    $name = $data['name'];
                    $price = $data['price'];
                    $attributes = $data['attributes'];

                    $product = createProductFromAttributes($sku, $name, $price, $attributes);
                    if ($product) {
                        $db->updateProduct($product);
                        echo json_encode(['message' => 'Product updated successfully']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['message' => 'Invalid product attributes']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Invalid input']);
                }
            }
            break;

        case 'DELETE':
            if ($path[0] === 'products') {
                $data = json_decode(file_get_contents('php://input'), true);

                if (isset($data['skus']) && is_array($data['skus'])) {
                    foreach ($data['skus'] as $sku) {
                        $db->deleteProduct(trim($sku));
                    }
                    echo json_encode(['message' => 'Products deleted successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Invalid input: ' . json_encode($data)]);
                }
            }

            break;

        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}

/**
 * Creates a product based on the attributes.
 *
 * @param string $sku
 * @param string $name
 * @param float $price
 * @param array $attributes
 * @return Product|null
 */
function createProductFromAttributes($sku, $name, $price, $attributes) {
    if (isset($attributes['Size'])) {
        return new DVD($sku, $name, $price, $attributes['Size']);
    } elseif (isset($attributes['Weight'])) {
        return new Book($sku, $name, $price, $attributes['Weight']);
    } elseif (isset($attributes['Dimensions'])) {
        return new Furniture($sku, $name, $price, $attributes['Dimensions']);
    }
    return null;
}
?>
