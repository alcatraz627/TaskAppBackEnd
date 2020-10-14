<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

use Pusher\Pusher;

// $pusher = new Pusher("APP_KEY", "APP_SECRET", "APP_ID", array('cluster' => 'APP_CLUSTER'));

class Controller extends BaseController
{
    //
    private $pusher;

    private function getPusher()
    {
        if (!$this->pusher) {
            $this->pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                array(
                    'cluster' => config('broadcasting.options.cluster'),
                    'useTLS' => config('broadcasting.options.cluster')
                )
            );
        }
        return $this->pusher;
    }

    protected function paginate($list, Request $request)
    {
        $limit = $request->input('limit', 5);
        $offset = $request->input('offset', 0);

        return ['count' => count($list), 'data' => array_slice($list->toArray(), $offset, $limit)];
        // return $list;
    }

    protected function pushEvent($data, $idArr)
    {
        $channels = array_map(function ($id) {
            return 'channelForUser' . $id;
        }, $idArr);

        $event = 'listenerEvent';

        $this->getPusher()->trigger($channels, $event, $data);
    }
}
