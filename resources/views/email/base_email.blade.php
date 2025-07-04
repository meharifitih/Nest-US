<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ $title ?? config('app.name') }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 32px;
            text-align: center;
        }
        
        .logo {
            height: 48px;
            margin-bottom: 16px;
            filter: brightness(0) invert(1);
        }
        
        .content {
            padding: 48px 32px;
            line-height: 1.6;
            color: #374151;
        }
        
        .content h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 24px;
            text-align: center;
        }
        
        .content h2 {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
        }
        
        .content h3 {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
        }
        
        .content p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #6b7280;
        }
        
        .content strong {
            color: #111827;
            font-weight: 600;
        }
        
        .content a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .content a:hover {
            text-decoration: underline;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 4px solid #667eea;
            padding: 24px;
            border-radius: 8px;
            margin: 24px 0;
        }
        
        .highlight-box p {
            color: #1e40af;
            font-weight: 500;
            margin-bottom: 12px;
        }
        
        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }
        
        .info-box h3 {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 500;
            color: #374151;
        }
        
        .detail-value {
            font-weight: 600;
            color: #111827;
            font-family: 'Courier New', monospace;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            margin: 24px 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .button-container {
            text-align: center;
            margin: 32px 0;
        }
        
        .url-link {
            background: #f3f4f6;
            padding: 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #374151;
            word-break: break-all;
            margin: 16px 0;
        }
        
        .footer {
            background: #f9fafb;
            padding: 24px 32px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer p {
            font-size: 14px;
            color: #9ca3af;
            margin: 0;
        }
        
        .signature {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        
        .signature p {
            margin: 4px 0;
            color: #6b7280;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-weight: 500;
        }
        
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }
        
        .alert-info {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }
        
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-container {
                border-radius: 12px;
            }
            
            .header {
                padding: 32px 24px;
            }
            
            .content {
                padding: 32px 24px;
            }
            
            .footer {
                padding: 20px 24px;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            
            .button {
                padding: 14px 24px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ $logo ?? asset('images/logo.png') }}" class="logo" alt="{{ config('app.name') }}">
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html> 