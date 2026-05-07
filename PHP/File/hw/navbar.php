<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <a class="navbar-brand font-weight-bold" href="dashboard.php">
        <i class="fas fa-code mr-2 text-info"></i>My App Panel
    </a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto align-items-center">
            <li class="nav-item">
                <span class="nav-link text-light">
                    <i class="fas fa-user-circle mr-1"></i> <?php echo $_SESSION['user']; ?>
                </span>
            </li>
            <li class="nav-item ml-lg-3">
                <a class="btn btn-outline-danger btn-sm" href="logout.php">
                    <i class="fas fa-power-off"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
