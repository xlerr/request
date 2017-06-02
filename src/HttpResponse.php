<?php

namespace xlerr\request;

class HttpResponse implements HttpResponseInterface
{
    protected $stream;
    protected $meta;
    protected $protocol;
    protected $headers;
    protected $content;

    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new RequestException('The sending request failed');
        }
        $this->stream = $stream;
    }

    public function getProtocol()
    {
        if ($this->protocol === null) {
            $wrapperData = $this->readMetaData('wrapper_data');

            $this->protocol = array_shift($wrapperData);
        }
        return $this->protocol;
    }

    public function getHeader($key, $default = null)
    {
        if ($this->headers === null) {
            $this->headers = $this->readHeaders();
        }
        return isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }

    public function getContent()
    {
        if ($this->content === null) {
            $this->content = stream_get_contents($this->stream);
        }
        return $this->content;
    }

    protected function readHeaders()
    {
        $wrapperData = $this->readMetaData('wrapper_data');

        $headers = [];
        foreach ($wrapperData as $item) {
            list($name, $value) = explode(': ', $item, 2);
            $name = strtolower($name);
            if (isset($headers[$name])) {
                if (is_array($headers[$name])) {
                    $headers[$name][] = $value;
                } else {
                    $headers[$name] = [$value];
                }
            } else {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    protected function readMetaData($key = null)
    {
        if ($this->meta === null) {
            $this->meta = stream_get_meta_data($this->stream);
        }

        if ($key === null) {
            return $this->meta;
        } elseif (isset($this->meta[$key])) {
            return $this->meta[$key];
        } else {
            return null;
        }
    }
}
