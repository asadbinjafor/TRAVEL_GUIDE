<div class="detail-hero">
    <h1 class="page-title"><?= Security::e($post['title']) ?></h1>
    <p class="meta">
        <?= Security::e($post['country']) ?> &middot; <?= Security::e($post['genre']) ?>
        <span class="badge badge-<?= Security::e($post['cost_level']) ?>"><?= Security::e($post['cost_level']) ?> cost</span>
    </p>
    <?php if ($canComment && !$inWishlist): ?>
        <button type="button" class="btn btn-outline btn-sm wishlist-add" data-post-id="<?= (int) $post['id'] ?>">Add to wishlist</button>
    <?php endif; ?>
</div>

<?php if (!empty($images)): ?>
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px">
    <?php foreach ($images as $img): ?>
        <img src="<?= Security::e(uploadUrl($img, 'post')) ?>" alt="" style="max-width:200px;border-radius:8px">
    <?php endforeach; ?>
</div>
<?php endif; ?>

<p><?= nl2br(Security::e($post['short_history'])) ?></p>
<p style="margin-top:16px"><strong>How to get there:</strong> <?= Security::e($post['travel_medium_info']) ?></p>

<div class="cost-box" id="cost-calculator" data-base="<?= (float) $cost['base_cost'] ?>" data-currency="<?= Security::e($cost['currency']) ?>">
    <h3>Probable trip cost</h3>
    <p>Base estimate: <strong><?= Security::e($cost['currency']) ?> <?= number_format((float) $cost['base_cost'], 2) ?></strong></p>
    <div class="filter-row">
        <div class="form-group">
            <label for="calc-travelers">Travelers (1–10)</label>
            <input type="number" id="calc-travelers" min="1" max="10" value="2">
        </div>
        <div class="form-group">
            <label for="calc-days">Days</label>
            <input type="number" id="calc-days" min="1" max="365" value="7">
        </div>
    </div>
    <p class="total" id="calc-total"></p>
</div>

<h2 style="margin-top:32px;font-family:var(--font-display)">Comments</h2>
<ul class="comment-list" id="comment-list">
    <?php foreach ($comments as $c): ?>
    <li class="comment-item" data-id="<?= (int) $c['id'] ?>">
        <div class="author"><?= Security::e($c['user_name']) ?></div>
        <div class="date"><?= Security::e($c['created_at']) ?></div>
        <div class="text"><?= Security::e($c['content']) ?></div>
        <?php if ($canComment && (int) $c['user_id'] === Auth::user()['id']): ?>
            <button type="button" class="btn btn-danger btn-sm comment-delete" data-id="<?= (int) $c['id'] ?>" style="margin-top:8px">Delete</button>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>

<?php if ($canComment): ?>
<form id="comment-form" class="form-card" style="max-width:560px;margin-top:20px">
    <input type="hidden" id="comment-post-id" value="<?= (int) $post['id'] ?>">
    <div class="form-group">
        <label for="display_name">Your name</label>
        <input type="text" id="display_name" value="<?= Security::e(Auth::user()['name']) ?>">
    </div>
    <div class="form-group">
        <label for="comment_content">Comment</label>
        <textarea id="comment_content" rows="3" maxlength="1000" required></textarea>
        <div class="field-error js-error" id="comment-error" style="display:none"></div>
    </div>
    <button type="submit" class="btn btn-primary">Post comment</button>
</form>
<?php endif; ?>
