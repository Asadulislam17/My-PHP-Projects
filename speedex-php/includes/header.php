<?php if (session_status()===PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SpeedEx Courier</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dark">
<header class="navbar">
  <a href="/" class="logo">⚡ SpeedEx <small>Courier Service</small></a>
  <nav>
    <a href="/">Home</a>
    <a href="/track.php">Track Parcel</a>
    <a href="/hubs.php">Find Hub</a>
    <a href="/services.php">Services</a>
    <a href="/contact.php">Contact</a>
  </nav>
  <div class="actions">
    <?php if (isset($_SESSION['user'])): ?>
      <a href="/<?= $_SESSION['user']['role'] ?>/dashboard.php" class="btn">Dashboard</a>
      <a href="/auth/logout.php" class="btn ghost">Logout</a>
    <?php else: ?>
      <a href="/auth/login.php" class="btn ghost">Login</a>
      <a href="/speedex-php/auth/register.php" class="btn">Register</a>
    <?php endif; ?>
  </div>
</header>
<main class="container">
