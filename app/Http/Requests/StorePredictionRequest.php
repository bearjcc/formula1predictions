<?php

namespace App\Http\Requests;

use App\Models\Drivers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePredictionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['race', 'sprint', 'preseason', 'midseason'])],
            'season' => ['required', 'integer', 'min:1950', 'max:2030'],
            'race_round' => ['required_if:type,race,sprint', 'prohibited_if:type,preseason,midseason', 'integer', 'min:1', 'max:25'],
            'race_id' => ['nullable', 'integer', 'exists:races,id'],
            'prediction_data' => ['required', 'array'],
            'prediction_data.driver_order' => ['required_if:type,race,sprint', 'array', 'min:1', 'max:'.config('f1.max_drivers', 22)],
            'prediction_data.driver_order.*' => ['nullable'],
            'prediction_data.fastest_lap' => ['nullable'],
            'prediction_data.dnf_predictions' => ['nullable', 'array'],
            'prediction_data.dnf_predictions.*' => ['string', 'exists:drivers,driver_id'],
            'prediction_data.team_order' => ['required_if:type,preseason,midseason', 'array', 'min:1', 'max:'.config('f1.max_constructors', 11)],
            'prediction_data.team_order.*' => ['required_if:type,preseason,midseason', 'integer', 'exists:teams,id'],
            'prediction_data.driver_championship' => ['required_if:type,midseason', 'array', 'min:1', 'max:'.config('f1.max_drivers', 22)],
            'prediction_data.driver_championship.*' => ['required_if:type,midseason', 'integer', 'exists:drivers,id'],
            'prediction_data.teammate_battles' => ['nullable', 'array'],
            'prediction_data.teammate_battles.*' => ['required', 'integer', 'exists:drivers,id'],
            'prediction_data.red_flags' => ['nullable', 'integer', 'min:0'],
            'prediction_data.safety_cars' => ['nullable', 'integer', 'min:0'],
            'prediction_data.superlatives' => ['nullable', 'array'],
            'prediction_data.superlatives.*' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('type');
            if (in_array($type, ['race', 'sprint'], true)) {
                $driverOrder = $this->input('prediction_data.driver_order', []);
                if (is_array($driverOrder)) {
                    foreach ($driverOrder as $value) {
                        if ($value === null || $value === '') {
                            continue;
                        }
                        $exists = is_numeric($value)
                            ? Drivers::where('id', (int) $value)->exists()
                            : Drivers::where('driver_id', (string) $value)->exists();
                        if (! $exists) {
                            $validator->errors()->add(
                                'prediction_data.driver_order',
                                'The selected driver is invalid.'
                            );

                            break;
                        }
                    }
                }

                $fastestLap = $this->input('prediction_data.fastest_lap');
                if ($fastestLap !== null && $fastestLap !== '') {
                    $exists = is_numeric($fastestLap)
                        ? Drivers::where('id', (int) $fastestLap)->exists()
                        : Drivers::where('driver_id', (string) $fastestLap)->exists();
                    if (! $exists) {
                        $validator->errors()->add(
                            'prediction_data.fastest_lap',
                            'The selected driver is invalid.'
                        );
                    }
                }
            }
            $teammateBattles = $this->input('prediction_data.teammate_battles', []);
            if ($type !== 'preseason' || ! is_array($teammateBattles)) {
                return;
            }
            foreach ($teammateBattles as $teamId => $driverId) {
                $driver = Drivers::find($driverId);
                if ($driver && (int) $driver->team_id !== (int) $teamId) {
                    $validator->errors()->add(
                        'prediction_data.teammate_battles',
                        "Driver must belong to the selected team for team ID {$teamId}."
                    );
                    break;
                }
            }
        });
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Prediction type must be race, preseason, or midseason.',
            'season.min' => 'Season must be 1950 or later.',
            'season.max' => 'Season cannot be later than 2030.',
            'race_round.min' => 'Race round must be 1 or higher.',
            'race_round.max' => 'Race round cannot exceed 25.',
            'prediction_data.driver_order.required_if' => 'Driver order is required for race predictions.',
            'prediction_data.driver_order.min' => 'Driver order must include between 1 and '.config('f1.max_drivers', 22).' drivers.',
            'prediction_data.driver_order.max' => 'Driver order must include between 1 and '.config('f1.max_drivers', 22).' drivers.',
            'prediction_data.team_order.required_if' => 'Constructor order is required for preseason and midseason predictions.',
            'prediction_data.team_order.min' => 'Constructor order must include between 1 and '.config('f1.max_constructors', 11).' constructors.',
            'prediction_data.team_order.max' => 'Constructor order must include between 1 and '.config('f1.max_constructors', 11).' constructors.',
            'prediction_data.driver_championship.required_if' => 'Driver championship order is required for midseason predictions.',
            'prediction_data.driver_championship.min' => 'Driver championship order must include between 1 and '.config('f1.max_drivers', 22).' drivers.',
            'prediction_data.driver_championship.max' => 'Driver championship order must include between 1 and '.config('f1.max_drivers', 22).' drivers.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validation error messages.
     */
    public function attributes(): array
    {
        return [
            'type' => 'prediction type',
            'season' => 'season',
            'race_round' => 'race round',
            'race_id' => 'race',
            'prediction_data.driver_order' => 'driver order',
            'prediction_data.fastest_lap' => 'fastest lap',
            'prediction_data.team_order' => 'constructor order',
            'prediction_data.driver_championship' => 'driver championship order',
            'prediction_data.superlatives' => 'superlatives',
            'notes' => 'notes',
        ];
    }
}
