<?php

namespace Weekii\Core\Http;

abstract class Controller
{
    private $request;
    private $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function write($string) {
        $this->response->write($string);
    }

    public function request()
    {
        return $this->request;
    }

    public function response()
    {
        return $this->response;
    }

    protected function actionNotFound($actionName)
    {
        $this->response()->withStatus(404);
        $this->write("<h1>Action: {$actionName} not found</h1>");
    }
}