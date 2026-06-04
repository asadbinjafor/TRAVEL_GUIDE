<?php if ($flash): ?>
    <div class="alert alert-success"><?= Security::e($flash) ?></div>
<?php endif; ?>

<?php if (!$currentUser): ?>
    <section class="hero">
        <h1>Discover the World</h1>
        <p>Travel Guide helps you explore destinations worldwide with scout-curated tips on cost, culture, and how to get there.</p>
        <a class="btn btn-accent" href="<?= url('/register') ?>">Create account</a>
        <a class="btn btn-outline" href="<?= url('/login') ?>" style="color:#fff;border-color:rgba(255,255,255,0.5)">Login</a>
    </section>
<?php elseif ($currentUser['role'] === 'admin'): ?>
    <h1 class="page-title">Welcome, <?= Security::e($currentUser['name']) ?></h1>
    <?php if (!$currentUser['is_verified']): ?>
        <div class="alert alert-warning">
            Your admin account is not verified yet. Go to <a href="<?= url('/admin/users') ?>">User Management</a> and click <strong>Verify</strong> on your row, or ask another verified admin to verify you.
        </div>
    <?php endif; ?>
    <p class="page-sub">Admin control panel</p>
    <a class="btn btn-primary" href="<?= url('/admin') ?>">Open dashboard</a>
    <a class="btn btn-outline" href="<?= url('/admin/users') ?>">Manage users &amp; verify admins</a>
<?php elseif (!$currentUser['is_verified'] || $pending): ?>
    <div class="alert alert-warning">
        Your account is pending admin approval. You cannot access detailed site features until verified.
    </div>
<?php else: ?>
    <h1 class="page-title">Welcome, <?= Security::e($currentUser['name']) ?></h1>
    <p class="page-sub">Latest approved destinations</p>
    <?php
    $showWishlist = Auth::isVerifiedGeneralUser();
    $wishlistIds = [];
    if ($showWishlist) {
        $wl = (new WishlistModel())->forUser($currentUser['id']);
        $wishlistIds = array_map(fn($i) => (int) $i['id'], $wl);
    }
    require ROOT_DIR . '/views/partials/post_cards.php';
    ?>
    <p style="margin-top:24px"><a class="btn btn-primary" href="<?= url('/posts') ?>">Browse all destinations</a></p>
<?php endif; ?>
