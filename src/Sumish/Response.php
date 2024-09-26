<?php

namespace Sumish;

class Response {
    public $turn;
    public $output;
    protected $headers = [];
    protected $level = 0;

    public function addHeader($header) {
        $this->headers[] = $header;
    }

    public function addHeaders($headers = []) {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }
    }

    public function addOutput($output) {
        $this->output .= $output;
    }

    public function setOutput($output) {
        $this->output = $output;
    }

    public function setCompression($level) {
        $this->level = $level;
    }

    public function print($text) {
        $this->addOutput($text);
    }

    public function redirect($url, $status = 302) {
        header('Location: ' . str_replace(['&amp;', "\n", "\r"], ['&', '', ''], $url), true, $status);
        exit;
    }

    private function compress($data = null) {
        if (is_null($data)) {
            $data = $this->output;
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)) {
            $encoding = 'gzip';
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)) {
            $encoding = 'x-gzip';
        }

        if (!isset($encoding) || ($this->level < -1 || $this->level > 9)) {
            return $data;
        }

        if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
            return $data;
        }

        if (headers_sent()) {
            return $data;
        }

        if (connection_status()) {
            return $data;
        }

        $this->addHeader('Content-Encoding: ' . $encoding);

        return gzencode($data, (int)$this->level);
    }

    public function init($turn = true) {
        if ($this->level) {
            $this->output = $this->compress();
        }

        if (!headers_sent()) {
            foreach ($this->headers as $header) {
                header($header, true);
            }
        }

        $this->turn = $turn;
    }
}
