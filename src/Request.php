<?php

namespace xlerr\request;

use Exception;

class Request
{
    const MULTIPART_FORM_DATA = 'multipart/form-data';
    const APPLICATION_JSON = 'application/json';
    const APPLICATION_X_WWW_FORM_URLENCODE = 'application/x-www-form-urlencoded';

    protected $url;
    protected $method = 'POST';
    protected $headers = [];
    protected $params = [];
    protected $data = [];

    protected $options = [
        'timeout' => 30,
        'ignore_errors' => true,
        'protocol_version' => '1.1',
    ];

    private $boundary;

    public function __construct($url, array $options = [])
    {
        $this->url = $url;
        $this->options = $options + $this->options;
    }

    public function submit()
    {
        $url = $this->url . ($this->params ? '?' . http_build_query($this->params) : '');

        $headers = $this->headers;
        $this->headers = [];
        foreach ($headers as $name => $value) {
            $this->headers[strtolower($name)] = $value;
        }

        // 下面两句顺序不能乱,
        $content = $this->buildContent();
        $header = $this->buildHeaders();

        $context = [
            'notification' => [$this, 'streamNotificationCallback'],
            'http' => $this->options + [
                'method' => strtoupper($this->method),
                'header' => $header,
                'content' => $content,
            ],
        ];

        try {
            $stream = fopen($url, 'r', false, stream_context_create($context));
        } catch (Exception $e) {
            throw new RequestException($e->getMessage());
        }

        return $this->parseResponse($stream);
    }

    public function streamNotificationCallback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max)
    {

    }

    public function parseResponse($stream)
    {
        return $stream;
    }

    public function buildHeaders()
    {
        $headers = $this->headers;
        $headArr = [];
        foreach ($headers as $name => $values) {
            if (is_array($values)) {
                $valueArr = [];
                foreach ($values as $n => $v) {
                    $valueArr[] = "$n=$v";
                }
                $value = implode('; ', $valueArr);
            } else {
                $value = $values;
            }

            $headArr[] = "$name: $value";
        }

        return implode("\r\n", $headArr);
    }

    public function buildContent()
    {
        $this->headers['content-type'] = isset($this->headers['content-type']) ? $this->headers['content-type'] : self::APPLICATION_X_WWW_FORM_URLENCODE;

        $content = '';
        switch ($this->headers['content-type']) {
            case self::APPLICATION_X_WWW_FORM_URLENCODE:
                $content = http_build_query($this->data);
                break;

            case self::APPLICATION_JSON:
                $content = json_encode($this->data);
                break;

            case self::MULTIPART_FORM_DATA:
                $this->boundary = 'Stream-' . uniqid();
                $this->headers['content-type'] .= '; boundary=' . $this->boundary;

                $content = $this->buildMultipartFormData($this->data);
                $content .= '--' . $this->boundary . '--';
                break;

            default:
                $content = $this->data;
        }

        return $content;
    }

    public function buildMultipartFormData($data, $fix = null)
    {
        $content = '';
        foreach ($data as $n => $value) {
            $name = $fix ? $fix . '[' . (is_int($n) ? null : $n) . ']' : $n;
            if ($value instanceof FileObject) {
                $fStr = vsprintf("Content-Disposition: form-data; name=\"%s\"; filename=\"%s\"\nContent-Type: %s\n\n%s", [$name, $value->filename, $value->mime, $value->content]);
                $content .= '--' . $this->boundary . "\r\n" . $fStr . "\r\n";
            } else if (is_array($value)) {
                $content .= $this->buildMultipartFormData($value, $name);
            } else {
                $fStr = vsprintf("Content-Disposition: form-data; name=\"%s\"\n\n%s", [$name, $value]);
                $content .= '--' . $this->boundary . "\r\n" . $fStr . "\r\n";
            }
        }

        return $content;
    }

    public function method($method)
    {
        $this->method = $method;
        return $this;
    }

    public function headers(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function params(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function data(array $data)
    {
        $this->data = $data;
        return $this;
    }
}
