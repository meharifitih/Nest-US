@component('mail::message')
# Welcome to {{ config('app.name') }}

Hi {{ $content['name'] ?? 'there' }},

Welcome to {{ $content['company_name'] ?? config('app.name') }}! We're excited to have you on board.

You can now log in and start using the platform:

@component('mail::button', ['url' => $content['url'] ?? config('app.url')])
Go to Dashboard
@endcomponent

If you have any questions, just reply to this email.

Thanks,<br>
The {{ $content['company_name'] ?? config('app.name') }} Team
@endcomponent 