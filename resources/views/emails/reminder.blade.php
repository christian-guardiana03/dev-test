<p>Dear {{ $event['attendee_name'] }},</p>

<p>This is a reminder that the {{ $event['event_name'] }} will start in 1 hour.</p>

<h4><strong>Event Details:</h4>
<p><strong>Event:</strong> {{ $event['event_name'] }}</p>
<p><strong>Start Time:</strong> {{ Carbon\Carbon::parse($event['startDateTime'])->format('F d, Y h:i a') }}</p>
<p><strong>End Time:</strong> {{ Carbon\Carbon::parse($event['endDateTime'])->format('F d, Y h:i a') }}</p>

<p>Please let me know if you can attend.</p>

<p>Best regards,</p>
<p>Christian Rey</p>