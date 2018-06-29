<?php

namespace App\Http\Controllers;

use App\HistoryData;
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
                $count = 0;
                while (null !== $chunk = yield $socket->read()) {

                    $buffer.=$chunk;
                    if ( preg_match_all("/[0-9].*#.*#.*#.*#/", utf8_decode($buffer), $matches, PREG_OFFSET_CAPTURE))
                    {
                        $this->process_history_data($matches[0][0][0]);
                        $buffer = '';
                    }

                    if ( preg_match_all("/[0-9].*#/", utf8_decode($buffer), $matches, PREG_OFFSET_CAPTURE))
                    {

                        $this->process_live_data($matches[0][0][0]);
                        $buffer = '';
                    }


                }
            };

            $server = \Amp\Socket\listen($uri);

            while ($socket = yield $server->accept()) {
                asyncCall($clientHandler, $socket);
            }
        });
    }
    protected function process_history_data($data)
    {
      $data= trim($data);
      $data_array = explode('*',$data);
      if (count($data_array)>=5)
      {
          for($i=1;$i<5;$i++)
          {
              $buffer = explode(",",$data_array[$i]);
              $imei =$data_array[0];
              $time = Carbon::createFromFormat('ymdhis', $buffer[0]);
              $latitude = $buffer[1];
              $longitude = $buffer[3];
              $speed = $buffer[6];
              $live = new HistoryData();
              $live->imei = $imei;
              $live->time = $time;
              $live->latitude = $latitude;
              $live->longitude = $longitude;
              $live->speed = $speed;
              $live->save();
          }
      }

    }
    protected function process_live_data($data)
    {
        $data =trim($data);
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
