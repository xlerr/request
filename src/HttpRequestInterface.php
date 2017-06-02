<?php

namespace xlerr\request;

interface HttpRequestInterface
{
    public static function createFile($file, $name = null, $mime = 'application/octet-stream');
    public static function post(string $url, array $data = [], array $options = []);
    public static function put(string $url, array $data = [], array $options = []);
    public static function get(string $url, array $data = [], array $options = []);
    public static function delete(string $url, array $data = [], array $options = []);
}
