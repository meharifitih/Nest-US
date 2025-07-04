<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ __('Welcome to') }} {{ config('app.name') }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #fff;
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #222;
        }
        .container {
            max-width: 480px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        .logo {
            display: block;
            margin: 0 auto 24px auto;
            height: 40px;
        }
        h1 {
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 20px 0;
            color: #111;
            text-align: center;
        }
        p {
            font-size: 15px;
            line-height: 1.7;
            margin: 0 0 18px 0;
            color: #333;
        }
        .button {
            display: inline-block;
            background: #2563eb;
            color: #fff !important;
            text-decoration: none;
            padding: 12px 28px;
            font-size: 15px;
            font-weight: 500;
            border-radius: 4px;
            margin: 24px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            color: #aaa;
            font-size: 13px;
            margin-top: 32px;
        }
        @media (max-width: 600px) {
            .container { padding: 16px 4px; }
            h1 { font-size: 18px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('images/logo.png') }}" class="logo" alt="{{ config('app.name') }}">
        <h1>{{ __('Welcome to') }} {{ config('app.name') }}</h1>
        <p>{{ __('Hi') }} <strong>{{ $content['name'] ?? 'there' }}</strong>,</p>
        <p>{{ __('Welcome to') }} <strong>{{ $content['company_name'] ?? config('app.name') }}</strong>! We're excited to have you on board.</p>
        <p>{{ __('You can now log in and start using the platform:') }}</p>
        <div style="text-align:center;">
            <a href="{{ $content['url'] ?? config('app.url') }}" class="button" target="_blank">{{ __('Go to Dashboard') }}</a>
        </div>
        <p style="margin-top:32px;">{{ __('If you have any questions, just reply to this email.') }}</p>
        <div class="footer">&copy; {{ date('Y') }} {{ config('app.name') }}</div>
    </div>
</body>
</html> 