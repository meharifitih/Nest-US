<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ __('Verify Your Email Address') }}</title>
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
        <img src="{{ asset(Storage::url('upload/logo/')) . '/logo.png' }}" class="logo" alt="{{ env('APP_NAME') }}">
        <h1>{{ __('Verify Your Email Address') }}</h1>
        <p>{{ __('Dear') }} <strong>{{ $data['name'] }}</strong>,</p>
        <p>{{ __('Thank you for signing up with') }} <strong>{{ env('APP_NAME') }}</strong>. {{ __('To complete your registration, please confirm your email address by clicking the button below:') }}</p>
        <div style="text-align:center;">
            <a href="{{ $data['url'] }}" class="button" target="_blank">{{ __('Verify Email Address') }}</a>
        </div>
        <p>{{ __('If the button above doesn\'t work, copy and paste this URL into your web browser:') }}</p>
        <p><a href="{{ $data['url'] }}">{{ $data['url'] }}</a></p>
        <p><strong>{{ __('Important:') }}</strong> {{ __('This link will expire in 60 minutes.') }}</p>
        <p>{{ __('If you didn\'t create this account, you can safely ignore this email.') }}</p>
        <div class="footer">&copy; {{ date('Y') }} {{ env('APP_NAME') }}</div>
    </div>
</body>
</html>
