<?php

namespace App\Http\Controllers;

use App\LiveData;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\TCPController;
require_once('TCPController.php');

class ServerController extends Controller
{
    public function startServer()
    {

        $server = new TCPController("localhost",10000); // Create a Server binding to the given ip address and listen to port 31337 for connections
        $server->max_clients = 10; // Allow no more than 10 people to connect at a time
        $server->hook("CONNECT","handle_connect"); // Run handle_connect every time someone connects
        $server->hook("INPUT","handle_input"); // Run handle_input whenever text is sent to the server
        $server->infinite_loop(); // Run Server Code Until Process is terminated.
    }


    public function handle_connect($server,$client,$input)
    {
        TCPController::socket_write_smart($client->socket,"Hello ","");
    }


    public function handle_input($server,$client,$input)
    {
        // You probably want to sanitize your inputs here
       // $trim = trim($input); // Trim the input, Remove Line Endings and Extra Whitespace.


        echo $input;
        if (strtolower($input) == "quit") // User Wants to quit the server
        {
            TCPController::socket_write_smart($client->socket, "Oh... Goodbye..."); // Give the user a sad goodbye message, meany!
            $server->disconnect($client->server_clients_index); // Disconnect this client.
            return; // Ends the function
        }




       // $output = "#200#"; // Reverse the String
     //   socket_write($client->socket,$input);
      //  TCPController::socket_write_smart($client->socket, $output); // Send the Client back the String
     //   TCPController::socket_write_smart($client->socket, "String?", "#"); // Request Another String
    }

    
}
