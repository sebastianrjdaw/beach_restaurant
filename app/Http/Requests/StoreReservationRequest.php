<?php

namespace App\Http\Requests;

use App\Models\RestaurantSetting;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'party_size' => ['required', 'integer', 'min:1', 'max:20'],
            'preferred_area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'locale' => ['required', 'in:es,en'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $settings = RestaurantSetting::query()->first();
                $maxDays = (int) ($settings?->max_days_in_advance ?? 30);
                $timezone = $settings?->timezone ?? 'Europe/Madrid';
                $minGuests = (int) ($settings?->min_guests_online ?? 1);
                $maxGuests = (int) ($settings?->max_guests_online ?? 10);
                $date = $this->input('reservation_date');
                $startTime = $this->input('start_time');
                $partySize = (int) $this->input('party_size', 0);

                if (! $date) {
                    return;
                }

                $maxDate = CarbonImmutable::today($timezone)->addDays($maxDays);

                if (CarbonImmutable::parse($date, $timezone)->greaterThan($maxDate)) {
                    $validator->errors()->add(
                        'reservation_date',
                        "Solo se aceptan reservas con {$maxDays} dias de antelacion.",
                    );
                }

                if (! $startTime) {
                    return;
                }

                $startsAt = CarbonImmutable::parse($date.' '.$startTime, $timezone);

                if ($startsAt->lessThanOrEqualTo(CarbonImmutable::now($timezone))) {
                    $validator->errors()->add('start_time', 'No se puede reservar una hora ya pasada.');
                }

                if ($partySize < $minGuests || $partySize > $maxGuests) {
                    $validator->errors()->add(
                        'party_size',
                        "Las reservas online deben ser de {$minGuests} a {$maxGuests} personas.",
                    );
                }
            },
        ];
    }
}
