<?php
class AdminController
{
    public function dashboard(): void
    {
        Auth::requireAdmin();
        $users = new UserModel();
        $posts = new PostModel();
        $requests = new PostRequestModel();
        $comments = new CommentModel();
        view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'roleCounts' => $users->countByRole(),
            'pendingRequests' => $requests->countPending(),
            'totalPosts' => $posts->countAll(),
            'totalComments' => $comments->countAll(),
        ]);
    }

    public function users(): void
    {
        Auth::requireAdmin();
        view('admin/users', [
            'title' => 'User Management',
            'users' => (new UserModel())->allForManagement(),
            'flash' => flash('success'),
            'extraScripts' => ['admin.js'],
        ]);
    }

    public function addUserForm(): void
    {
        Auth::requireAdmin();
        view('admin/user_form', [
            'title' => 'Add User',
            'errors' => $_SESSION['form_errors'] ?? [],
            'old' => $_SESSION['form_old'] ?? [],
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    public function addUser(): void
    {
        Auth::requireAdmin();
        Security::requireCsrfPost();
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $verified = !empty($_POST['is_verified']) ? 1 : 0;
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email required.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Password min 8 chars.';
        }
        if ((new UserModel())->findByEmail($email)) {
            $errors['email'] = 'Email exists.';
        }
        if (!in_array($role, ['admin', 'scout', 'user'], true)) {
            $errors['role'] = 'Invalid role.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = compact('name', 'email', 'role');
            redirect('/admin/users/add');
        }

        try {
            (new UserModel())->create([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'is_verified' => $verified,
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23505') {
                throw $exception;
            }
            $_SESSION['form_errors'] = ['email' => 'Email exists.'];
            $_SESSION['form_old'] = compact('name', 'email', 'role');
            redirect('/admin/users/add');
        }
        flash('success', 'User created.');
        redirect('/admin/users');
    }

    public function posts(): void
    {
        Auth::requireAdmin();
        view('admin/posts', [
            'title' => 'Post Moderation',
            'pending' => (new PostRequestModel())->pendingAll(),
            'posts' => (new PostModel())->allForAdmin(),
            'extraScripts' => ['admin.js'],
        ]);
    }

    public function editPostForm(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $post = (new PostModel())->findById($id);
        if (!$post) {
            redirect('/admin/posts');
        }
        view('admin/post_edit', [
            'title' => 'Edit Post',
            'post' => $post,
            'errors' => $_SESSION['form_errors'] ?? [],
        ]);
        unset($_SESSION['form_errors']);
    }

    public function updatePost(): void
    {
        Auth::requireAdmin();
        Security::requireCsrfPost();
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'short_history' => trim($_POST['short_history'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'genre' => $_POST['genre'] ?? '',
            'cost_level' => $_POST['cost_level'] ?? '',
            'travel_medium_info' => trim($_POST['travel_medium_info'] ?? ''),
        ];
        $errors = [];
        foreach (['title', 'short_history', 'country', 'travel_medium_info'] as $field) {
            if ($data[$field] === '') {
                $errors[$field] = 'This field is required.';
            }
        }
        if (!in_array($data['genre'], GENRES, true)) {
            $errors['genre'] = 'Invalid genre.';
        }
        if (!in_array($data['cost_level'], ['low', 'medium', 'high'], true)) {
            $errors['cost_level'] = 'Invalid cost level.';
        }
        if (!(new PostModel())->findById($id)) {
            $errors['post'] = 'Post not found.';
        }
        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            redirect('/admin/posts/edit', ['id' => $id]);
        }
        (new PostModel())->update($id, $data);
        (new CostEstimateModel())->upsertForPost($id, baseCostFromLevel($data['cost_level']));
        flash('success', 'Post updated.');
        redirect('/admin/posts');
    }

    public function comments(): void
    {
        Auth::requireAdmin();
        view('admin/comments', [
            'title' => 'Comment Moderation',
            'comments' => (new CommentModel())->allForAdmin(),
            'extraScripts' => ['admin.js'],
        ]);
    }
}
