<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$msg = "";
$msg_type = "success";
$suggestions = [];

if (isset($_POST['submit'])) {

    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $location    = mysqli_real_escape_string($conn, $_POST['location']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);

    // IMAGE UPLOAD
    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
        $img_name = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $new_name = "item_" . time() . "." . $ext;
        $destination = "../assets/images/" . $new_name;

        if (move_uploaded_file($tmp_name, $destination)) {
            $image = $new_name;
        } else {
            $msg = "Image upload failed!";
            $msg_type = "error";
        }
    }

    if ($msg == "") {
        $user_id = $_SESSION['user_id'];
        $insert = mysqli_query($conn, "INSERT INTO items (user_id,title,description,category,location,status,image) VALUES ('$user_id','$title','$description','$category','$location','$status','$image')");

        if ($insert) {
            $msg = "Item posted successfully!";
            $msg_type = "success";

            $opposite = ($status === "lost") ? "found" : "lost";
            $titleEsc = mysqli_real_escape_string($conn, $title);
            $catEsc = mysqli_real_escape_string($conn, $category);
            $locEsc = mysqli_real_escape_string($conn, $location);

            $sugQuery = mysqli_query($conn, "
                SELECT id, title, category, location, image, created_at,
                    (
                        (CASE WHEN category LIKE '%$catEsc%' THEN 3 ELSE 0 END) +
                        (CASE WHEN location LIKE '%$locEsc%' THEN 2 ELSE 0 END) +
                        (CASE WHEN title LIKE '%$titleEsc%' THEN 1 ELSE 0 END)
                    ) AS score
                FROM items
                WHERE status='$opposite'
                ORDER BY score DESC, created_at DESC
                LIMIT 5
            ");

            if ($sugQuery) {
                while ($r = mysqli_fetch_assoc($sugQuery)) {
                    $suggestions[] = $r;
                }
            }
        } else {
            $msg = "Failed to post item!";
            $msg_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Item | CampusFind</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page-wrapper">
<div class="header">
    <div style="display:flex;align-items:center;gap:15px;">
        <img src="../assets/images/college-logo.png">
        <h1>CampusFind</h1>
    </div>
    <div>
        <a href="dashboard.php" class="btn">Dashboard</a>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</div>

<div class="container">
    <div class="card" style="max-width:600px;margin:auto;">
        <h2>Post Lost / Found Item</h2>

        <?php if ($msg != "") { ?>
            <p style="color:<?php echo ($msg_type === 'error') ? '#e63946' : '#27ae60'; ?>;">
                <?php echo htmlspecialchars($msg); ?>
            </p>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Item Name" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <label>Category:</label>
            <select name="category" required>
                <option value="">-- Select Category --</option>
                <option value="Phone">Phone</option>
                <option value="Laptop">Laptop</option>
                <option value="Bag">Bag</option>
                <option value="Wallet">Wallet</option>
                <option value="ID Card">ID Card</option>
                <option value="Book">Book</option>
                <option value="Keys">Keys</option>
                <option value="Electronics">Electronics</option>
                <option value="Clothing">Clothing</option>
                <option value="Others">Others</option>
            </select>
            <input type="text" name="location" placeholder="Location (Library, Lab, Canteen)" required>

            <label>Status:</label>
            <select name="status" required>
                <option value="lost">Lost</option>
                <option value="found">Found</option>
            </select>

            <label>Upload Image:</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit" name="submit" class="btn">Post Item</button>
        </form>
    </div>
    <?php if ($msg_type === "success" && count($suggestions) > 0) { ?>
        <div class="card" style="max-width:900px;margin:20px auto 0;">
            <h2>Possible Matches</h2>
            <p style="color:#555;margin-bottom:10px;">
                We found items that might match your post.
            </p>

            <table width="100%" cellpadding="10">
                <tr style="background:#003366;color:white;">
                    <th>Image</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($suggestions as $s) { ?>
                    <tr>
                        <td style="width:80px;">
                            <?php if (!empty($s['image'])) { ?>
                                <img src="../assets/images/<?php echo htmlspecialchars($s['image']); ?>"
                                     style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($s['title']); ?></td>
                        <td><?php echo htmlspecialchars($s['category']); ?></td>
                        <td><?php echo htmlspecialchars($s['location']); ?></td>
                        <td>
                            <?php if ($status === "lost") { ?>
                                <a class="btn" style="background:#27ae60;" href="found-items.php?q=<?php echo urlencode($title); ?>">
                                    View Found
                                </a>
                            <?php } else { ?>
                                <a class="btn" style="background:#e74c3c;" href="lost-items.php?q=<?php echo urlencode($title); ?>">
                                    View Lost
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
</div>

<div class="footer">
    © <?php echo date("Y"); ?> CampusFind
</div>
</div>
</body>
</html>
