<h1 class="page-title">Edit Post</h1>
<div class="form-card" style="max-width:640px">
    <form method="post" action="<?= url('/admin/posts/edit') ?>">
        <?= Security::csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
        <div class="form-group"><label>Title</label><input name="title" value="<?= Security::e($post['title']) ?>" required></div>
        <div class="form-group"><label>History</label><textarea name="short_history" rows="4" required><?= Security::e($post['short_history']) ?></textarea></div>
        <div class="form-group"><label>Country</label><input name="country" value="<?= Security::e($post['country']) ?>" required></div>
        <div class="form-group"><label>Genre</label>
            <select name="genre"><?php foreach (GENRES as $g): ?><option value="<?= $g ?>" <?= $post['genre'] === $g ? 'selected' : '' ?>><?= Security::e($g) ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-group"><label>Cost</label>
            <select name="cost_level"><?php foreach (['low','medium','high'] as $c): ?><option value="<?= $c ?>" <?= $post['cost_level'] === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-group"><label>Travel info</label><textarea name="travel_medium_info" rows="2" required><?= Security::e($post['travel_medium_info']) ?></textarea></div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
