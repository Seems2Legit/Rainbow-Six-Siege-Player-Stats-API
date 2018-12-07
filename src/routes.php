<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$version = "1";
$prefix = "/api/$version/";

$app->get($prefix . "test", function (Request $request, Response $response, array $args) {
    $this->logger->info("Test request @ '".$request->getUri()->getPath()."'");

    return "test";
});

$app->post($prefix . "player", function (Request $request, Response $response, array $args) {
    $this->logger->info("Player request @ '".$request->getUri()->getPath()."'");

    return "test";
});