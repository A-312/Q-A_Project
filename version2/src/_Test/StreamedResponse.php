<?php
use Symfony\HttpFoundation\StreamedResponse;
$response = new StreamedResponse();
$response->setCallback(function () {
    echo 'Hello';
    flush();
});
$response->send();
$response = new StreamedResponse();
$response->setCallback(function () {
	sleep(1);
    echo ' World';
    flush();
});
$response->send();
$response = new StreamedResponse();
$response->setCallback(function () {
	sleep(1);
    echo ' !';
    flush();
});
$response->send();