<?php
// Contact page logic (Optional: message handling)
$message_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // এখানে আপনার মেইল পাঠানোর লজিক বা ডাটাবেসে সেভ করার কোড লিখবেন
    $message_sent = true;
}
?>

<div class="container py-5">
    <div class="row g-5">
        <!-- Contact Information -->
        <div class="col-lg-5">
            <h2 class="mb-4">আমাদের সাথে যোগাযোগ করুন</h2>
            <p class="text-muted mb-5">আপনার যেকোনো প্রশ্ন বা প্রপার্টি সংক্রান্ত সাহায্যের জন্য আমাদের টিম সবসময় প্রস্তুত।</p>
            
            <div class="d-flex mb-4">
                <div class="icon-box bg-accent text-white p-3 rounded-circle me-3">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div>
                    <h5>ঠিকানা</h5>
                    <p>বাড়ি ১২, রোড ০৫, ধানমন্ডি, ঢাকা - ১২০৯</p>
                </div>
            </div>

            <div class="d-flex mb-4">
                <div class="icon-box bg-accent text-white p-3 rounded-circle me-3">
                    <i class="bi bi-telephone"></i>
                </div>
                <div>
                    <h5>ফোন করুন</h5>
                    <p>+৮৮০ ১৭০০-০০০০০০</p>
                </div>
            </div>

            <div class="d-flex mb-4">
                <div class="icon-box bg-accent text-white p-3 rounded-circle me-3">
                    <i class="bi bi-envelope"></i>
                </div>
                <div>
                    <h5>ইমেইল</h5>
                    <p>info@realestate.com</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 p-4">
                <?php if ($message_sent): ?>
                    <div class="alert alert-success">আপনার বার্তাটি সফলভাবে পাঠানো হয়েছে! আমরা শীঘ্রই যোগাযোগ করব।</div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">আপনার নাম</label>
                            <input type="text" name="name" class="form-control" required placeholder="নাম লিখুন">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ইমেইল</label>
                            <input type="email" name="email" class="form-control" required placeholder="email@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label">বিষয়</label>
                            <input type="text" name="subject" class="form-control" placeholder="কি বিষয়ে জানতে চান?">
                        </div>
                        <div class="col-12">
                            <label class="form-label">বার্তা</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="আপনার বার্তাটি বিস্তারিত লিখুন..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-accent btn-lg w-100">বার্তা পাঠান</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.bg-accent { background-color: #ff5a3c; } /* আপনার থিম কালার অনুযায়ী পরিবর্তন করতে পারেন */
.icon-box { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; }
</style>
