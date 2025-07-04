<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ __('Welcome to') }} {{ env('APP_NAME') }}</title>
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
        .info-box {
            border: 1px solid #e5e7eb;
            background: #fff;
            padding: 24px;
            margin: 24px 0;
        }
        .info-box .row {
            margin-bottom: 10px;
        }
        .info-box .label {
            font-weight: 500;
            color: #222;
        }
        .info-box .value {
            color: #2563eb;
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
        <img src="{{ asset(Storage::url('upload/logo/')) . '/' . $data['logo'] }}" class="logo" alt="{{ env('APP_NAME') }}">
        <h1>{{ __('Welcome to') }} {{ env('APP_NAME') }}</h1>
        <p>{{ __('Dear') }} <strong>{{ $data['name'] }}</strong>,</p>
        <p>{{ __('We are excited to have you on board and look forward to providing you with an exceptional experience.') }}</p>
        <div class="info-box">
            <div class="row"><span class="label">{{ __('App Link:') }}</span> <span class="value"><a href="{{ $data['url'] }}">{{ $data['url'] }}</a></span></div>
            <div class="row"><span class="label">{{ __('Username:') }}</span> <span class="value">{{ $data['email'] }}</span></div>
            <div class="row"><span class="label">{{ __('Password:') }}</span> <span class="value">{{ $data['password'] }}</span></div>
        </div>
        <div style="text-align:center;">
            <a href="{{ $data['url'] }}" class="button" target="_blank">{{ __('Login to Dashboard') }}</a>
        </div>
        <div class="footer">&copy; {{ date('Y') }} {{ env('APP_NAME') }}</div>
    </div>
</body>
</html>
