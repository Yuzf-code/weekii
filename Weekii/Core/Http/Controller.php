<?php

namespace Weekii\Core\Http;

use duncan3dc\Laravel\BladeInstance;
use Weekii\Core\App;

/**
 * Class Controller
 * @property App $app
 * @package Weekii\Core\Http
 */
abstract class Controller
{
    protected $app;
    protected $request;
    protected $response;
    protected $view;

    protected $tplVar = [];

    public function __construct(Request $request, Response $response, BladeInstance $view)
    {
        $this->app = App::getInstance();
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
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

    protected function assign($tplVar, $value = null)
    {
        if (is_array($tplVar)) {
            $this->tplVar = $tplVar;
        } else {
            $this->tplVar[$tplVar] = $value;
        }
    }

    protected function fetch(string $view, array $params = [])
    {
        $params = array_merge($params, $this->tplVar);
        $content = $this->view->render($view, $params);
        $this->response->write($content);
    }
}