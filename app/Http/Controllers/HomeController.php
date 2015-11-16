<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;

class HomeController extends Controller
{
    use DispatchesCommands;

    public function index()
    {
        return view('app');
    }

}
