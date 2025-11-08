<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Books Are Ready!</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #dcfce7; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #16a34a; margin-top: 0;">Your Books Are Ready for Pickup!</h1>
        <p style="font-size: 16px; margin-bottom: 0;">Great news! Your reserved books are now ready to be picked up.</p>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #1f2937; font-size: 18px; margin-top: 0;">Reservation Details</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Reservation Code:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-family: monospace;">{{ $reservation->reservation_code }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Expires At:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; color: #dc2626;">{{ $reservation->expired_at->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Total Books:</td>
                <td style="padding: 8px 0;">{{ $reservation->total_books }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #1f2937; font-size: 18px; margin-top: 0;">Books to Pick Up</h2>

        @foreach($reservation->items as $item)
            <div style="padding: 12px; background-color: #f9fafb; border-radius: 4px; margin-bottom: 10px;">
                <h3 style="margin: 0 0 5px 0; font-size: 16px; color: #1f2937;">{{ $item->book->title }}</h3>
                <p style="margin: 0; color: #6b7280; font-size: 14px;">by {{ $item->book->author }}</p>
                @if($item->book->shelf_location)
                    <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 13px;">Shelf Location: {{ $item->book->shelf_location }}</p>
                @endif
            </div>
        @endforeach
    </div>

    <div style="background-color: #dbeafe; padding: 15px; border-left: 4px solid #3b82f6; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0 0 10px 0; color: #1e3a8a; font-size: 14px;">
            <strong>What to do next:</strong>
        </p>
        <ol style="margin: 0; padding-left: 20px; color: #1e40af;">
            <li>Come to the library during opening hours</li>
            <li>Bring your library card or ID</li>
            <li>Show your reservation code: <strong>{{ $reservation->reservation_code }}</strong></li>
            <li>Collect your books from the circulation desk</li>
        </ol>
    </div>

    <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0; color: #92400e; font-size: 14px;">
            <strong>Reminder:</strong> Please pick up your books before {{ $reservation->expired_at->format('d M Y, H:i') }}. After this time, your reservation will be cancelled automatically.
        </p>
    </div>

    <div style="text-align: center; color: #6b7280; font-size: 14px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated message from the Library Management System.</p>
        <p>If you have any questions, please contact our library staff.</p>
    </div>
</body>
</html>
