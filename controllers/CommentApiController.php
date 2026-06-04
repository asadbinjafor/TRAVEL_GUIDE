<?php
class CommentApiController
{
    public function add(): void
    {
        Auth::requireGeneralUser();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $postId = (int) ($input['post_id'] ?? 0);
        $content = trim($input['content'] ?? '');
        $displayName = trim($input['display_name'] ?? Auth::user()['name']);

        if ($postId <= 0 || $content === '') {
            Security::json(['success' => false, 'error' => 'Comment cannot be empty.'], 400);
        }
        if (mb_strlen($content) > 1000) {
            Security::json(['success' => false, 'error' => 'Comment too long (max 1000).'], 400);
        }
        $post = (new PostModel())->findApproved($postId);
        if (!$post) {
            Security::json(['success' => false, 'error' => 'Post not found.'], 404);
        }

        $id = (new CommentModel())->create($postId, Auth::user()['id'], $content);
        Security::json([
            'success' => true,
            'comment' => [
                'id' => $id,
                'user_name' => $displayName,
                'content' => $content,
                'created_at' => date('Y-m-d H:i'),
            ],
        ]);
    }

    public function delete(): void
    {
        Auth::requireGeneralUser();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);
        $comment = (new CommentModel())->find($id);
        if (!$comment || (int) $comment['user_id'] !== Auth::user()['id']) {
            Security::json(['success' => false, 'error' => 'Not allowed.'], 403);
        }
        (new CommentModel())->deleteOwned($id, Auth::user()['id']);
        Security::json(['success' => true]);
    }
}
