<h1 class="page-title">Register</h1>
<div class="form-card">
    <form method="post" action="<?= url('/register') ?>" id="register-form" novalidate>
        <?= Security::csrfField() ?>
        <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?= Security::e($old['name'] ?? '') ?>" required>
            <?php if (!empty($errors['name'])): ?><div class="field-error"><?= Security::e($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= Security::e($old['email'] ?? '') ?>" required>
            <?php if (!empty($errors['email'])): ?><div class="field-error"><?= Security::e($errors['email']) ?></div><?php endif; ?>
        </div>
        <p class="page-sub">New registrations create a general user account. An administrator can create scout or admin accounts.</p>
        <div class="form-group">
            <label for="password">Password (min 8)</label>
            <input type="password" id="password" name="password" required minlength="8">
            <?php if (!empty($errors['password'])): ?><div class="field-error"><?= Security::e($errors['password']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm password</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
            <?php if (!empty($errors['password_confirm'])): ?><div class="field-error"><?= Security::e($errors['password_confirm']) ?></div><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Register</button>
    </form>
</div>
<?php $extraScripts = ['validation.js']; ?>
