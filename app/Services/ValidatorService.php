<?php namespace App\Services;

use Gitonomy\Git\Admin;
use Illuminate\Validation\Validator;

class ValidatorService extends Validator
{
    public function validateGitRepoUrl($attribute, $value)
    {
        return Admin::isValidRepository($value, config('git.options'));
    }

    public function validateValidPattern($attribute, $value)
    {
        return @preg_match("#$value#", 'test') !== false;
    }

    public function validateVolumePath($attribute, $value)
    {
        if (!preg_match('/^[A-z0-9\/\-\_]+$/', $value)) {
            return false;
        }
        return true;
    }
}
