<?php namespace App\Http\Requests\Repo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateRepo
 */
class CreateRepo extends FormRequest
{
    public $redirect = '/repo';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'url' => 'required|max:255|unique:repos,url|gitRepoUrl',
            'name' => 'required|max:255|unique:repos,name',
            'group' => 'max:255',
        ];
    }
}
