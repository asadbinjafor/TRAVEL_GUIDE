<h1 class="page-title">Admin Dashboard</h1>
<div class="stats-grid">
    <div class="stat-card"><div class="num"><?= (int) ($roleCounts['admin'] ?? 0) ?></div><div class="label">Admins</div></div>
    <div class="stat-card"><div class="num"><?= (int) ($roleCounts['scout'] ?? 0) ?></div><div class="label">Scouts</div></div>
    <div class="stat-card"><div class="num"><?= (int) ($roleCounts['user'] ?? 0) ?></div><div class="label">Users</div></div>
    <div class="stat-card"><div class="num"><?= (int) $pendingRequests ?></div><div class="label">Pending requests</div></div>
    <div class="stat-card"><div class="num"><?= (int) $totalPosts ?></div><div class="label">Posts</div></div>
    <div class="stat-card"><div class="num"><?= (int) $totalComments ?></div><div class="label">Comments</div></div>
</div>
<div class="admin-section">
    <a class="btn btn-primary" href="<?= url('/admin/users') ?>">Manage users</a>
    <a class="btn btn-primary" href="<?= url('/admin/posts') ?>">Moderate posts</a>
    <a class="btn btn-primary" href="<?= url('/admin/comments') ?>">Moderate comments</a>
</div>
