<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class TeamInviteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'team_id'              => 'required|exists:teams,id',
            'email'                => 'required|email',
            'role'                 => 'required|in:creator,viewer',
            'credits_to_allocate'  => 'nullable|integer|min:0', // ✅ Viewer ko 0 allow krega
        ];
    }

    public function messages()
    {
        return [
            'email.required'       => __('Email is required'),
            'email.email'          => __('Please enter a valid email address'),
            'role.required'        => __('Role is required'),
            'credits_to_allocate.integer' => __('Credits must be a number'),
        ];
    }
}