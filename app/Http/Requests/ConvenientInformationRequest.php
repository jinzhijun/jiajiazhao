<?php

namespace App\Http\Requests;

use App\Model\CardCategory;

class ConvenientInformationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'string|required',
            'content' => 'string|required',
            'lng' => 'required',
            'lat' => 'required',
            'card_id' => ['required',
                function ($attribute, $value, $fail) {
                    if (!CardCategory::find($value)) {
                        return $fail('未发现有此分类！');
                    }
                },
            ],
        ];
    }
}
