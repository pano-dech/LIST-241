<?php
session_start();
include 'db_connect.php';

// MAC address retrieval matching register.php's method
function getMacAddress() {
    ob_start();
    system('ipconfig /all');
    $content = ob_get_clean();

    // Find active wireless adapter with IPv4 address
    preg_match_all(
        '/Wireless LAN adapter (.*?)(?=Wireless LAN adapter|Ethernet adapter|$)/s', 
        $content, 
        $wirelessAdapters
    );

    foreach ($wirelessAdapters[0] as $adapter) {
        // Check for active connection with IPv4
        if (strpos($adapter, 'IPv4 Address') !== false) {
            preg_match('/Physical Address[ .]+: ([\w-]+)/', $adapter, $macMatch);
            if (!empty($macMatch[1])) {
                $mac = strtoupper(str_replace('-', ':', $macMatch[1]));
                if (strlen($mac) === 17) {
                    return $mac; // Returns 7C:67:A2:37:C3:41
                }
            }
        }
    }

    return '00:00:00:00:00:00'; // Fallback
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = ["success" => false, "message" => ""];
    $action = $_POST["action"] ?? null;
    $id = $_POST["id"] ?? null;

    try {
        // Input validation
        if (!$id && !in_array($action, ["clear_cart", "purchase"])) {
            throw new Exception("Invalid request parameters");
        }

        // Common security setup
        $mac_address = getMACAddress();
        $mac_address = substr($mac_address, 0, 17); // Ensure length matches database

        if ($action === "increase" || $action === "decrease") {
            $id = intval($id);
            
            if ($action === "increase") {
                $stmt = $conn->prepare("UPDATE products SET count = count + 1 
                                      WHERE id = ? AND status = 'on cart'");
                $stmt->bind_param("i", $id);
            } else {
                // Decrease logic with existence check
                $stmt = $conn->prepare("SELECT count FROM products 
                                      WHERE id = ? AND status = 'on cart'");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $new_quantity = max(0, $row['count'] - 1);
                    if ($new_quantity === 0) {
                        $stmt = $conn->prepare("DELETE FROM products 
                                              WHERE id = ? AND status = 'on cart'");
                    } else {
                        $stmt = $conn->prepare("UPDATE products SET count = ? 
                                              WHERE id = ? AND status = 'on cart'");
                        $stmt->bind_param("ii", $new_quantity, $id);
                    }
                }
            }
            $stmt->execute();
            $stmt->close();
        } 
        elseif ($action === "clear_cart") {
            $stmt = $conn->prepare("DELETE FROM products WHERE status = 'on cart'");
            $stmt->execute();
            $stmt->close();
        } 
        elseif ($action === "purchase") {
    // Verify cart not empty first
    $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM products 
                           WHERE status = 'on cart'");
    $check->execute();
    if ($check->get_result()->fetch_assoc()['cnt'] == 0) {
        throw new Exception("Cannot purchase empty cart");
    }

    // Get user details if online and MAC matches
    $userQuery = $conn->prepare("SELECT name, email, phone_number 
                               FROM users 
                               WHERE mac_address = ? AND status = 'online'");
    $userQuery->bind_param("s", $mac_address);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    $userData = $userResult->fetch_assoc();

    // Extract user data or set to NULL if not found
    $username = $userData['name'] ?? null;
    $email = $userData['email'] ?? null;
    $phone = $userData['phone_number'] ?? null;

    // Update products with user details and purchase status
    $stmt = $conn->prepare("UPDATE products 
                           SET status = 'purchased', 
                               mac_address = ?, 
                               username = ?, 
                               email = ?, 
                               phone_number = ?
                           WHERE status = 'on cart'");
    $stmt->bind_param("ssss", $mac_address, $username, $email, $phone);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Purchase processing failed");
    }
    $stmt->close();
}

        // Get updated totals (using same security pattern)
        $stmt = $conn->prepare("SELECT 
            SUM(price * count) AS grand_total,
            SUM(count) AS cart_count 
            FROM products WHERE status = 'on cart'");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $response = [
            "success" => true,
            "grand_total" => number_format($result['grand_total'] ?? 0, 2),
            "cart_count" => $result['cart_count'] ?? 0
        ];

        // Add item-specific data if needed
        if (in_array($action, ["increase", "decrease"])) {
            $stmt = $conn->prepare("SELECT price, count FROM products 
                                   WHERE id = ? AND status = 'on cart'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            
            $response["new_quantity"] = $product['count'] ?? 0;
            $response["subtotal"] = number_format(($product['price'] ?? 0) * ($product['count'] ?? 0), 2);
        }

    } catch (Exception $e) {
        $response["message"] = $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>