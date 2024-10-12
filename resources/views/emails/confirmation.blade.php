<p>Dear {{ $event['attendee_name'] }},</p>

<p>You are invited to the following event:</p>

<p><strong>Title:</strong> {{ $event['event_name'] }}</p>
<p><strong>Start Time:</strong> {{ Carbon\Carbon::parse($event['startDateTime'])->format('F d, Y h:i a') }}</p>
<p><strong>End Time:</strong> {{ Carbon\Carbon::parse($event['endDateTime'])->format('F d, Y h:i a') }}</p>

<p>Please let me know if you can attend.</p>

<p>Best regards,</p>
<p>Christian Rey</p>