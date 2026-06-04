<h1 class="page-title">Add User</h1>
<div class="form-card">
    <form method="post" action="<?= url('/admin/users/add') ?>">
        <?= Security::csrfField() ?>
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?= Security::e($old['name'] ?? '') ?>" required>
            <?php if (!empty($errors['name'])): ?><div class="field-error"><?= Security::e($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= Security::e($old['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="user">General User</option>
                <option value="scout">Scout</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_verified" value="1" checked> Verified immediately</label>
        </div>
        <button type="submit" class="btn btn-primary">Create user</button>
    </form>
</div>
