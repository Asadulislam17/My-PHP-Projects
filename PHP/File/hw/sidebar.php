<div class="sidebar shadow">
    <ul class="nav flex-column py-4">
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-home mr-2"></i> ড্যাশবোর্ড
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>" href="admin.php">
                <i class="fas fa-plus-circle mr-2"></i> ডাটা এন্ট্রি (Admin)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'edit-profile.php') ? 'active' : ''; ?>" href="edit-profile.php">
                <i class="fas fa-user-edit mr-2"></i> প্রোফাইল আপডেট
            </a>
        </li>
        <li class="nav-item mt-5 pt-5 border-top border-secondary">
            <a class="nav-link text-danger logout-link" href="logout.php">
                <i class="fas fa-sign-out-alt mr-2"></i> লগআউট
            </a>
        </li>
    </ul>
</div>
