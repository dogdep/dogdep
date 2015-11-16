<?php namespace App\Http\Requests\Repo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateCommand
 */
class CreateCommand extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'repo_id' => 'required|exists:repos,id',
            'order' => 'required|integer',
            'type' => 'required|in:post-release,one-off',
            'command' => 'required|string',
            'container' => 'required|string',
        ];
    }
}
