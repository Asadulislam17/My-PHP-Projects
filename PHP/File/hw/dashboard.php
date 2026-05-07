<?php
session_start();
include('db.php');

if (!isset($_SESSION['user'])) { 
    header("Location: login.php"); 
    exit(); 
}

$username = $_SESSION['user'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE username='$username' LIMIT 1"));

// ডামি স্ট্যাটাস (রিয়েল প্রজেক্টে এগুলো ডেটাবেজ থেকে আসে)
$total_birds = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM birds"));
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - My App</title>
    
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Font Awesome এর সঠিক লিঙ্ক -->
    <link rel="stylesheet" href="https://cloudflare.com">
    
    <style>
        body { background-color: #f4f7f6; overflow-x: hidden; }
        .sidebar { position: sticky; top: 56px; height: calc(100vh - 56px); background: #212529; z-index: 1000; }
        .nav-link { color: rgba(255,255,255,.75); padding: 12px 20px; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background: #0d6efd; text-decoration: none; }
        .profile-card { border: none; border-radius: 15px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .profile-header { background: #0d6efd; height: 100px; }
        .stat-card { border: none; border-left: 5px solid #0d6efd; border-radius: 10px; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid">
    <div class="row">
        <!-- সাইডবার -->
        <div class="col-md-3 col-lg-2 p-0 d-none d-md-block shadow-sm">
            <?php include('sidebar.php'); ?>
        </div>

        <!-- মেইন কন্টেন্ট -->
        <main class="col-md-9 ml-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h4 text-dark"><i class="fas fa-tachometer-alt mr-2 text-primary"></i>ড্যাশবোর্ড ওভারভিউ</h1>
                <span class="badge badge-success p-2"><i class="fas fa-clock mr-1"></i> অনলাইন</span>
            </div>

            <!-- কুইক স্ট্যাটাস কার্ডস -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white p-3 rounded-circle mr-3">
                                <i class="fas fa-kiwi-bird fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">মোট পাখি (Birds)</h6>
                                <h4 class="font-weight-bold mb-0"><?php echo $total_birds; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm p-3" style="border-left-color: #28a745;">
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white p-3 rounded-circle mr-3">
                                <i class="fas fa-user-check fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">অ্যাকাউন্ট স্ট্যাটাস</h6>
                                <h4 class="font-weight-bold mb-0 text-success">Active</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- প্রোফাইল ইনফো -->
                <div class="col-md-6 col-lg-5 mb-4">
                    <div class="card profile-card">
                        <div class="profile-header"></div>
                        <div class="card-body text-center mt-n5" style="margin-top: -50px;">
                            <!-- UI Avatars ফিক্স করা লিঙ্ক -->
                            <img src="https://ui-avatars.com<?php echo urlencode($user['full_name']); ?>&background=random&size=128" class="rounded-circle border border-4 border-white shadow-sm mb-3" width="100" height="100">
                            
                            <h5 class="mb-0 font-weight-bold"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                            <p class="text-muted small mb-3">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <hr>
                            <div class="text-left px-3 small">
                                <p class="mb-2"><i class="fas fa-envelope mr-2 text-primary"></i><strong>ইমেইল:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                <p class="mb-2"><i class="fas fa-phone mr-2 text-primary"></i><strong>ফোন:</strong> <?php echo htmlspecialchars($user['contact_number']); ?></p>
                                <p class="mb-0"><i class="fas fa-map-marker-alt mr-2 text-primary"></i><strong>ঠিকানা:</strong> <?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : 'নাই'; ?></p>
                            </div>
                            <a href="edit-profile.php" class="btn btn-outline-primary btn-sm mt-3 btn-block">প্রোফাইল আপডেট করুন</a>
                        </div>
                    </div>
                </div>

                <!-- অ্যাকশন বক্স -->
                <div class="col-md-6 col-lg-7">
                    <div class="card shadow-sm border-0 p-4 bg-white">
                        <h5 class="font-weight-bold text-dark">দ্রুত শুরু করুন</h5>
                        <p class="text-muted small">সিস্টেমের নতুন ডেটা এন্ট্রি করতে অ্যাডমিন প্যানেলে যান। কোনো সমস্যা হলে সাপোর্টে যোগাযোগ করুন।</p>
                        <div class="row mt-3">
                            <div class="col-6">
                                <a href="admin.php" class="btn btn-primary btn-block p-3">
                                    <i class="fas fa-plus-circle d-block mb-2 fa-lg"></i> নতুন পাখি যোগ
                                </a>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-info btn-block p-3">
                                    <i class="fas fa-file-alt d-block mb-2 fa-lg"></i> রিপোর্ট দেখুন
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>
