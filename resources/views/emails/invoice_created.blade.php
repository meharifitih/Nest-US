<!DOCTYPE html>
<html>
<head>
    <title>New Rent Invoice Generated</title>
</head>
<body>
    <h2>Hello {{ $name }},</h2>
    
    <p>A new rent invoice has been generated for your unit.</p>
    
    <div style="margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h3>Invoice Details:</h3>
        <p><strong>Invoice Number:</strong> {{ $invoice_id }}</p>
        <p><strong>Property:</strong> {{ $property_name }}</p>
        <p><strong>Unit:</strong> {{ $unit_name }}</p>
        <p><strong>Period:</strong> {{ $period }}</p>
        <p><strong>Amount Due:</strong> ${{ number_format($amount, 2) }}</p>
        <p><strong>Due Date:</strong> {{ $due_date }}</p>
    </div>

    <p>Please ensure payment is made by the due date to avoid any late fees.</p>
    
    <p>Thank you,<br>
    Property Management Team</p>
</body>
</html> 