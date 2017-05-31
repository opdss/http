<?php

include '../src/RequestMultipartBody.class.php';
include '../src/Request.class.php';
include '../src/Response.class.php';

$multipartBody = new \Opdss\Http\RequestMultipartBody();
$multipartBody->add('aaa', 'bbb');
$multipartBody->addFile('file', __DIR__ . '/test_multipart.php', 'test_multipart.php');

$request = \Opdss\Http\Request::newSession();
$response = $request->post('http://localhost:9999/multi.php', $multipartBody);

var_dump($response->body);
