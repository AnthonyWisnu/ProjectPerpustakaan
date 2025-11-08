<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Due Date Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fef3c7; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #f59e0b; margin-top: 0;">
            @if($daysUntilDue === 1)
                Book Due Tomorrow!
            @else
                Book Due in {{ $daysUntilDue }} Days
            @endif
        </h1>
        <p style="font-size: 16px; margin-bottom: 0;">This is a friendly reminder that your borrowed book is due soon.</p>
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
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">{{ $loan->borrowed_at->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Due Date:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; color: #dc2626; font-weight: bold;">{{ $loan->due_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Days Until Due:</td>
                <td style="padding: 8px 0; color: #f59e0b; font-weight: bold;">{{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'day' : 'days' }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #1f2937; font-size: 18px; margin-top: 0;">Book Information</h2>

        <div style="padding: 15px; background-color: #f9fafb; border-radius: 4px;">
            <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1f2937;">{{ $loan->book->title }}</h3>
            <p style="margin: 0; color: #6b7280; font-size: 15px;">by {{ $loan->book->author }}</p>
        </div>
    </div>

    <div style="background-color: #dbeafe; padding: 15px; border-left: 4px solid #3b82f6; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0 0 10px 0; color: #1e3a8a; font-size: 14px;">
            <strong>What you can do:</strong>
        </p>
        <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
            <li>Return the book before {{ $loan->due_date->format('d M Y') }} to avoid fines</li>
            @if($loan->canBeExtended())
                <li>Request a loan extension through your member dashboard (one-time only)</li>
            @endif
            <li>Drop off books at the library circulation desk during opening hours</li>
        </ul>
    </div>

    <div style="background-color: #fee2e2; padding: 15px; border-left: 4px solid #ef4444; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0; color: #991b1b; font-size: 14px;">
            <strong>Late Return Policy:</strong> Books returned after the due date will incur a fine of Rp 1,000 per day. Please return your book on time to avoid additional charges.
        </p>
    </div>

    <div style="text-align: center; color: #6b7280; font-size: 14px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated reminder from the Library Management System.</p>
        <p>If you have any questions, please contact our library staff.</p>
    </div>
</body>
</html>
