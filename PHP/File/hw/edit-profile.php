<?php
session_start();
include('db.php');

// ইউজার লগইন না থাকলে ফিরিয়ে দাও
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];
$success = "";
$error = "";

// বর্তমান তথ্য ডেটাবেজ থেকে আনা
$query = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// আপডেট বাটন ক্লিক করলে
if (isset($_POST['update'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $update_query = "UPDATE users SET full_name='$full_name', contact_number='$contact', address='$address' WHERE username='$username'";

    if (mysqli_query($conn, $update_query)) {
        $success = "প্রোফাইল সফলভাবে আপডেট হয়েছে!";
        // আপডেট হওয়ার পর নতুন ডেটা রিফ্রেশ করা
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
    } else {
        $error = "আপডেট করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile - My App</title>
    
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <style>
        body { background-color: #f4f7f6; overflow-x: hidden; }
        .sidebar { 
            position: sticky; 
            top: 56px; 
            height: calc(100vh - 56px); 
            background: #212529; 
            z-index: 1000;
        }
        .nav-link { color: rgba(255,255,255,.75); padding: 12px 20px; transition: 0.3s; }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,.1); text-decoration: none; }
        .nav-link.active { color: #fff; background: #0d6efd; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .form-label { font-weight: 600; color: #555; font-size: 14px; }
    </style>
</head>
<body>

<!-- ১. টপ নেভবার ইনক্লুড -->
<?php include('navbar.php'); ?>

<div class="container-fluid">
    <div class="row">
        
        <!-- ২. সাইডবার ইনক্লুড -->
        <div class="col-md-3 col-lg-2 p-0 shadow-sm">
            <?php include('sidebar.php'); ?>
        </div>

        <!-- ৩. মেইন কন্টেন্ট এলাকা -->
        <main class="col-md-9 ml-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-person-gear mr-2"></i>প্রোফাইল সেটিংস</h1>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-white font-weight-bold py-3">
                            <i class="bi bi-pencil-square mr-1"></i> আপনার তথ্য আপডেট করুন
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- মেসেজ প্রদর্শন -->
                            <?php if($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle-fill mr-2"></i> <?php echo $success; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if($error): ?>
                                <div class="alert alert-danger p-2 small"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">পুরো নাম</label>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">কন্টাক্ট নম্বর</label>
                                        <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">ইউজারনেম (পরিবর্তনযোগ্য নয়)</label>
                                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">ইমেইল অ্যাড্রেস (পরিবর্তনযোগ্য নয়)</label>
                                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">বর্তমান ঠিকানা</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="আপনার বিস্তারিত ঠিকানা লিখুন"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="dashboard.php" class="btn btn-light mr-2">বাতিল করুন</a>
                                    <button type="submit" name="update" class="btn btn-primary px-4 shadow-sm">
                                        <i class="bi bi-save mr-1"></i> সেভ করুন
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    </div>
</div>

<!-- জাভাস্ক্রিপ্ট ফাইলসমূহ -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>
