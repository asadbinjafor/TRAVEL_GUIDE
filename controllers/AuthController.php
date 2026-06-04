<?php
class AuthController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function showLogin(): void
    {
        if (Auth::user()) {
            redirect('/');
        }
        view('auth/login', [
            'title' => 'Login',
            'errors' => $_SESSION['form_errors'] ?? [],
            'old' => $_SESSION['form_old'] ?? [],
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    public function showRegister(): void
    {
        if (Auth::user()) {
            redirect('/');
        }
        view('auth/register', [
            'title' => 'Register',
            'errors' => $_SESSION['form_errors'] ?? [],
            'old' => $_SESSION['form_old'] ?? [],
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    public function login(): void
    {
        Security::requireCsrfPost();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember_me']);
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email required.';
        }
        if ($password === '') {
            $errors['password'] = 'Password required.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = ['email' => $email];
            redirect('/login');
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('error', 'Invalid email or password.');
            redirect('/login');
        }

        if ($user['role'] === 'admin' && !(new UserModel())->hasVerifiedAdmin()) {
            (new UserModel())->setVerified((int) $user['id'], 1);
            $user['is_verified'] = 1;
        }

        Auth::login($user);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->users->setRememberToken((int) $user['id'], $token);
            Auth::setRemember((int) $user['id'], $token);
        }

        if ($user['role'] === 'admin') {
            flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('/admin');
        }
        if (!$user['is_verified']) {
            flash('success', 'Your account is pending admin approval.');
        } else {
            flash('success', 'Welcome back, ' . $user['name'] . '!');
        }
        redirect('/');
    }

    public function register(): void
    {
        Security::requireCsrfPost();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email required.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }
        if (!in_array($role, ['admin', 'scout', 'user'], true)) {
            $errors['role'] = 'Invalid role.';
        }
        if ($this->users->findByEmail($email)) {
            $errors['email'] = 'Email already registered.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = compact('name', 'email', 'role');
            redirect('/register');
        }

        $this->users->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'is_verified' => 0,
        ]);

        flash('success', 'Registration successful. Please login after admin approval.');
        redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'You have been logged out.');
        redirect('/');
    }
}
