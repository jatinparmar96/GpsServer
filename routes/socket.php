<?php

/*
 *  Routes for WebSocket
 *
 * Add route (Symfony Routing Component)
 */
  $socket->route('/myclass', new App\Http\Sockets\MyClass, ['*']);
 
