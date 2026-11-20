<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // GET /auth/register -> show sign-up form (this IS "subscribing" for now)
    public function register(): void
    {
        $this->view('auth/register', ['title' => 'Subscribe']);
    }

    // POST /auth/store -> create the account
    public function store(): void
    {
        verify_csrf();

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            die('All fields are required.');
        }

        if (strlen($password) < 8) {
            die('Password must be at least 8 characters.');
        }

        if ($this->userModel->findByEmail($email)) {
            die('An account with that email already exists. <a href="' . BASE_URL . 'auth/login">Log in instead?</a>');
        }

        if ($this->userModel->findByUsername($username)) {
            die('That username is already taken. <a href="' . BASE_URL . 'auth/register">Try again</a>');
        }

        $userId = $this->userModel->create($username, $email, $password);

        // log them straight in
        $_SESSION['user_id']  = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role']     = 'subscriber';

        $this->redirect('');
    }

    // GET /auth/login -> show login form
    public function login(): void
    {
        $this->view('auth/login', ['title' => 'Log in']);
    }

    // POST /auth/authenticate -> check credentials
    public function authenticate(): void
    {
        verify_csrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            die('Invalid email or password.');
        }

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        $this->redirect('');
    }

    // GET /auth/logout
    public function logout(): void
    {
        session_destroy();
        $this->redirect('');
    }
}
