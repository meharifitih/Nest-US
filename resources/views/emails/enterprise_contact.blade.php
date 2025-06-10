<!DOCTYPE html>
<html>
<head>
    <title>New Enterprise Contact Form Submission</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; background: #f9f9f9; border-radius: 8px; }
        h2 { color: #007bff; }
        .info-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .info-table td { padding: 8px 0; }
        .label { font-weight: bold; width: 180px; }
        .value { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Enterprise Contact Request</h2>
        <table class="info-table">
            <tr>
                <td class="label">Name:</td>
                <td class="value">{{ $contact->name }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td class="value">{{ $contact->email }}</td>
            </tr>
            <tr>
                <td class="label">Desired Property Limit:</td>
                <td class="value">{{ $contact->property_limit }}</td>
            </tr>
            <tr>
                <td class="label">Desired Unit Limit:</td>
                <td class="value">{{ $contact->unit_limit }}</td>
            </tr>
            <tr>
                <td class="label">Preferred Interval:</td>
                <td class="value">{{ $contact->interval }}</td>
            </tr>
            @if($contact->message)
            <tr>
                <td class="label">Message:</td>
                <td class="value">{{ $contact->message }}</td>
            </tr>
            @endif
        </table>
    </div>
</body>
</html> 