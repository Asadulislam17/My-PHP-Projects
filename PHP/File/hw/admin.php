<?php
session_start();
include('db.php');

// ইউজার লগইন না থাকলে ফিরিয়ে দাও
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// ১. ডেটা ডিলিট লজিক (ছবিসহ)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // ফোল্ডার থেকে ছবি ডিলিট করার জন্য কুয়েরি
    $img_query = mysqli_query($conn, "SELECT image FROM birds WHERE id = '$delete_id'");
    $img_data = mysqli_fetch_assoc($img_query);
    
    if ($img_data) {
        $file_path = "uploads/" . $img_data['image'];
        if (file_exists($file_path)) {
            unlink($file_path); // ফোল্ডার থেকে ডিলিট
        }
        mysqli_query($conn, "DELETE FROM birds WHERE id = '$delete_id'");
        echo "<script>alert('সফলভাবে ডিলিট হয়েছে!'); window.location='admin.php';</script>";
    }
}

// ২. ডেটা ইনসার্ট লজিক
if (isset($_POST['submit'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $image_name = $_FILES['image']['name'];
    $target = "uploads/" . basename($image_name);

    if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $sql = "INSERT INTO birds (id, name, image) VALUES ('$id', '$name', '$image_name')";
        if(mysqli_query($conn, $sql)) {
            echo "<script>alert('সফলভাবে যুক্ত হয়েছে!'); window.location='admin.php';</script>";
        } else {
            echo "<script>alert('ভুল: এই আইডিটি ইতিমধ্যে আছে!');</script>";
        }
    }
}

$result = mysqli_query($conn, "SELECT * FROM birds");
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - Bird Entry</title>
    
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cloudflare.com">
    
    <style>
        body { background-color: #f4f7f6; overflow-x: hidden; }
        .sidebar { position: sticky; top: 56px; height: calc(100vh - 56px); background: #212529; z-index: 1000; }
        .nav-link { color: rgba(255,255,255,.75); padding: 12px 20px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: #fff; background: #0d6efd; text-decoration: none; }
        .bird-img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; cursor: pointer; transition: 0.2s; border: 1px solid #ddd; }
        .bird-img:hover { transform: scale(1.1); border-color: #0d6efd; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        #preview { width: 120px; height: 120px; object-fit: cover; display: none; border-radius: 8px; margin-top: 10px; border: 2px solid #0d6efd; }
        /* Modal Customization */
        .modal-content { background: rgba(0,0,0,0.8); border: none; }
        .close-btn { position: absolute; top: -40px; right: 0; color: white; font-size: 30px; cursor: pointer; background: none; border: none; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 p-0 shadow-sm">
            <?php include('sidebar.php'); ?>
        </div>

        <main class="col-md-9 ml-sm-auto col-lg-10 px-4 py-4">
            <div class="pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-plus-circle mr-2 text-primary"></i>পাখি এন্ট্রি প্যানেল</h1>
            </div>

            <!-- ফরম সেকশন -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white font-weight-bold">নতুন তথ্য যোগ করুন</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row align-items-start">
                            <div class="col-md-2 mb-3">
                                <label class="font-weight-bold small">ID</label>
                                <input type="number" name="id" class="form-control" placeholder="01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold small">পাখির নাম</label>
                                <input type="text" name="name" class="form-control" placeholder="যেমন: টিয়া" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold small">ছবি সিলেক্ট করুন</label>
                                <input type="file" name="image" id="imgInput" class="form-control-file" accept="image/*" required>
                                <img id="preview" src="#" alt="Preview">
                            </div>
                            <div class="col-md-2 mb-3 mt-md-4">
                                <button type="submit" name="submit" class="btn btn-primary btn-block shadow-sm">Save Data</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- টেবিল সেকশন -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 text-center">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>নাম</th>
                                <th>ছবি (ক্লিক করুন)</th>
                                <th>অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="align-middle">#<?php echo $row['id']; ?></td>
                                <td class="align-middle font-weight-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="align-middle">
                                    <img src="uploads/<?php echo $row['image']; ?>" class="bird-img" onclick="openModal('uploads/<?php echo $row['image']; ?>')" data-toggle="modal" data-target="#viewModal">
                                </td>
                                <td class="align-middle">
                                    <a href="admin.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('আপনি কি নিশ্চিত?')">
                                        <i class="fas fa-trash"></i> ডিলিট
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- ইমেজ ভিউ মোডাল -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="close-btn" data-dismiss="modal">&times;</button>
                <img id="modalImg" src="" class="img-fluid w-100 rounded shadow">
            </div>
        </div>
    </div>
</div>

<script src="https://jquery.com"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

<script>
    // ১. ইমেজ প্রিভিউ (সিলেক্ট করার সময়)
    const imgInput = document.getElementById('imgInput');
    const preview = document.getElementById('preview');
    imgInput.onchange = evt => {
        const [file] = imgInput.files;
        if (file) {
            preview.style.display = "block";
            preview.src = URL.createObjectURL(file);
        }
    }

    // ২. মোডালে বড় ছবি দেখানো
    function openModal(src) {
        document.getElementById('modalImg').src = src;
    }
</script>

</body>
</html>
