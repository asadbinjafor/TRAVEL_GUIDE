<h1 class="page-title">My Wishlist</h1>
<p class="page-sub">Your saved destinations checklist</p>
<?php if (empty($items)): ?>
    <p>No items yet. <a href="<?= url('/posts') ?>">Browse posts</a> to add some.</p>
<?php else: ?>
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Country</th><th>Cost</th><th>Genre</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr id="wish-row-<?= (int) $item['id'] ?>">
                <td><a href="<?= url('/posts/detail', ['id' => (int) $item['id']]) ?>"><?= Security::e($item['title']) ?></a></td>
                <td><?= Security::e($item['country']) ?></td>
                <td><span class="badge badge-<?= Security::e($item['cost_level']) ?>"><?= Security::e($item['cost_level']) ?></span></td>
                <td><?= Security::e($item['genre']) ?></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm wishlist-remove" data-post-id="<?= (int) $item['id'] ?>">Remove</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
