<?php

namespace Sumish;

class Document {
    public $output;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function init() {
        $this->output = $this->getOutput();

        if ($this->isTurn()) {
            print($this->output);
        } else {
            exit($this->output);
        }
    }

    public function isTurn() {
        return $this->container->response->turn;
    }

    public function getOutput() {
        return $this->container->response->output;
    }

    public function setOutput($output) {
        $this->container->response->setOutput($output);
    }
}