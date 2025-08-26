<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'type' => ['required', 'string', Rule::in(['race', 'preseason', 'midseason'])],
            'season' => ['required', 'integer', 'min:1950', 'max:2030'],
            'race_round' => ['nullable', 'integer', 'min:1', 'max:25'],
            'race_id' => ['nullable', 'integer', 'exists:races,id'],
            'prediction_data' => ['required', 'array'],
            'prediction_data.driver_order' => ['required_if:type,race', 'array', 'min:20', 'max:20'],
            'prediction_data.driver_order.*' => ['required_if:type,race', 'integer', 'exists:drivers,id'],
            'prediction_data.fastest_lap' => ['nullable', 'integer', 'exists:drivers,id'],
            'prediction_data.team_order' => ['required_if:type,preseason,midseason', 'array', 'min:10', 'max:10'],
            'prediction_data.team_order.*' => ['required_if:type,preseason,midseason', 'integer', 'exists:teams,id'],
            'prediction_data.driver_championship' => ['required_if:type,preseason,midseason', 'array', 'min:20', 'max:20'],
            'prediction_data.driver_championship.*' => ['required_if:type,preseason,midseason', 'integer', 'exists:drivers,id'],
            'prediction_data.superlatives' => ['nullable', 'array'],
            // Accept IDs or strings for superlatives to support references by ID
            'prediction_data.superlatives.*' => ['nullable'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
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
            'prediction_data.driver_order.min' => 'Driver order must include exactly 20 drivers.',
            'prediction_data.driver_order.max' => 'Driver order must include exactly 20 drivers.',
            'prediction_data.team_order.required_if' => 'Team order is required for preseason and midseason predictions.',
            'prediction_data.team_order.min' => 'Team order must include exactly 10 teams.',
            'prediction_data.team_order.max' => 'Team order must include exactly 10 teams.',
            'prediction_data.driver_championship.required_if' => 'Driver championship order is required for preseason and midseason predictions.',
            'prediction_data.driver_championship.min' => 'Driver championship order must include exactly 20 drivers.',
            'prediction_data.driver_championship.max' => 'Driver championship order must include exactly 20 drivers.',
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
            'prediction_data.team_order' => 'team order',
            'prediction_data.driver_championship' => 'driver championship order',
            'prediction_data.superlatives' => 'superlatives',
            'notes' => 'notes',
        ];
    }
}
