<?php
$isEdit = !empty($request);
$old = $old ?? [];
$action = $isEdit
    ? url('/scout/request/edit')
    : (!empty($original_post_id) ? url('/scout/change-request') : url('/scout/request/create'));
?>
<h1 class="page-title"><?= Security::e($title) ?></h1>
<div class="form-card" style="max-width:640px">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="scout-form" novalidate>
        <?= Security::csrfField() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
        <?php endif; ?>
        <?php if (!empty($original_post_id)): ?>
            <input type="hidden" name="original_post_id" value="<?= (int) $original_post_id ?>">
        <?php endif; ?>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?= Security::e($old['title'] ?? '') ?>" required>
            <?php if (!empty($errors['title'])): ?><div class="field-error"><?= Security::e($errors['title']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="short_history">Short history / cultural significance</label>
            <textarea id="short_history" name="short_history" rows="4" required><?= Security::e($old['short_history'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" value="<?= Security::e($old['country'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="genre">Genre</label>
            <select id="genre" name="genre" required>
                <?php foreach (GENRES as $g): ?>
                    <option value="<?= $g ?>" <?= ($old['genre'] ?? '') === $g ? 'selected' : '' ?>><?= Security::e(ucfirst($g)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="cost_level">Cost level</label>
            <select id="cost_level" name="cost_level" required>
                <?php foreach (['low', 'medium', 'high'] as $c): ?>
                    <option value="<?= $c ?>" <?= ($old['cost_level'] ?? '') === $c ? 'selected' : '' ?>><?= Security::e(ucfirst($c)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="travel_medium_info">Travel medium info</label>
            <textarea id="travel_medium_info" name="travel_medium_info" rows="2" required><?= Security::e($old['travel_medium_info'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="images">Images (optional)</label>
            <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
            <?php if (!empty($errors['images'])): ?><div class="field-error"><?= Security::e($errors['images']) ?></div><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Submit' ?> request</button>
    </form>
</div>
<?php $extraScripts = ['scout.js', 'validation.js']; ?>
