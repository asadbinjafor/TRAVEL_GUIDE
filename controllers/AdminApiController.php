<?php
class AdminApiController
{
    public function toggleVerify(): void
    {
        Auth::requireAdmin();
        Security::requireCsrfRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int) ($input['user_id'] ?? 0);
        $verified = (int) ($input['is_verified'] ?? 0) ? 1 : 0;
        $currentId = Auth::user()['id'];
        $target = (new UserModel())->findById($id);
        if (!$target) {
            Security::json(['success' => false, 'error' => 'User not found.'], 404);
        }
        if ($id === $currentId && !$verified) {
            Security::json(['success' => false, 'error' => 'You cannot unverify your own account.'], 400);
        }
        if ($target['role'] === 'admin' && !$verified && (new UserModel())->countVerifiedAdmins() <= 1 && $target['is_verified']) {
            Security::json(['success' => false, 'error' => 'Cannot unverify the last verified admin.'], 400);
        }
        (new UserModel())->setVerified($id, $verified);
        if ($id === $currentId) {
            $_SESSION['is_verified'] = $verified;
        }
        Security::json(['success' => true, 'is_verified' => $verified]);
    }

    public function approveRequest(): void
    {
        Auth::requireAdmin();
        Security::requireCsrfRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int) ($input['request_id'] ?? 0);
        $reqModel = new PostRequestModel();
        $req = $reqModel->findAny($id);
        if (!$req || $req['status'] !== 'pending') {
            Security::json(['success' => false, 'error' => 'Request not found.'], 404);
        }
        $database = db();
        $database->beginTransaction();
        try {
            $d = $req['post_data'];
            $postModel = new PostModel();
            if (!empty($req['original_post_id'])) {
                $postModel->update((int) $req['original_post_id'], array_merge($d, [
                    'image_paths_json' => isset($d['image_paths']) ? json_encode($d['image_paths']) : null,
                ]));
                $postId = (int) $req['original_post_id'];
            } else {
                $postId = $postModel->createFromData((int) $req['scout_id'], $d, 'approved');
            }
            (new CostEstimateModel())->upsertForPost($postId, baseCostFromLevel($d['cost_level']));
            $reqModel->deleteById($id);
            $database->commit();
        } catch (Throwable $exception) {
            $database->rollBack();
            throw $exception;
        }
        Security::json(['success' => true, 'post_id' => $postId]);
    }

    public function rejectRequest(): void
    {
        Auth::requireAdmin();
        Security::requireCsrfRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int) ($input['request_id'] ?? 0);
        $reason = trim($input['reason'] ?? 'Rejected by admin');
        $reqModel = new PostRequestModel();
        if (!$reqModel->setStatus($id, 'rejected', $reason)) {
            Security::json(['success' => false, 'error' => 'Failed.'], 400);
        }
        Security::json(['success' => true]);
    }

    public function deletePost(): void
    {
        Auth::requireAdmin();
        Security::requireCsrfRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int) ($input['post_id'] ?? 0);
        (new PostModel())->delete($id);
        Security::json(['success' => true]);
    }

    public function deleteComment(): void
    {
        Auth::requireAdmin();
        Security::requireCsrfRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int) ($input['comment_id'] ?? 0);
        (new CommentModel())->delete($id);
        Security::json(['success' => true]);
    }

    public function deleteUser(): void
    {
        Auth::requireAdmin();
        Security::requireCsrfRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int) ($input['user_id'] ?? 0);
        if ($id === Auth::user()['id']) {
            Security::json(['success' => false, 'error' => 'Cannot delete yourself.'], 400);
        }
        $target = (new UserModel())->findById($id);
        if ($target && $target['role'] === 'admin') {
            Security::json(['success' => false, 'error' => 'Admin accounts cannot be deleted here. Unverify or contact support.'], 400);
        }
        (new UserModel())->deleteUser($id);
        Security::json(['success' => true]);
    }
}
