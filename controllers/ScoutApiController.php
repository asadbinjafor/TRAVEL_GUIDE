<?php
class ScoutApiController
{
    public function delete(): void
    {
        Auth::requireScout();
        Security::requireCsrfRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            Security::json(['success' => false, 'error' => 'Invalid request.'], 400);
        }
        $ok = (new PostRequestModel())->delete($id, Auth::user()['id']);
        if (!$ok) {
            Security::json(['success' => false, 'error' => 'Cannot delete. Only pending requests can be removed.'], 400);
        }
        Security::json(['success' => true]);
    }
}
