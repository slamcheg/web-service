<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

$app = new \Proxy\Service\Core\Kernel();

$app->map('index', ['controller' => [\Proxy\Service\Controllers\DefaultController::class, 'index']], ['POST']);


$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $app->handle($request);
$response->send();