<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:52
 */

namespace rossoneri\pdfparser;

use Exception;
class NameObject extends Object
{
    public $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public static function read_from_stream($stream) {
        $name = fread($stream, 1);
        if ($name != '/') {
            throw new Exception("Error reading PDF: name read error.");
        }
        while (true) {
            $tok = fread($stream, 1);
            if ((trim($tok) == '') || (in_array($tok, Constants::NAME_DELIMITERS))) {
                fseek($stream, -1, 1);
                break;
            }
            $name .= $tok;
        }
        return $name;
    }
}