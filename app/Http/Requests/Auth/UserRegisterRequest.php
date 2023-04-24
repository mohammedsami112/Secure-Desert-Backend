<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Str;
use App\Traits\CanUploadImage;
use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
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
            /* Required fields */
            'username' => 'required|string|unique:users,username',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',

            /* Optional fields */
            'photo' => 'image',
            'country_code' => 'string|size:2',
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
        return array_merge(parent::validated(), [
            'user_id' => Str::random(3) . '-' . round(rand(1000, 9999)),
            'password' => bcrypt($this->password),
            'photo' => $this->uploadImage($this, 'photo', config('upload.path.users')),
        ]);
    }
}
