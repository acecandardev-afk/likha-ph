<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        $product = $this->route('product');

        return $this->user() && $this->user()->can('create', [Review::class, $order, $product]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'comment' => [
                'nullable',
                'string',
                'min:10',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Please select a rating.',
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',
            'comment.min' => 'Review comment must be at least 10 characters.',
            'comment.max' => 'Review comment cannot exceed 1000 characters.',
        ];
    }
}
