<h1 class="page-title">Post Moderation</h1>
<h2 style="margin:24px 0 12px;font-size:1.2rem">Pending requests</h2>
<?php if (empty($pending)): ?>
    <p class="page-sub">No pending requests.</p>
<?php else: ?>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Title</th><th>Scout</th><th>Country</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($pending as $r): $d = $r['post_data']; ?>
            <tr id="pending-<?= (int) $r['id'] ?>">
                <td><?= Security::e($d['title'] ?? '') ?><?php if ($r['original_post_id']): ?> <span class="badge badge-medium">Change</span><?php endif; ?></td>
                <td><?= Security::e($r['scout_name']) ?></td>
                <td><?= Security::e($d['country'] ?? '') ?></td>
                <td>
                    <button class="btn btn-primary btn-sm admin-approve" data-id="<?= (int) $r['id'] ?>">Approve</button>
                    <button class="btn btn-danger btn-sm admin-reject" data-id="<?= (int) $r['id'] ?>">Reject</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<h2 style="margin:32px 0 12px;font-size:1.2rem">All posts</h2>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Title</th><th>Status</th><th>Scout</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($posts as $p): ?>
            <tr>
                <td><?= Security::e($p['title']) ?></td>
                <td><span class="badge badge-<?= Security::e($p['status']) ?>"><?= Security::e($p['status']) ?></span></td>
                <td><?= Security::e($p['scout_name'] ?? '') ?></td>
                <td>
                    <a class="btn btn-outline btn-sm" href="<?= url('/admin/posts/edit', ['id' => (int) $p['id']]) ?>">Edit</a>
                    <button class="btn btn-danger btn-sm admin-delete-post" data-id="<?= (int) $p['id'] ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
