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
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <?php foreach (['user' => 'General User', 'scout' => 'Scout', 'admin' => 'Admin'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($old['role'] ?? 'user') === $val ? 'selected' : '' ?>><?= Security::e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
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
