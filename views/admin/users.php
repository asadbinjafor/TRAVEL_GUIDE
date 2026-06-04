<h1 class="page-title">User Management</h1>
<?php if ($flash): ?><div class="alert alert-success"><?= Security::e($flash) ?></div><?php endif; ?>
<p class="page-sub">Verify scouts, general users, and <strong>new admin</strong> accounts. Admins appear in this list.</p>
<p><a class="btn btn-primary btn-sm" href="<?= url('/admin/users/add') ?>">Add user</a></p>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr id="user-row-<?= (int) $u['id'] ?>">
                <td><?= Security::e($u['name']) ?></td>
                <td><?= Security::e($u['email']) ?></td>
                <td><span class="badge badge-role"><?= Security::e($u['role']) ?></span></td>
                <td>
                    <?php if ($u['is_verified']): ?>
                        <span class="badge badge-approved">Verified</span>
                    <?php else: ?>
                        <span class="badge badge-pending">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button type="button" class="btn btn-outline btn-sm admin-verify" data-user-id="<?= (int) $u['id'] ?>" data-verified="<?= (int) $u['is_verified'] ?>">
                        <?= $u['is_verified'] ? 'Unverify' : 'Verify' ?>
                    </button>
                    <?php if ($u['role'] !== 'admin'): ?>
                        <button type="button" class="btn btn-danger btn-sm admin-delete-user" data-user-id="<?= (int) $u['id'] ?>">Delete</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
