<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Delete item (and related claims)
if (isset($_GET['delete'])) {
    $item_id = intval($_GET['delete']);

    // Optional: delete related claims first
    mysqli_query($conn, "DELETE FROM claims WHERE item_id='$item_id'");

    // Then delete the item
    mysqli_query($conn, "DELETE FROM items WHERE id='$item_id'");

    header("Location: items.php");
    exit;
}

$items = mysqli_query($conn, "
    SELECT items.*, users.name AS poster
    FROM items
    JOIN users ON items.user_id = users.id
    ORDER BY items.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Items | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="page-wrapper">
<div class="header">
    <h1>All Items – Admin</h1>
    <a href="index.php" class="btn">Dashboard</a>
</div>

<div class="container">
<div class="card">

<table width="100%" cellpadding="12">
<tr style="background:#003366;color:white;">
    <th>Image</th>
    <th>Item</th>
    <th>Category</th>
    <th>Posted By</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($items)) { ?>
<tr>
    <td>
        <?php if ($row['image']) { ?>
            <img src="../assets/images/<?php echo $row['image']; ?>"
                 style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
        <?php } ?>
    </td>
    <td><?php echo $row['title']; ?></td>
    <td><?php echo $row['category']; ?></td>
    <td><?php echo $row['poster']; ?></td>
    <td>
        <span class="badge <?php echo $row['status']; ?>">
            <?php echo ucfirst($row['status']); ?>
        </span>
    </td>
    <td>
        <a href="items.php?delete=<?php echo $row['id']; ?>"
           class="btn"
           style="background:#e63946;"
           onclick="return confirm('Are you sure you want to delete this item?');">
            Delete
        </a>
    </td>
</tr>
<?php } ?>

</table>

</div>
</div>

<div class="footer">© <?php echo date("Y"); ?> CampusFind | Admin</div>
</div>
</body>
</html>
