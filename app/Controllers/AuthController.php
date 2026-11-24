<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if ($user = $this->currentUser()) {
            $this->redirect($user['role'] === 'admin' ? '/admin' : '/staff');
        }

        $this->view('auth/login', [
            'pageTitle' => 'Sign in — Dispensa',
            'error' => $_SESSION['login_error'] ?? null,
        ], null);
        unset($_SESSION['login_error']);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['login_error'] = 'Please enter both your work email and password.';
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['login_error'] = 'Invalid work email or password.';
            $this->redirect('/login');
        }

        if ($user['status'] === 'suspended') {
            $_SESSION['login_error'] = 'This account has been suspended. Please contact your pharmacy admin.';
            $this->redirect('/login');
        }

        unset($user['password_hash']);
        $_SESSION['user'] = $user;

        $this->redirect($user['role'] === 'admin' ? '/admin' : '/staff');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}
