<?php namespace App\Http\Requests\Repo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateCheck
 */
class CreateCheck extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'repo_id' => 'required|exists:repos,id',
            'type' => 'required|in:http,command',
            'container' => 'required|alpha',
            'params' => 'array',
            'params.slack' => 'string',
            'params.text' => 'valid_pattern',
            'params.url' => 'string',
            'params.command' => 'string|required_if:type,command',
        ];
    }
}
