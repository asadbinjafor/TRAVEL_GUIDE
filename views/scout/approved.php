<h1 class="page-title">My Approved Posts</h1>
<?php if (empty($posts)): ?>
    <p>No approved posts yet.</p>
<?php else: ?>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Title</th><th>Country</th><th>Genre</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($posts as $p): ?>
            <tr>
                <td><?= Security::e($p['title']) ?></td>
                <td><?= Security::e($p['country']) ?></td>
                <td><?= Security::e($p['genre']) ?></td>
                <td>
                    <a class="btn btn-outline btn-sm" href="<?= url('/posts/detail', ['id' => (int) $p['id']]) ?>">View</a>
                    <a class="btn btn-accent btn-sm" href="<?= url('/scout/change-request', ['post_id' => (int) $p['id']]) ?>">Request changes</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
