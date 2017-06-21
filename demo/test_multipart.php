<?php

include '../src/RequestMultipartBody.php';
include '../src/Request.php';
include '../src/Response.php';

$multipartBody = new \Opdss\Http\RequestMultipartBody();
$multipartBody->add('aaa', 'bbb');
$multipartBody->addFile('file', __DIR__ . '/test_multipart.php', 'test_multipart.php');

$request = \Opdss\Http\Request::factory();
$response = $request->post('http://www.istimer.com/multi.php', $multipartBody);

var_dump($response->body);
