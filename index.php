```php
<?php
// Suppress error display for users, log errors instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize inventory if not set
$inventory = isset($_SESSION['inventory']) ? $_SESSION['inventory'] : [
    ['id' => 1, 'name' => 'Laptop', 'quantity' => 25, 'price' => 999.99, 'category' => 'Electronics'],
    ['id' => 2, 'name' => 'Smartphone', 'quantity' => 50, 'price' => 499.99, 'category' => 'Electronics'],
    ['id' => 3, 'name' => 'Headphones', 'quantity' => 100, 'price' => 79.99, 'category' => 'Accessories'],
];

// Handle form submissions with validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_item'])) {
            // Validate input
            if (empty($_POST['name']) || !is_numeric($_POST['quantity']) || !is_numeric($_POST['price']) || empty($_POST['category'])) {
                throw new Exception('Invalid input data');
            }
            $newItem = [
                'id' => count($inventory) + 1,
                'name' => htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'),
                'quantity' => max(0, (int)$_POST['quantity']),
                'price' => max(0, (float)$_POST['price']),
                'category' => htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8')
            ];
            $inventory[] = $newItem;
            $_SESSION['inventory'] = $inventory;
        } elseif (isset($_POST['update_quantity'])) {
            // Validate update
            if (!is_numeric($_POST['id']) || !is_numeric($_POST['quantity'])) {
                throw new Exception('Invalid update data');
            }
            $id = (int)$_POST['id'];
            $quantity = max(0, (int)$_POST['quantity']);
            foreach ($inventory as &$item) {
                if ($item['id'] == $id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
            $_SESSION['inventory'] = $inventory;
        }
    } catch (Exception $e) {
        error_log('Error in form processing: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Inventory Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            animation: fadeInDown 1s ease-out;
        }

        .header h1 {
            font-size: 2.5em;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .card h3 {
            margin-bottom: 10px;
            color: #00ff88;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .form-container input,
        .form-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 1em;
        }

        .form-container input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-container button {
            background: #00ff88;
            color: #1e3c72;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .form-container button:hover {
            background: #00cc70;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        th {
            background: #00ff88;
            color: #1e3c72;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }

        .low-stock {
            color: #ff4444;
            font-weight: bold;
        }

        .error-message {
            color: #ff4444;
            text-align: center;
            margin: 20px 0;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['inventory'])): ?>
        <div class="error-message">
            Warning: Session data not initialized properly. Please ensure PHP sessions are enabled.
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="header">
            <h1>Smart Inventory Tracker</h1>
        </div>

        <div class="dashboard">
            <div class="card">
                <h3>Total Items</h3>
                <p><?php echo count($inventory); ?></p>
            </div>
            <div class="card">
                <h3>Total Value</h3>
                <p>$<?php
                    $totalValue = 0;
                    foreach ($inventory as $item) {
                        $totalValue += ($item['quantity'] * $item['price']);
                    }
                    echo number_format($totalValue, 2);
                ?></p>
            </div>
            <div class="card">
                <h3>Low Stock</h3>
                <p><?php
                    $lowStock = 0;
                    foreach ($inventory as $item) {
                        if ($item['quantity'] < 10) $lowStock++;
                    }
                    echo $lowStock;
                ?> items</p>
            </div>
        </div>

        <div class="form-container">
            <h2>Add New Item</h2>
            <form method="POST" onsubmit="return validateForm()">
                <input type="text" name="name" placeholder="Item Name" required>
                <input type="number" name="quantity" placeholder="Quantity" min="0" required>
                <input type="number" step="0.01" name="price" placeholder="Price" min="0" required>
                <select name="category" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Other">Other</option>
                </select>
                <button type="submit" name="add_item">Add Item</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $item): ?>
                    <tr <?php if ($item['quantity'] < 10) echo 'class="low-stock"'; ?>>
                        <td><?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return validateUpdateForm(this)">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?>" style="width:80px;" min="0">
                                <button type="submit" name="update_quantity">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Client-side form validation
        function validateForm() {
            const quantity = document.querySelector('input[name="quantity"]').value;
            const price = document.querySelector('input[name="price"]').value;
            if (quantity < 0 || price < 0) {
                alert('Quantity and price cannot be negative!');
                return false;
            }
            return true;
        }

        function validateUpdateForm(form) {
            const quantity = form.querySelector('input[name="quantity"]').value;
            if (quantity < 0) {
                alert('Quantity cannot be negative!');
                return false;
            }
            return true;
        }

        // Add animation to table rows
        document.querySelectorAll('tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.01)';
                this.style.transition = 'transform 0.2s ease';
            });
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
```