<?php namespace App\Http\Requests\Repo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateVolume
 */
class CreateVolume extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'repo_id' => 'required|exists:repos,id',
            'volume' => 'required|volume_path|string',
            'container' => 'required|string',
        ];
    }
}
