<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Book - Action Required</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fee2e2; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #dc2626; margin-top: 0;">Overdue Book - Action Required!</h1>
        <p style="font-size: 16px; margin-bottom: 0;">Your borrowed book is now overdue. Please return it as soon as possible.</p>
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
                <td style="padding: 8px 0; font-weight: bold;">Days Overdue:</td>
                <td style="padding: 8px 0; color: #dc2626; font-weight: bold;">{{ $daysOverdue }} {{ $daysOverdue === 1 ? 'day' : 'days' }}</td>
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

    <div style="background-color: #fee2e2; padding: 20px; border: 2px solid #dc2626; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #dc2626; font-size: 18px; margin-top: 0;">Fine Information</h2>

        <p style="margin: 0 0 10px 0; color: #991b1b;">
            <strong>Current Fine:</strong> <span style="font-size: 20px;">Rp {{ number_format($daysOverdue * 1000, 0, ',', '.') }}</span>
        </p>
        <p style="margin: 0; color: #991b1b; font-size: 14px;">
            Fine rate: Rp 1,000 per day
        </p>
    </div>

    <div style="background-color: #dbeafe; padding: 15px; border-left: 4px solid #3b82f6; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0 0 10px 0; color: #1e3a8a; font-size: 14px;">
            <strong>What you need to do:</strong>
        </p>
        <ol style="margin: 0; padding-left: 20px; color: #1e40af;">
            <li>Return the book to the library as soon as possible</li>
            <li>Pay the accumulated fine at the circulation desk</li>
            <li>Fines must be paid before making new reservations</li>
        </ol>
    </div>

    <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0; color: #92400e; font-size: 14px;">
            <strong>Important:</strong> Continued failure to return overdue books may result in the suspension of your library account. Maximum fine is capped at Rp 50,000 per book.
        </p>
    </div>

    <div style="text-align: center; padding: 20px; background-color: #f9fafb; border-radius: 5px; margin-bottom: 20px;">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #6b7280;">Need help or have questions?</p>
        <p style="margin: 0; font-size: 14px; color: #6b7280;">Please contact our library staff during opening hours.</p>
    </div>

    <div style="text-align: center; color: #6b7280; font-size: 14px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated notification from the Library Management System.</p>
    </div>
</body>
</html>
