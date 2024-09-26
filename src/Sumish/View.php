<?php

namespace Sumish;

class View {
    public $processor = 'twig';
    public $ext = '.tpl';
    protected $path;
    protected $file;
    protected $template;
    protected $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function renderData($template, array $data = []) {
        $this->template = $template . $this->ext;
        $this->_detectPath();
        return $this->process($data);
    }

    public function renderOutput($template, $data = []) {
        return $this->renderData($template, $data);
    }

    public function render($template, array $data = []) {
        $output = $this->renderOutput($template, $data);
        $this->print($output);
    }

    public function process($data) {
        return $this->_checkTwig()
            ? $this->_processTwig($data)
            : $this->_processDynamic($data);
    }

    public function print($text) {
        $this->container->response->print($text);
    }

    protected function _processDynamic(array $data = []) {
        $file = $this->file;

        if ($file && $data) {
            if (is_file($this->file)) {
                ob_start();
                extract($data, EXTR_SKIP);
                include_once($file);
                $output = ob_get_contents();
                ob_end_clean();
                return $output;
            }
        }

        return false;
    }

    protected function _processTwig(array $data = []) {
        static $twig = null;
        $checkTwig = false;
        $template = $this->template;
        $path = $this->path;

        if (is_null($twig)) {
            if (is_file($this->file)) {
                $loader = new \Twig_Loader_Filesystem($path);
                $twig = new \Twig_Environment($loader);
                $checkTwig = true;
            }
        }

        if ($checkTwig) {
            return $twig->render($template, $data);
        }

        return false;
    }

    private function _checkTwig() {
        return ($this->processor == 'twig' &&
                class_exists('Twig_Loader_Filesystem'));
    }

    private function _detectPath() {
        $this->file = $this->_getPathTemplate();
        if (!is_file($this->file)) {
            $this->file = $this->_getPathTemplate(DIR_ROOT);
        }
    }

    private function _getPathTemplate($path = null) {
        $this->path = is_null($path)
              ? $this->container->app->path . '/templates'
              : $path . '/templates/' . $this->container->app->name;

        return $this->path . '/' . $this->template;
    }
}
