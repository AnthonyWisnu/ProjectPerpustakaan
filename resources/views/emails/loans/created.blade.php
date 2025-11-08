<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Loan Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #dcfce7; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #16a34a; margin-top: 0;">Book Successfully Borrowed!</h1>
        <p style="font-size: 16px; margin-bottom: 0;">Your book loan has been confirmed. Happy reading!</p>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #1f2937; font-size: 18px; margin-top: 0;">Loan Details</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Loan Code:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-family: monospace;">{{ $loan->loan_code }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Borrowed At:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">{{ $loan->borrowed_at->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Due Date:</td>
                <td style="padding: 8px 0; color: #dc2626; font-weight: bold;">{{ $loan->due_date->format('d M Y') }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #1f2937; font-size: 18px; margin-top: 0;">Book Information</h2>

        <div style="padding: 15px; background-color: #f9fafb; border-radius: 4px;">
            <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1f2937;">{{ $loan->book->title }}</h3>
            <p style="margin: 0 0 5px 0; color: #6b7280; font-size: 15px;">by {{ $loan->book->author }}</p>
            @if($loan->book->publisher)
                <p style="margin: 0; color: #9ca3af; font-size: 13px;">Published by {{ $loan->book->publisher }}</p>
            @endif
            @if($loan->book->isbn)
                <p style="margin: 5px 0 0 0; color: #9ca3af; font-size: 13px;">ISBN: {{ $loan->book->isbn }}</p>
            @endif
        </div>
    </div>

    <div style="background-color: #dbeafe; padding: 15px; border-left: 4px solid #3b82f6; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0 0 10px 0; color: #1e3a8a; font-size: 14px;">
            <strong>Important Reminders:</strong>
        </p>
        <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
            <li>Please return the book by <strong>{{ $loan->due_date->format('d M Y') }}</strong></li>
            <li>You will receive a reminder email 3 days before the due date</li>
            <li>Late returns are subject to fines (Rp 1,000/day)</li>
            <li>You can extend your loan once if needed (before due date)</li>
            <li>Please keep the book in good condition</li>
        </ul>
    </div>

    <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0; color: #92400e; font-size: 14px;">
            <strong>Note:</strong> This book must be returned by {{ $loan->due_date->format('l, d F Y') }}. Late fees will apply if not returned on time.
        </p>
    </div>

    <div style="text-align: center; color: #6b7280; font-size: 14px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated message from the Library Management System.</p>
        <p>If you have any questions, please contact our library staff.</p>
    </div>
</body>
</html>
