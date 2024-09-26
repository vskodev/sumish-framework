<?php

namespace Sumish;

abstract class Controller {
    protected $container;

    function __construct(Container $container) {
        $this->container = $container;
    }

    public function __get($id) {
        return $this->container->get($id);
    }

    public function dispatch() {
        $controller = $this->controller;
        $action = $this->app->action;
        $params = $this->app->params;

        if (is_callable([$controller, $action])) {
            call_user_func_array([$controller, $action], [$params]);
        }
    }
}
