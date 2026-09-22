<h1 class="page-title">Profile</h1>
<?php if ($flash): ?>
    <div class="alert alert-success"><?= Security::e($flash) ?></div>
<?php endif; ?>
<?php
$profilePicture = $profile['profile_picture'] ?? null;
$profileName = $profile['name'] ?? '';
$profileEmail = $profile['email'] ?? '';
$pic = $profilePicture
    ? uploadUrl($profilePicture, 'profile')
    : 'https://ui-avatars.com/api/?name=' . urlencode($profileName) . '&background=0c6e8a&color=fff';
?>
<div class="form-card" style="max-width:560px">
    <img class="profile-avatar" src="<?= Security::e($pic) ?>" alt="Avatar" style="margin-bottom:20px">
    <form method="post" action="<?= url('/profile') ?>" enctype="multipart/form-data" id="profile-form" novalidate>
        <?= Security::csrfField() ?>
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= Security::e($profileName) ?>" required>
            <?php if (!empty($errors['name'])): ?><div class="field-error"><?= Security::e($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= Security::e($profileEmail) ?>" required>
            <?php if (!empty($errors['email'])): ?><div class="field-error"><?= Security::e($errors['email']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="profile_picture">Profile picture</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($errors['profile_picture'])): ?><div class="field-error"><?= Security::e($errors['profile_picture']) ?></div><?php endif; ?>
        </div>
        <hr style="margin:24px 0;border:none;border-top:1px solid var(--border)">
        <div class="form-group">
            <label for="current_password">Current password (to change)</label>
            <input type="password" id="current_password" name="current_password">
            <?php if (!empty($errors['current_password'])): ?><div class="field-error"><?= Security::e($errors['current_password']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" minlength="8">
        </div>
        <div class="form-group">
            <label for="new_password_confirm">Confirm new password</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm">
            <?php if (!empty($errors['new_password_confirm'])): ?><div class="field-error"><?= Security::e($errors['new_password_confirm']) ?></div><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </form>
</div>
<?php $extraScripts = ['validation.js']; ?>
