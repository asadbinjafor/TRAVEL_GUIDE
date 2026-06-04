<?php
/** @var array $posts */
/** @var bool $showWishlist */
$showWishlist = $showWishlist ?? false;
$wishlistIds = $wishlistIds ?? [];
?>
<?php if (empty($posts)): ?>
    <p class="page-sub">No destinations found.</p>
<?php else: ?>
<div class="post-grid" id="post-grid">
    <?php foreach ($posts as $post): ?>
    <article class="post-card" data-post-id="<?= (int) $post['id'] ?>">
        <div class="post-card-body">
            <h3><?= Security::e($post['title']) ?></h3>
            <div class="meta">
                <?= Security::e($post['country']) ?> &middot;
                <?= Security::e($post['genre']) ?>
                <span class="badge badge-<?= Security::e($post['cost_level']) ?>"><?= Security::e($post['cost_level']) ?></span>
            </div>
            <p class="snippet"><?= Security::e(mb_substr($post['short_history'], 0, 120)) ?>...</p>
            <div class="post-card-actions">
                <a class="btn btn-primary btn-sm" href="<?= url('/posts/detail', ['id' => (int) $post['id']]) ?>">Read more</a>
                <?php if ($showWishlist && !in_array((int) $post['id'], $wishlistIds, true)): ?>
                    <button type="button" class="btn btn-outline btn-sm wishlist-add" data-post-id="<?= (int) $post['id'] ?>">Save</button>
                <?php elseif ($showWishlist): ?>
                    <span class="badge badge-approved">Saved</span>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
