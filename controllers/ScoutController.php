<?php
class ScoutController
{
    private PostRequestModel $requests;
    private PostModel $posts;

    public function __construct()
    {
        $this->requests = new PostRequestModel();
        $this->posts = new PostModel();
    }

    public function requests(): void
    {
        Auth::requireScout();
        $list = $this->requests->byScout(Auth::user()['id']);
        view('scout/requests', [
            'title' => 'My Requests',
            'requests' => $list,
            'extraScripts' => ['scout.js'],
        ]);
    }

    public function createForm(): void
    {
        Auth::requireScout();
        view('scout/form', [
            'title' => 'New Post Request',
            'request' => null,
            'errors' => $_SESSION['form_errors'] ?? [],
            'old' => $_SESSION['form_old'] ?? [],
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    public function editForm(): void
    {
        Auth::requireScout();
        $id = (int) ($_GET['id'] ?? 0);
        $req = $this->requests->find($id, Auth::user()['id']);
        if (!$req || $req['status'] !== 'pending') {
            flash('error', 'Cannot edit this request.');
            redirect('/scout/requests');
        }
        view('scout/form', [
            'title' => 'Edit Request',
            'request' => $req,
            'errors' => $_SESSION['form_errors'] ?? [],
            'old' => $req['post_data'],
        ]);
        unset($_SESSION['form_errors']);
    }

    public function changeForm(): void
    {
        Auth::requireScout();
        $postId = (int) ($_GET['post_id'] ?? 0);
        $post = $this->posts->findById($postId);
        if (!$post || (int) $post['scout_id'] !== Auth::user()['id']) {
            flash('error', 'Invalid post.');
            redirect('/scout/approved');
        }
        $old = [
            'title' => $post['title'],
            'short_history' => $post['short_history'],
            'country' => $post['country'],
            'genre' => $post['genre'],
            'cost_level' => $post['cost_level'],
            'travel_medium_info' => $post['travel_medium_info'],
        ];
        view('scout/form', [
            'title' => 'Request Changes',
            'request' => null,
            'original_post_id' => $postId,
            'errors' => $_SESSION['form_errors'] ?? [],
            'old' => $_SESSION['form_old'] ?? $old,
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    public function approved(): void
    {
        Auth::requireScout();
        $posts = $this->posts->byScout(Auth::user()['id']);
        view('scout/approved', ['title' => 'Approved Posts', 'posts' => $posts]);
    }

    public function store(): void
    {
        Auth::requireScout();
        Security::requireCsrfPost();
        $originalId = !empty($_POST['original_post_id']) ? (int) $_POST['original_post_id'] : null;
        if ($originalId !== null) {
            $original = $this->posts->findById($originalId);
            if (!$original || (int) $original['scout_id'] !== Auth::user()['id']) {
                flash('error', 'Invalid post change request.');
                redirect('/scout/approved');
            }
        }
        $data = $this->collectPostData();
        if (!empty($data['errors'])) {
            $_SESSION['form_errors'] = $data['errors'];
            $_SESSION['form_old'] = $data['fields'];
            if ($originalId !== null) {
                redirect('/scout/change-request', ['post_id' => $originalId]);
            }
            redirect('/scout/request/create');
        }
        $this->requests->create(Auth::user()['id'], $data['fields'], $originalId);
        flash('success', 'Request submitted for admin review.');
        redirect('/scout/requests');
    }

    public function update(): void
    {
        Auth::requireScout();
        Security::requireCsrfPost();
        $id = (int) ($_POST['id'] ?? 0);
        $data = $this->collectPostData();
        if (!empty($data['errors'])) {
            $_SESSION['form_errors'] = $data['errors'];
            redirect('/scout/request/edit', ['id' => $id]);
        }
        $existing = $this->requests->find($id, Auth::user()['id']);
        if ($existing && !array_key_exists('image_paths', $data['fields']) && !empty($existing['post_data']['image_paths'])) {
            $data['fields']['image_paths'] = $existing['post_data']['image_paths'];
        }
        if (!$existing || !$this->requests->update($id, Auth::user()['id'], $data['fields'])) {
            flash('error', 'Could not update request.');
        } else {
            flash('success', 'Request updated.');
        }
        redirect('/scout/requests');
    }

    private function collectPostData(): array
    {
        $fields = [
            'title' => trim($_POST['title'] ?? ''),
            'short_history' => trim($_POST['short_history'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'genre' => $_POST['genre'] ?? '',
            'cost_level' => $_POST['cost_level'] ?? '',
            'travel_medium_info' => trim($_POST['travel_medium_info'] ?? ''),
        ];
        $errors = [];
        foreach (['title', 'short_history', 'country', 'travel_medium_info'] as $f) {
            if ($fields[$f] === '') {
                $errors[$f] = 'This field is required.';
            }
        }
        if (!in_array($fields['genre'], GENRES, true)) {
            $errors['genre'] = 'Select a valid genre.';
        }
        if (!in_array($fields['cost_level'], ['low', 'medium', 'high'], true)) {
            $errors['cost_level'] = 'Select cost level.';
        }

        if (!empty($_FILES['images']['name'][0])) {
            $paths = [];
            $count = count($_FILES['images']['name']);
            if ($count > MAX_POST_IMAGES) {
                $errors['images'] = 'Upload at most ' . MAX_POST_IMAGES . ' images.';
                return ['fields' => $fields, 'errors' => $errors];
            }
            $files = [];
            for ($i = 0; $i < $count; $i++) {
                $file = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i],
                ];
                if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $err = Security::validateImageUpload($file);
                if ($err) {
                    $errors['images'] = $err;
                    break;
                }
                $files[] = $file;
            }
            if (!isset($errors['images'])) {
                foreach ($files as $file) {
                    $saved = Security::saveUpload($file, POST_UPLOAD_DIR, 'post', 'posts');
                    if (!$saved) {
                        $errors['images'] = 'Could not save image.';
                        break;
                    }
                    $paths[] = $saved;
                }
            }
            if (!isset($errors['images'])) {
                $fields['image_paths'] = $paths;
            }
        }

        return ['fields' => $fields, 'errors' => $errors];
    }
}
