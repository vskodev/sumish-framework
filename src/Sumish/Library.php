<?php

namespace Sumish;

class Library {
    public function load($file) {
        if (is_file($file)) {
            require_once $file;
        }
    }
}