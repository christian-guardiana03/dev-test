<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class BookingFormRequest extends FormRequest
{   

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendee_name' => 'required',
            'attendee_email' => 'required',
            'booking_date' => 'required',
            'booking_time' => 'required',
            'booking_timezone' => 'required'
        ];
    }

    public function withValidator(Validator $validator){
        $validator->after(function(Validator $validator){

            // Convert the value to a Carbon instance
            $time = $this->input('booking_time');
            $dateTime = Carbon::parse($this->input('booking_date').' '.$time);
            // Check if the weekday is Monday to Friday
            $isWeekday = $dateTime->isWeekday();

            // Check if the time is within 8 AM to 5 PM
            $startTime = '08:00';
            $endTime = '17:00';
            
            if (!$isWeekday) {
                $validator->errors()->add('booking_date', 'Booking day is only allowed from Monday to Friday.');
            }

            if ($time < $startTime || $time > $endTime) {
                $validator->errors()->add('booking_date', 'Booking time is only allowed from 8 AM to 5 PM.');
            }
        });
    }


}
