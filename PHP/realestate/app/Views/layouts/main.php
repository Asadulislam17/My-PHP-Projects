<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\core\App::config('app.lang', 'en')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= htmlspecialchars($title ?? \App::config('app.name', 'Real Estate')) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description ?? '') ?>">

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= htmlspecialchars($title ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description ?? '') ?>">
    <meta property="og:type"        content="website">

    <!-- Favicon -->
    <link rel="icon" href="/public/img/favicon.ico" type="image/x-icon">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/realestate/public/css/app.css">

    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>

<!-- ── Navigation ─────────────────────────────────────────────────── -->
<nav class="navbar">
    <div class="container navbar__inner">
        <a href="/" class="navbar__brand">
            <i class="fa-solid fa-building"></i>
            <?= htmlspecialchars(\Core\App::config('app.name', 'Real Estate')) ?>
        </a>

        <ul class="navbar__links">
            <li><a href="/">Home</a></li>
            <li><a href="/realestate/public/properties">Properties</a></li>
            <li><a href="/realestate/public/agent">Agents</a></li>
            <li><a href="/realestate/public/blog">Blog</a></li>
        </ul>

        <div class="navbar__actions">
            <?php if (isset($auth) && $auth): ?>
                <a href="/realestate/public/dashboard" class="btn btn--ghost">Dashboard</a>
                <a href="/realestate/public/auth/logout" class="btn btn--outline">Logout</a>
            <?php else: ?>
                <a href="/realestate/public/auth/login"    class="btn btn--ghost">Login</a>
                <a href="/realestate/public/auth/register" class="btn btn--primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ── Flash Messages ─────────────────────────────────────────────── -->
<?php if (!empty($flash)): ?>
    <div class="flash-container container">
        <?php foreach ($flash as $type => $messages): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="flash flash--<?= htmlspecialchars($type) ?>">
                    <i class="fa-solid <?= $type === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
                    <?= htmlspecialchars($msg) ?>
                    <button class="flash__close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ── Page Content ───────────────────────────────────────────────── -->
<main class="main-content">
    <?= $content ?>
</main>

<!-- ── Footer ────────────────────────────────────────────────────── -->
<footer class="footer">
    <div class="container footer__inner">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(\core\App::config('app.name', 'Real Estate')) ?>. All rights reserved.</p>
        <ul class="footer__links">
            <li><a href="/privacy">Privacy Policy</a></li>
            <li><a href="/terms">Terms of Service</a></li>
            <li><a href="/contact">Contact</a></li>
        </ul>
    </div>
</footer>

<!-- JS -->
<script src="/realestate/public/js/app.js"></script>

<?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>

</body>
</html>
