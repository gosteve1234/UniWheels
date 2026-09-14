<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require "connection.php";

// 1. Handle Add Product
if (isset($_POST['add_product'])) {
    $name     = $_POST['product_name'];
    $price    = $_POST['price'];
    $category = $_POST['category'];
    $stock    = $_POST['available_stock'];

    $stmt = $connection->prepare("INSERT INTO products (product_name, price, category, available_stock) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $name, $price, $category, $stock);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

// 2. Handle Update Product
if (isset($_POST['update_product'])) {
    $id       = $_POST['edit_id'];
    $name     = $_POST['product_name'];
    $price    = $_POST['price'];
    $category = $_POST['category'];
    $stock    = $_POST['available_stock'];

    $stmt = $connection->prepare("UPDATE products SET product_name = ?, price = ?, category = ?, available_stock = ? WHERE product_id = ?");
    $stmt->bind_param("sdsii", $name, $price, $category, $stock, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

// 3. Handle Delete Product
if (isset($_POST['delete_product'])) {
    $delete_id = $_POST['delete_id'];

    $stmt = $connection->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM products";
$result = mysqli_query($connection, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products &mdash; POS</title>
<link rel="stylesheet" href="style.css">
<style>
    /* --- Products page: extends the login page's palette (style.css) --- */

    body.products-body {
        display: block;
        align-items: initial;
        justify-content: initial;
        min-height: 100vh;
        padding: 0 0 60px;
    }

    .topbar {
        background-color: #8E1616;
        color: #F8EEDF;
        padding: 18px 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .topbar h1 {
        color: #F8EEDF;
        font-size: 1.3rem;
        margin: 0;
        letter-spacing: 0.3px;
    }

    .topbar .session {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 0.9rem;
    }

    .topbar a.logout {
        color: #F8EEDF;
        text-decoration: none;
        border: 1px solid #F8EEDF;
        padding: 6px 14px;
        border-radius: 4px;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .topbar a.logout:hover {
        background-color: #F8EEDF;
        color: #8E1616;
    }

    .page-wrap {
        max-width: 1000px;
        margin: 36px auto 0;
        padding: 0 24px;
    }

    .page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .page-head h2 {
        margin: 0;
        color: #8E1616;
        font-size: 1.4rem;
    }

    .btn {
        background-color: #8E1616;
        color: #F8EEDF;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.95rem;
    }

    .btn:hover {
        background-color: #000000;
    }

    .btn.secondary {
        background-color: transparent;
        color: #8E1616;
        border: 1px solid #8E1616;
    }

    .btn.secondary:hover {
        background-color: #8E1616;
        color: #F8EEDF;
    }

    .btn.danger {
        background-color: transparent;
        color: #8E1616;
        border: 1px solid #8E1616;
        padding: 6px 12px;
        font-size: 0.85rem;
    }

    .btn.danger:hover {
        background-color: #8E1616;
        color: #F8EEDF;
    }

    .btn.small {
        padding: 6px 12px;
        font-size: 0.85rem;
        margin-right: 6px;
    }

    /* Product table */
    table.products {
        width: 100%;
        border-collapse: collapse;
        background-color: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    }

    table.products th {
        background-color: #E8C999;
        color: #8E1616;
        text-align: left;
        padding: 12px 16px;
        font-size: 0.85rem;
        letter-spacing: 0.2px;
        border-bottom: 2px solid #8E1616;
    }

    table.products td {
        padding: 12px 16px;
        border-bottom: 1px solid #F0E4D0;
        font-size: 0.95rem;
    }

    table.products tr:last-child td {
        border-bottom: none;
    }

    table.products tr:hover td {
        background-color: #FBF5E9;
    }

    .empty-row td {
        text-align: center;
        color: #8a8a8a;
        padding: 30px 16px;
    }

    .actions-cell {
        white-space: nowrap;
    }

    .actions-cell form {
        display: inline;
        background: none;
        padding: 0;
        box-shadow: none;
    }

    /* Modals */
    dialog {
        border: none;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        width: 380px;
        max-width: 90vw;
    }

    dialog::backdrop {
        background: rgba(0, 0, 0, 0.4);
    }

    dialog form {
        background-color: #F8EEDF;
        padding: 24px 26px;
        border-radius: 8px;
        box-shadow: none;
    }

    dialog h2 {
        margin: 0 0 18px;
        color: #8E1616;
        font-size: 1.15rem;
    }

    dialog p {
        margin: 0 0 14px;
    }

    dialog label {
        display: block;
        margin-bottom: 4px;
        font-weight: bold;
        font-size: 0.85rem;
        color: #333;
    }

    dialog input[type="text"],
    dialog input[type="number"] {
        width: 100%;
        box-sizing: border-box;
        padding: 8px;
        border: 1px solid #8E1616;
        border-radius: 4px;
        font-size: 0.95rem;
    }

    dialog .dialog-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
</style>
</head>
<body class="products-body">

    <div class="topbar">
        <h1>POS &mdash; Products</h1>
        <div class="session">
            <span>Signed in as <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a class="logout" href="logout.php">Log out</a>
        </div>
    </div>

    <div class="page-wrap">
        <div class="page-head">
            <h2>Products List</h2>
            <button type="button" class="btn" onclick="document.getElementById('addModal').showModal()">+ Add Product</button>
        </div>

        <table class="products">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Action</th>
            </tr>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row["product_id"] ?></td>
                        <td><?= $row["product_name"] ?></td>
                        <td><?= $row["price"] ?></td>
                        <td><?= $row["category"] ?></td>
                        <td><?= $row["available_stock"] ?></td>
                        <td class="actions-cell">
                            <!-- EDIT BUTTON -->
                            <button type="button" class="btn secondary small" onclick="editProduct('<?= $row['product_id'] ?>', '<?= $row['product_name'] ?>', '<?= $row['price'] ?>', '<?= $row['category'] ?>', '<?= $row['available_stock'] ?>')">Edit</button>

                            <!-- DELETE BUTTON -->
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this?');">
                                <input type="hidden" name="delete_id" value="<?= $row['product_id'] ?>">
                                <button type="submit" name="delete_product" class="btn danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php else: ?>
                <tr class="empty-row">
                    <td colspan="6">No products yet &mdash; add your first one above.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- 1. Add Modal Dialog -->
    <dialog id="addModal">
        <form method="POST">
            <h2>Add New Product</h2>
            <p>
                <label>Product Name</label>
                <input type="text" name="product_name" required>
            </p>
            <p>
                <label>Price</label>
                <input type="number" step="0.01" name="price" required>
            </p>
            <p>
                <label>Category</label>
                <input type="text" name="category" required>
            </p>
            <p>
                <label>Stock</label>
                <input type="number" name="available_stock" required>
            </p>
            <div class="dialog-actions">
                <button type="button" class="btn secondary" onclick="document.getElementById('addModal').close()">Cancel</button>
                <button type="submit" name="add_product" class="btn">Save Product</button>
            </div>
        </form>
    </dialog>

    <!-- 2. Edit Modal Dialog -->
    <dialog id="editModal">
        <form method="POST">
            <input type="hidden" name="edit_id" id="edit_id">
            <h2>Edit Product</h2>
            <p>
                <label>Product Name</label>
                <input type="text" name="product_name" id="edit_name" required>
            </p>
            <p>
                <label>Price</label>
                <input type="number" step="0.01" name="price" id="edit_price" required>
            </p>
            <p>
                <label>Category</label>
                <input type="text" name="category" id="edit_category" required>
            </p>
            <p>
                <label>Stock</label>
                <input type="number" name="available_stock" id="edit_stock" required>
            </p>
            <div class="dialog-actions">
                <button type="button" class="btn secondary" onclick="document.getElementById('editModal').close()">Cancel</button>
                <button type="submit" name="update_product" class="btn">Save Changes</button>
            </div>
        </form>
    </dialog>

    <!-- JavaScript to pre-fill Edit Modal -->
    <script>
    function editProduct(id, name, price, category, stock) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('editModal').showModal();
    }
    </script>

</body>
</html>