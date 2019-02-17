<?php

use App\Controllers\ApiController;

// Routes

$app->get('/scripts/', ApiController::class . ':read');
$app->get('/scripts/{id:[0-9]+}', ApiController::class . ':read');
$app->post('/scripts/', ApiController::class . ':create');
$app->put('/scripts/{id:[0-9]+}', ApiController::class . ':update');
$app->delete('/scripts/{id:[0-9]+}', ApiController::class . ':delete');