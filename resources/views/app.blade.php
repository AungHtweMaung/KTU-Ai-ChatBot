<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <style>
            :root[data-bs-theme='light'] {
                color-scheme: light;
                --bs-body-bg: #ffffff;
                --bs-body-color: #1a1a1a;
            }

            :root[data-bs-theme='dark'] {
                color-scheme: dark;
                --bs-body-bg: #0f1419;
                --bs-body-color: #e1e8ed;
            }

            html,
            body {
                background: var(--bs-body-bg, #ffffff);
                color: var(--bs-body-color, #1a1a1a);
            }
        </style>

        <!-- Apply theme before React and CSS load -->
        <script>
            (function () {
                var storedTheme = null;

                try {
                    storedTheme = localStorage.getItem('admin_theme');
                } catch (error) {
                    storedTheme = null;
                }

                var theme = storedTheme === 'dark' || storedTheme === 'light'
                    ? storedTheme
                    : 'light';

                document.documentElement.setAttribute('data-bs-theme', theme);
            })();
        </script>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
