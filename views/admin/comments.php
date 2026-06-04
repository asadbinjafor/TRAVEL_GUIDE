<h1 class="page-title">Comment Moderation</h1>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Post</th><th>User</th><th>Comment</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($comments as $c): ?>
            <tr id="comment-row-<?= (int) $c['id'] ?>">
                <td><?= Security::e($c['post_title']) ?></td>
                <td><?= Security::e($c['user_name']) ?></td>
                <td><?= Security::e($c['content']) ?></td>
                <td><?= Security::e($c['created_at']) ?></td>
                <td><button class="btn btn-danger btn-sm admin-delete-comment" data-id="<?= (int) $c['id'] ?>">Delete</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
