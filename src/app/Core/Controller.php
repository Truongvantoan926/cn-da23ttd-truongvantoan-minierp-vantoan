<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use RuntimeException;

abstract class Controller
{
    public function render(string $view, array $data = []): void
    {
        if (!preg_match('/\A[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*\z/', $view)) {
            throw new InvalidArgumentException('Tên view không hợp lệ.');
        }

        $viewsDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views';
        $viewPath = $viewsDirectory
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $view)
            . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException('Không tìm thấy view được yêu cầu.');
        }

        extract($data, EXTR_SKIP);

        foreach (['header', 'sidebar'] as $layout) {
            $layoutPath = $viewsDirectory
                . DIRECTORY_SEPARATOR . 'layouts'
                . DIRECTORY_SEPARATOR . $layout . '.php';

            if (is_file($layoutPath)) {
                require $layoutPath;
            }
        }

        require $viewPath;

        $footerPath = $viewsDirectory
            . DIRECTORY_SEPARATOR . 'layouts'
            . DIRECTORY_SEPARATOR . 'footer.php';

        if (is_file($footerPath)) {
            require $footerPath;
        }
    }

    public function redirect(string $url): void
    {
        if (preg_match('/[\r\n]/', $url)) {
            throw new InvalidArgumentException('URL chuyển hướng không hợp lệ.');
        }

        $parts = parse_url($url);
        if (
            $url === ''
            || $parts === false
            || isset($parts['scheme'])
            || isset($parts['host'])
            || str_starts_with($url, '//')
            || str_contains($url, '\\')
        ) {
            throw new InvalidArgumentException('Chỉ hỗ trợ chuyển hướng nội bộ.');
        }

        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        if (
            $baseUrl !== ''
            && $url !== $baseUrl
            && !str_starts_with($url, $baseUrl . '/')
        ) {
            $url = $baseUrl . '/' . ltrim($url, '/');
        }

        header('Location: ' . $url, true, 302);
        exit;
    }
}
