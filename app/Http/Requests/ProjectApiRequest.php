<?php

namespace App\Http\Requests;

use App\Http\Middleware\ApiAuthenticate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProjectApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return null != ApiAuthenticate::$apiKey;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        ];
    }

    public function getApiUser(): User
    {
        return ApiAuthenticate::$apiKey->user;
    }

    public function project()
    {
        return Project::whereHas('users', function($query) {
            $query->where('user_id', ApiAuthenticate::$apiKey->user->id);
        })->findOrFail($this->project);
    }
}
