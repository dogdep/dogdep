<?php namespace App\Http\Requests\Repo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateRepo
 */
class UpdateRepo extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'compose_yml' => 'max:255|string',
            'domain' => 'max:255',
            'env' => 'array',
        ];
    }
}
