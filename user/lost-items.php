<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$q = isset($_GET['q']) ? trim($_GET['q']) : "";
$category = isset($_GET['category']) ? trim($_GET['category']) : "";
$location = isset($_GET['location']) ? trim($_GET['location']) : "";

$where = " WHERE items.status='lost' ";

if ($q !== "") {
    $qEsc = mysqli_real_escape_string($conn, $q);
    $where .= " AND (items.title LIKE '%$qEsc%' OR items.description LIKE '%$qEsc%') ";
}
if ($category !== "") {
    $catEsc = mysqli_real_escape_string($conn, $category);
    $where .= " AND items.category LIKE '%$catEsc%' ";
}
if ($location !== "") {
    $locEsc = mysqli_real_escape_string($conn, $location);
    $where .= " AND items.location LIKE '%$locEsc%' ";
}

$sql = "
    SELECT items.*, users.name AS poster
    FROM items
    JOIN users ON items.user_id = users.id
    $where
    ORDER BY items.created_at DESC
";

$query = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lost Items | CampusFind</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="page-wrapper">
<div class="header">
    <h1>Lost Items</h1>
    <a href="dashboard.php" class="btn">Dashboard</a>
</div>

<div class="container">
<div class="card">

<form method="GET" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:15px;">
    <div>
        <label>Search</label>
        <input type="text" name="q" placeholder="Title or description" value="<?php echo htmlspecialchars($q); ?>">
    </div>
    <div>
        <label>Category</label>
        <select name="category">
            <option value="">-- Any Category --</option>
            <option value="Phone" <?php if ($category === 'Phone') echo 'selected'; ?>>Phone</option>
            <option value="Laptop" <?php if ($category === 'Laptop') echo 'selected'; ?>>Laptop</option>
            <option value="Bag" <?php if ($category === 'Bag') echo 'selected'; ?>>Bag</option>
            <option value="Wallet" <?php if ($category === 'Wallet') echo 'selected'; ?>>Wallet</option>
            <option value="ID Card" <?php if ($category === 'ID Card') echo 'selected'; ?>>ID Card</option>
            <option value="Book" <?php if ($category === 'Book') echo 'selected'; ?>>Book</option>
            <option value="Keys" <?php if ($category === 'Keys') echo 'selected'; ?>>Keys</option>
            <option value="Electronics" <?php if ($category === 'Electronics') echo 'selected'; ?>>Electronics</option>
            <option value="Clothing" <?php if ($category === 'Clothing') echo 'selected'; ?>>Clothing</option>
            <option value="Others" <?php if ($category === 'Others') echo 'selected'; ?>>Others</option>
        </select>
    </div>
    <div>
        <label>Location</label>
        <input type="text" name="location" placeholder="Library, Lab..." value="<?php echo htmlspecialchars($location); ?>">
    </div>
    <div style="display:flex;gap:10px;">
        <button class="btn" type="submit">Filter</button>
        <a class="btn" href="lost-items.php" style="background:#7f8c8d;">Reset</a>
    </div>
</form>

<table width="100%" cellpadding="10">
<tr style="background:#7a0000;color:white;">
    <th>Image</th>
    <th>Item</th>
    <th>Category</th>
    <th>Location</th>
    <th>Posted By</th>
    <th>Status</th>
</tr>

<?php if (mysqli_num_rows($query) == 0) { ?>
<tr>
    <td colspan="6" align="center">No lost items found.</td>
</tr>
<?php } ?>

<?php while ($row = mysqli_fetch_assoc($query)) { ?>
<tr>
    <td>
        <?php if ($row['image']) { ?>
            <img src="../assets/images/<?php echo htmlspecialchars($row['image']); ?>"
                 style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
        <?php } ?>
    </td>

    <td><?php echo htmlspecialchars($row['title']); ?></td>
    <td><?php echo htmlspecialchars($row['category']); ?></td>
    <td><?php echo htmlspecialchars($row['location']); ?></td>
    <td><?php echo htmlspecialchars($row['poster']); ?></td>

    <td>
        <span class="badge lost">Lost</span>
    </td>
</tr>
<?php } ?>

</table>

</div>
</div>

<div class="footer">
    © <?php echo date("Y"); ?> CampusFind
</div>
</div>
</body>
</html>
