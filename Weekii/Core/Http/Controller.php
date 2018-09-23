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

    public function writeJson(array $params, int $code)
    {
        $this->response()->withStatus($code);
        $this->response()->write(json_encode($params));
    }

    public function request()
    {
        return $this->request;
    }

    public function response()
    {
        return $this->response;
    }

    public function redirect($url, int $code)
    {
        $this->response->redirect($url, $code);
    }

    public function getActionName()
    {
        return $this->request->getActionName();
    }

    protected function actionNotFound($actionName)
    {
        $this->response()->withStatus(404);
        $this->write("<h1>Action: {$actionName} not found</h1>");
    }
}