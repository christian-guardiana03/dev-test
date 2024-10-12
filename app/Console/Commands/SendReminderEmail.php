<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventReminderMail;
use Carbon\Carbon;

class SendReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reminder-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send an email to the ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::whereHas('bookings', function($query){
                    $query->where('local_start_time', '=', Carbon::now()->addHour()->setTimezone('Asia/Manila')->format('H:i'));
                })->with(['bookings' => function ($query) {
                    $query->where('local_start_time', '=', Carbon::now()->addHour()->setTimezone('Asia/Manila')->format('H:i'));
                }])->get();
       
        foreach ($events as $event) {

            foreach ($event->bookings as $booking) {
                $recipientEmail = $booking->attendee_email;
                $startTime = Carbon::parse($booking->booking_date.' '.$booking->booking_time);
                $endTime = (clone $startTime)->addMinutes($event->duration);

                $eventDetails = [
                    'attendee_name' => $booking->attendee_name,
                    'event_name' => $event->name, 
                    'startDateTime' => $startTime->toDateTimeString(),
                    'endDateTime' => $endTime->toDateTimeString()
                ];

                // Send the reminder email
                Mail::to($recipientEmail)->send(new EventReminderMail($eventDetails));

                echo "Reminder sent to: $recipientEmail \n";
            }
        }
    }
}
