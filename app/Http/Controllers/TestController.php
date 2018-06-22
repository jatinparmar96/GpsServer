<?php

namespace App\Http\Controllers;

use App\LiveData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Amp\Loop;
use Amp\Socket\ServerSocket;
use function Amp\asyncCall;


class TestController extends Controller
{
    public function runAmpServer()
    {

        Loop::run(function () {
            $uri = "tcp://127.0.0.1:1337";

            $clientHandler = function (ServerSocket $socket) {
                $buffer = '';
                while (null !== $chunk = yield $socket->read()) {

                    $buffer.=$chunk;

                    if($pos = strpos($buffer,'#')!==false)
                    {
                        echo $buffer."\n";
                        $buffer= '';
                    }


                }
            };

            $server = \Amp\Socket\listen($uri);

            while ($socket = yield $server->accept()) {
                asyncCall($clientHandler, $socket);
            }
        });
    }

    protected function process_data($data)
    {
        $sanitized_data = explode(',', $data);
        if (count($sanitized_data) == 14) {
            $extra_data = explode('*', $sanitized_data[0]);
            $imei = $extra_data[0];
            $time = Carbon::createFromFormat('ymdhis', $extra_data[1]);
            $latitude = $sanitized_data[1];
            $longitude = $sanitized_data[3];
            $speed = $sanitized_data[6];
            $live = new LiveData();
            $live->imei = $imei;
            $live->time = $time;
            $live->latitude = $latitude;
            $live->longitude = $longitude;
            $live->speed = $speed;
            $live->save();
            return true;
        }
        return false;
    }
}
