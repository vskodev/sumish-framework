<?php

namespace Sumish;

class Log {
    protected $handle;

    public function __construct($filename) {
        if (is_file($filename)) {
            $this->handle = fopen($filename, 'a');
        }

        return false;
    }

    public function write($message) {
        fwrite($this->handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
    }

    public function __destruct() {
        fclose($this->handle);
    }
}
