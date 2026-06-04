<h1 class="page-title">Login</h1>
<?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger"><?= Security::e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash('success')): ?>
    <div class="alert alert-success"><?= Security::e($msg) ?></div>
<?php endif; ?>
<div class="form-card">
    <form method="post" action="<?= url('/login') ?>" id="login-form" novalidate>
        <?= Security::csrfField() ?>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= Security::e($old['email'] ?? '') ?>" required>
            <?php if (!empty($errors['email'])): ?><div class="field-error"><?= Security::e($errors['email']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <?php if (!empty($errors['password'])): ?><div class="field-error"><?= Security::e($errors['password']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="remember_me" value="1"> Remember me (30 days)</label>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Login</button>
    </form>
    <p style="margin-top:16px;text-align:center;font-size:0.9rem">
        No account? <a href="<?= url('/register') ?>">Register</a>
    </p>
</div>
<?php $extraScripts = ['validation.js']; ?>
