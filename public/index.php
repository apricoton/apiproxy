<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

$app = new Silex\Application();
$app->register(new Silex\Provider\VarDumperServiceProvider());

$app->match('{url}', function ($url, Request $request) use($app) {
    $body = $request->request->all();
    $query = $request->query->all();
    $method = $request->getMethod();
    
    $options = [];
    if (count($query)) {
        $options['query'] = $query;
    }
    if (count($body)) {
        $options['body'] = $body;
    }
    
    $client = new Client();
    $res = $client->request($method, $url, $options);
    
    $status = $res->getStatusCode();
    $body = $res->getBody();
    $headers = $res->getHeaders();
    
    $response = new Response();
    $response->headers->set('Content-Type', $headers['Content-Type']);
    $response->setContent($body);
    $response->setStatusCode($status);
    return $response;
})->assert('url', '.*');

$app->error(function (Exception $e, Request $request, $code) {
    return new Response($code . ': ' . $e->getMessage());
});

$app->run();