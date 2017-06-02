<?php

namespace xlerr\request;

use Exception;

class FileObject
{
    public $mime;
    public $filename;
    public $content;

    public function __construct($file, $name = null, $mime = 'application/octet-stream')
    {
        if ($name !== null) {
            $this->filename = $name;
            $this->content = $file;
            $this->mime = $mime;
        } else if (is_file($file)) {
            $this->filename = basename($file);
            $this->mime = self::getMimeType($file);
            $this->content = file_get_contents($file);
        } else {
            throw new Exception('File does not exist: ' . $file);
        }
    }

    public static function getMimeType($file)
    {
        if (!extension_loaded('fileinfo')) {
            throw new Exception('The fileinfo extension is missing');
        }

        $info = finfo_open(FILEINFO_MIME_TYPE);
        if ($info) {
            $result = finfo_file($info, $file);
            finfo_close($info);

            if ($result !== false) {
                return $result;
            }
        }
    }
}
