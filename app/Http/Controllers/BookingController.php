<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use Spatie\GoogleCalendar\Event as GoogleCalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventConfirmationMail;
use Sabre\VObject;
use App\Http\Requests\BookingFormRequest;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_ConferenceData;
use Google_Service_Calendar_CreateConferenceRequest;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_ConferenceSolutionKey;


class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('event')->get();

        return view('bookings.index', compact('bookings'));
    }

    public function store(BookingFormRequest $request, $eventId)
    {  
        $event = Event::findOrFail($eventId);

        // check first if there is a duplicate booking time and date
        $checkBooking = Booking::where([
            'event_id' => $eventId, 
            'booking_date' => $request->input('booking_date'), 
            'booking_time' => $request->input('booking_time'),
            'booking_timezone' => $request->input('booking_timezone')
        ])->first();
        
        if ($checkBooking) {
            return Redirect::route('bookings.create', $eventId)->with(['status' => 'error', 'message' => 'Unfortunately, this time slot is no longer available. Please try a different time slot.']);
        }

        $localTime = Carbon::createFromFormat('Y-m-d H:i', $request->input('booking_date').' '.$request->input('booking_time'), $request->input('booking_timezone'));
        $localTime->setTimezone('Asia/Manila');

        $booking = new Booking();
        $booking->attendee_name = $request->input('attendee_name');
        $booking->attendee_email = $request->input('attendee_email');
        $booking->event_id = $eventId;
        $booking->booking_date = $request->input('booking_date');
        $booking->booking_time = $request->input('booking_time');
        $booking->local_start_time = $localTime;
        $booking->booking_timezone = $request->input('booking_timezone');
        $booking->save();

        $this->createGoogleEvent($request, $booking);

        return view('bookings.thank-you', ['booking' => $booking]);
    }

    public function create(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $selectedDate = $request->input('booking_date', now()->toDateString());

        $timeSlots = $this->generateTimeSlots($selectedDate);

        $timeZones = timezone_identifiers_list();
        $selectedTimeZone = $request->input('timezone', "Asia/Manila");

        return view('bookings.calendar', compact('event', 'timeSlots', 'selectedDate', 'timeZones', 'selectedTimeZone'));
    }

    private function generateTimeSlots($date)
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->startOfDay()->addHours(24);
        $interval = 30; // 30 minutes per time block

        $timeSlots = [];

        while ($startOfDay < $endOfDay) {
            $end = $startOfDay->copy()->addMinutes($interval);

            $timeSlots[] = [
                'time' => $startOfDay->format('H:i'),
            ];

            $startOfDay = $end;
        }

        return $timeSlots;
    }
    
    public function createGoogleEvent($request, $booking): void {

        $googleCalendar = new GoogleCalendarEvent;

        $startTime = Carbon::createFromFormat('Y-m-d H:i', $request->input('booking_date').' '.$request->input('booking_time'), $request->input('booking_timezone'));
        $startTime->setTimezone('Asia/Manila');
        $endTime = (clone $startTime)->addMinutes($booking->event->duration);

        $eventName = $booking->event->name;

        $googleCalendar->name = $eventName;
        $googleCalendar->description = $eventName.' Event';
        $googleCalendar->startDateTime = $startTime;
        $googleCalendar->endDateTime = $endTime;
        $googleCalendar->save();

        // Generate an ICalendar and put it in a file
        $vcalendar = new VObject\Component\VCalendar([
            'VEVENT' => [
                'SUMMARY' => $booking->event->name,
                'DESCRIPTION' => "You are Invited for the $eventName Event.",
                'DTSTART' => $startTime,
                'DTEND'   => $endTime,
                'ORGANIZER' => env('MAIL_FROM_ADDRESS'),
                'ATTENDEE' => "ATTENDEE;CN=".$request->attendee_email.":mailto:" . $request->attendee_email . "\n",
            ]
        ]);
        
        file_put_contents(public_path('attachments/invite.ics'), $vcalendar->serialize());

        Mail::to($request->input('attendee_email'))->send(new EventConfirmationMail($booking));

    }
}
