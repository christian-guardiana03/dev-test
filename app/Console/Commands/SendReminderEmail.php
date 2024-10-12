<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
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
        $currentAddOneHour = Carbon::now()->addHour()->setTimezone('Asia/Manila')->format('H:i');

        $bookings = Booking::where('local_start_time', '=', $currentAddOneHour)->get();
        
        foreach ($bookings as $booking) {

            $recipientEmail = $booking->attendee_email;
            $startTime = Carbon::parse($booking->booking_date.' '.$booking->booking_time);
            $endTime = (clone $startTime)->addMinutes($booking->event->duration);

            // Send the reminder email
            Mail::to($recipientEmail)->send(new EventReminderMail($booking));

            echo "Reminder sent to: $recipientEmail \n";
        }
    }
}
