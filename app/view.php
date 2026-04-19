<?php
declare(strict_types=1);

if (!function_exists('viewRenderLayout')) {
    function viewRenderLayout(string $layout, array $data = []): void
    {
        $layoutPath = __DIR__ . '/views/layouts/' . $layout . '.php';
        if (!is_file($layoutPath)) {
            echo (string) ($data['content'] ?? '');
            return;
        }

        extract($data, EXTR_SKIP);
        require $layoutPath;
    }
}

if (!function_exists('viewBegin')) {
    function viewBegin(string $layout, array $data = []): void
    {
        if (!isset($GLOBALS['__view_stack']) || !is_array($GLOBALS['__view_stack'])) {
            $GLOBALS['__view_stack'] = [];
        }

        $GLOBALS['__view_stack'][] = [
            'layout' => $layout,
            'data' => $data,
        ];

        ob_start();
    }
}

if (!function_exists('viewEnd')) {
    function viewEnd(): void
    {
        $stack = $GLOBALS['__view_stack'] ?? [];
        if (!is_array($stack) || $stack === []) {
            return;
        }

        $view = array_pop($stack);
        $GLOBALS['__view_stack'] = $stack;

        $content = ob_get_clean();
        if ($content === false) {
            $content = '';
        }

        $data = (array) ($view['data'] ?? []);
        $data['content'] = $content;

        viewRenderLayout((string) ($view['layout'] ?? ''), $data);
    }
}
