<?php
class WishlistApiController
{
    public function add(): void
    {
        Auth::requireGeneralUser();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $postId = (int) ($input['post_id'] ?? 0);
        if ($postId <= 0) {
            Security::json(['success' => false, 'error' => 'Invalid post.'], 400);
        }
        $post = (new PostModel())->findApproved($postId);
        if (!$post) {
            Security::json(['success' => false, 'error' => 'Post not found.'], 404);
        }
        (new WishlistModel())->add(Auth::user()['id'], $postId);
        Security::json(['success' => true, 'message' => 'Added to wishlist.']);
    }

    public function remove(): void
    {
        Auth::requireGeneralUser();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $postId = (int) ($input['post_id'] ?? $_GET['post_id'] ?? 0);
        if ($postId <= 0) {
            Security::json(['success' => false, 'error' => 'Invalid post.'], 400);
        }
        (new WishlistModel())->remove(Auth::user()['id'], $postId);
        Security::json(['success' => true, 'message' => 'Removed from wishlist.']);
    }
}
