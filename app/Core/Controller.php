<?php
declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    protected function view(string $viewPath, array $data = [], ?string $layout = 'layouts/app'): void
    {
        $data['config'] = $this->config;
        $data['currentUser'] = $this->currentUser();
        $data['flash'] = $this->getFlash();

        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../Views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewPath}");
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../Views/' . $layout . '.php';
        require $layoutFile;
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function requireAuth(): array
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->redirect('/login');
        }
        return $user;
    }

    protected function requireRole(string $role): array
    {
        $user = $this->requireAuth();
        if ($user['role'] !== $role) {
            $target = $user['role'] === 'admin' ? '/admin' : '/staff';
            $this->redirect($target);
        }
        return $user;
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
}
