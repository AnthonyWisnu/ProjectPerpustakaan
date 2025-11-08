<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #2563eb; margin-top: 0;">Reservation Confirmed!</h1>
        <p style="font-size: 16px; margin-bottom: 0;">Thank you for your reservation. Your books are being prepared for pickup.</p>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #1f2937; font-size: 18px; margin-top: 0;">Reservation Details</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Reservation Code:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-family: monospace;">{{ $reservation->reservation_code }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Reserved At:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">{{ $reservation->reserved_at->format('d M Y, H:i') }}</td>
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
        <h2 style="color: #1f2937; font-size: 18px; margin-top: 0;">Reserved Books</h2>

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

    <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; border-radius: 4px; margin-bottom: 20px;">
        <p style="margin: 0; color: #92400e; font-size: 14px;">
            <strong>Important:</strong> Please pick up your books within 24 hours. Your reservation will automatically expire after that.
        </p>
    </div>

    <div style="text-align: center; color: #6b7280; font-size: 14px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated message from the Library Management System.</p>
        <p>If you have any questions, please contact our library staff.</p>
    </div>
</body>
</html>
