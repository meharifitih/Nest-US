<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ __('Test Email') }} - {{ env('APP_NAME') }}</title>
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
        <img src="{{ asset(Storage::url('upload/logo/')) . '/' . $data['company_logo'] }}" class="logo" alt="{{ env('APP_NAME') }}">
        <h1>{{ __('Test Email') }}</h1>
        <p>{{ __('Hello, Sir!') }}</p>
        <p>{{ __('This is a test email from') }} <strong>{{ env('APP_NAME') }}</strong>.</p>
        <p>{{ __('If you received this email, your email configuration is working correctly.') }}</p>
        <p>{{ __('Email sent at:') }} <strong>{{ now()->format('F j, Y \a\t g:i A') }}</strong></p>
        <div class="footer">&copy; {{ date('Y') }} {{ env('APP_NAME') }}</div>
    </div>
</body>
</html>
