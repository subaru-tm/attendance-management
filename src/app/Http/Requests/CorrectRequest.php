<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attendanced_at' => 'date_format:H:i|before:leaved_at',
            'started_at.*' => 'date_format:H:i|after_or_equal:attendanced_at|before:leaved_at',
            'ended_at.*' => 'date_format:H:i|before_or_equal:leaved_at',
            'remarks' => 'required',
        ];
        
    }

    public function messages()
    {
        return [
            'attendanced_at.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'started_at.*.after_or_equal' => '休憩時間が勤務時間外です',
            'started_at.*.before' => '休憩時間が勤務時間外です',
            'ended_at.*.before_or_equal' => '休憩時間が勤務時間外です',
            'remarks.required' => '備考を記入してください'
        ];
    }
}
