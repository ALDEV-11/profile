<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code', 'Error') | Personal Profile</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .error-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1.5rem;
            background-color: var(--bg-main);
            background-image:
                linear-gradient(var(--grid-line) 2px, transparent 2px),
                linear-gradient(90deg, var(--grid-line) 2px, transparent 2px);
            background-size: 48px 48px;
        }

        .error-card {
            width: min(100%, 720px);
            padding: clamp(2rem, 6vw, 5rem);
            text-align: center;
            background: var(--bg-surface);
            border: var(--border-width-lg) solid var(--border-color);
            box-shadow: var(--shadow-offset-lg) var(--shadow-offset-lg) 0 var(--shadow-color);
        }

        .error-code {
            display: inline-block;
            padding: 0.25rem 1rem;
            background: var(--neo-pink);
            color: #000;
            border: var(--border-width-lg) solid var(--border-color);
            box-shadow: var(--shadow-offset) var(--shadow-offset) 0 var(--shadow-color);
            font-size: clamp(4rem, 16vw, 9rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.08em;
        }

        .error-title {
            margin-top: 2.5rem;
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            text-transform: uppercase;
        }

        .error-message {
            max-width: 560px;
            margin: 1rem auto 2rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
        }

        .error-home {
            display: inline-block;
            padding: 0.9rem 1.4rem;
            background: var(--neo-cyan);
            color: #000;
            border: var(--border-width-lg) solid var(--border-color);
            box-shadow: var(--shadow-offset) var(--shadow-offset) 0 var(--shadow-color);
            font-weight: 800;
            text-transform: uppercase;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .error-home:hover {
            transform: translate(3px, 3px);
            box-shadow: 2px 2px 0 var(--shadow-color);
        }
    </style>
</head>
<body>
    <main class="error-page">
        <section class="error-card" role="alert">
            <div class="error-code">@yield('code', '500')</div>
            <h1 class="error-title">@yield('title', 'Something went wrong')</h1>
            <p class="error-message">@yield('message', 'The page could not be loaded right now.')</p>
            <a class="error-home" href="{{ url('/') }}">Back to home</a>
        </section>
    </main>
</body>
</html>
