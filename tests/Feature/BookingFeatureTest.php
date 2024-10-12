<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class BookingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanCreateBooking()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($user)->post('/events/' . $event->id . '/book', [
            'event_id' => $event->id,
            'booking_time' => Carbon::parse('2024-10-14 08:00')->format('H:i'),
            'booking_date' => Carbon::parse('2024-10-14 08:30')->format('Y-m-d'),
            'attendee_email' => 'test@test.com',
            'attendee_name' => 'Best Tester',
            'booking_timezone' => 'Asia/Manila'
        ]);

        $response->assertSee('Thank You!');
        $response->assertSee($event->name);
    }

    public function testBookingCollisionDetection() {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $now = Carbon::now();
        $futureDate = $now->addDays(rand(1, 30)); // Adjust the range as needed
        
        while ($futureDate->isWeekend()) {
            $futureDate = $futureDate->addDay();
        }

        // Create a conflicting booking within the event time
        Booking::factory()->create([
            'event_id' => $event->id,
            'booking_time' => Carbon::parse('2024-10-14 08:00:00')->format('H:i'), 
            'booking_date' => $futureDate->format('Y-m-d'),
            'booking_timezone' => 'Asia/Manila'
        ]);

        $response = $this->actingAs($user)->post('/events/' . $event->id . '/book', [
            'event_id' => $event->id,
            'booking_time' => Carbon::parse('2024-10-14 08:00')->format('H:i'),
            'booking_date' => $futureDate->format('Y-m-d'),
            'attendee_email' => 'test@test.com',
            'attendee_name' => 'Best Tester',
            'booking_timezone' => 'Asia/Manila'
        ]);

        $response->assertRedirect(route('bookings.create', $event));

        $redirectedResponse = $this->followRedirects($response);
        $redirectedResponse->assertSee("Select a Time Slot for $event->name");
    }
}
