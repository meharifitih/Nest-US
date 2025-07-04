<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ __('New Enterprise Package Inquiry') }} - {{ config('app.name') }}</title>
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
        <h1>{{ __('New Enterprise Package Inquiry') }}</h1>
        <div class="info-box">
            <div class="row"><span class="label">{{ __('Name:') }}</span> <span class="value">{{ $data['name'] ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">{{ __('Email:') }}</span> <span class="value">{{ $data['email'] ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">{{ __('Number of Units:') }}</span> <span class="value">{{ $data['units'] ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">{{ __('Number of Properties:') }}</span> <span class="value">{{ $data['properties'] ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">{{ __('Interval:') }}</span> <span class="value">{{ $data['interval'] ?? 'N/A' }}</span></div>
            @if(!empty($data['message']))
            <div class="row"><span class="label">{{ __('Message:') }}</span> <span class="value">{{ $data['message'] }}</span></div>
            @endif
        </div>
        <div class="footer">&copy; {{ date('Y') }} {{ config('app.name') }}</div>
    </div>
</body>
</html> 