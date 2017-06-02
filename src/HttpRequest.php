<?php

namespace xlerr\request;

class HttpRequest extends Request implements HttpRequestInterface
{
    /**
     * @param $file
     * @param $name
     * @param null $mime
     */
    public static function createFile($file, $name = null, $mime = 'application/octet-stream')
    {
        return new FileObject($file, $name, $mime);
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $options
     */
    public static function delete(string $url, array $options = [])
    {
        return self::send('delete', $url, $options);
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $options
     */
    public static function get(string $url, array $options = [])
    {
        return self::send('get', $url, $options);
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $options
     */
    public static function post(string $url, array $options = [])
    {
        return self::send('post', $url, $options);
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $options
     */
    public static function put(string $url, array $options = [])
    {
        return self::send('put', $url, $options);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     */
    public static function send(string $method, string $url, array $options = [])
    {
        $request = (new static($url))->method($method);

        foreach (['headers', 'data', 'params'] as $item) {
            if (array_key_exists($item, $options) && is_array($options[$item])) {
                $request->$item($options[$item]);
            }
        }

        $stream = $request->submit();
        return new HttpResponse($stream);
    }
}
