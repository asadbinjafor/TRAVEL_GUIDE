<?php
class ProfileController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function show(): void
    {
        Auth::requireLogin();
        $id = Auth::user()['id'];
        $user = $this->users->findById($id);
        if (!$user) {
            flash('error', 'User not found.');
            redirect('/login');
        }
        view('profile/show', [
            'title' => 'Profile',
            'profile' => $user,
            'errors' => $_SESSION['form_errors'] ?? [],
            'flash' => flash('success'),
        ]);
        unset($_SESSION['form_errors']);
    }

    public function update(): void
    {
        Auth::requireLogin();
        Security::requireCsrfPost();
        $id = Auth::user()['id'];
        $user = $this->users->findById($id);
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $current = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirm'] ?? '';
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email required.';
        }
        $existing = $this->users->findByEmail($email);
        if ($existing && (int) $existing['id'] !== $id) {
            $errors['email'] = 'Email already in use.';
        }

        $picture = $user['profile_picture'] ?? null;
        if (!empty($_FILES['profile_picture']['name'])) {
            $err = Security::validateImageUpload($_FILES['profile_picture']);
            if ($err) {
                $errors['profile_picture'] = $err;
            } else {
                $saved = Security::saveUpload($_FILES['profile_picture'], PROFILE_UPLOAD_DIR, 'profile_' . $id, 'profiles');
                if ($saved) {
                    $picture = $saved;
                } else {
                    $errors['profile_picture'] = 'Could not save image.';
                }
            }
        }

        if ($newPass !== '' || $confirm !== '') {
            if (!password_verify($current, $user['password_hash'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            } elseif (strlen($newPass) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters.';
            } elseif ($newPass !== $confirm) {
                $errors['new_password_confirm'] = 'Passwords do not match.';
            }
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            redirect('/profile');
        }

        $oldPicture = $user['profile_picture'] ?? null;
        $this->users->updateProfile($id, $name, $email, $picture !== $oldPicture ? $picture : null);
        if ($newPass !== '') {
            $this->users->updatePassword($id, password_hash($newPass, PASSWORD_DEFAULT));
        }

        $_SESSION['name'] = $name;
        flash('success', 'Profile updated successfully.');
        redirect('/profile');
    }
}
