<?php

namespace App\Http\Requests\Auth;

use App\Traits\CanUploadImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserUpdateProfileRequest extends FormRequest
{
    use CanUploadImage;

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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'string',
            'photo' => 'image',
            'email' => [
                'email',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
            'username' => [
                'string',
                Rule::unique('users', 'username')->ignore(Auth::id()),
            ],
            'country_code' => 'string',
            'phone_number' => 'string|max:20',
        ];
    }

    /**
     * Get parsed data from the request.
     *
     * @return array<string, mixed>
     */
    public function parsed(): array
    {
        $data = parent::validated();

        if ($this->hasFile('photo')) {
            $data['photo'] = $this->uploadImage($this, 'photo', config('upload.path.users'));
        }

        return $data;
    }
}
