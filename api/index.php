<?php
require 'RestServer.php';
require 'DomainController.php';
$server = new RestServer('production'); //debug or production
$server->addClass('DomainController');
$server->handle();