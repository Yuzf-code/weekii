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

    protected function write($string) {
        $this->response->write($string);
    }

    protected function writeJson(array $params, int $code)
    {
        $this->response()->withStatus($code);
        $this->response()->write(json_encode($params));
        $this->response()->header('Content-type','application/json;charset=utf-8');
    }

    protected function request()
    {
        return $this->request;
    }

    protected function response()
    {
        return $this->response;
    }

    protected function redirect($url, int $code)
    {
        $this->response->redirect($url, $code);
    }

    protected function getActionName()
    {
        return $this->request->getActionName();
    }

    protected function actionNotFound($actionName)
    {
        $this->response()->withStatus(404);
    }
}