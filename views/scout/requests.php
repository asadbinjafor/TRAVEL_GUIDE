<h1 class="page-title">My Post Requests</h1>
<p class="page-sub"><a class="btn btn-primary btn-sm" href="<?= url('/scout/request/create') ?>">New request</a></p>
<?php if (empty($requests)): ?>
    <p>No requests yet.</p>
<?php else: ?>
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Country</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $r): $d = $r['post_data']; ?>
            <tr id="req-row-<?= (int) $r['id'] ?>">
                <td><?= Security::e($d['title'] ?? '') ?></td>
                <td><?= Security::e($d['country'] ?? '') ?></td>
                <td><span class="badge badge-<?= Security::e($r['status']) ?>"><?= Security::e($r['status']) ?></span></td>
                <td><?= Security::e($r['requested_at']) ?></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                        <a class="btn btn-outline btn-sm" href="<?= url('/scout/request/edit', ['id' => (int) $r['id']]) ?>">Edit</a>
                        <button type="button" class="btn btn-danger btn-sm scout-delete" data-id="<?= (int) $r['id'] ?>">Delete</button>
                    <?php endif; ?>
                    <?php if (!empty($r['original_post_id'])): ?>
                        <span class="badge badge-medium">Change req</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
