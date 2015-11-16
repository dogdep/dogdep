<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Session;

abstract class Controller extends BaseController
{

    use DispatchesCommands, ValidatesRequests;

    /**
     * @param string $type
     * @param string $message
     */
    public function flash($type, $message)
    {
        Session::flash("alert-$type", $message);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function redirectBack()
    {
        return $this->redirect(app('url')->previous());
    }

    /**
     * @param string $url
     * @return \Illuminate\Http\Response
     */
    public function redirect($url)
    {
        if (!app('request')->ajax()) {
            return redirect($url);
        }

        $messages = [];
        foreach (['danger', 'warning', 'success', 'info'] as $msg) {
            $session = app('session');
            if($session->has('alert-' . $msg)) {
                $messages[$msg] = $session->get('alert-' . $msg);
                $session->get('alert-' . $msg);
                $session->put('alert-' . $msg, null);
            }
        }

        return response(json_encode([
            'status' => 'ok',
            'action' => 'redirect',
            'messages' => $messages,
            'url' => $url,
        ]), 200);
    }
}
