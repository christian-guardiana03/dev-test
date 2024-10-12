<p>Dear {{ $booking->attendee_name }},</p>

<p>This is a reminder that the {{ $booking->event->name }} will start in 1 hour.</p>

<h4><strong>Event Details:</h4>
<p><strong>Event:</strong> {{ $booking->event->name }}</p>
<p><strong>Start Time:</strong> {{ Carbon\Carbon::parse($booking->booking_date . " " . $booking->booking_time)->format('F d, Y h:i a') }}</p>
<p><strong>End Time:</strong> {{ Carbon\Carbon::parse($booking->booking_date . " " . $booking->booking_time)->addMinutes($booking->event->duration)->format('F d, Y h:i a') }}</p>

<p>Please let me know if you can attend.</p>

<p>Best regards,</p>
<p>Christian Rey</p>