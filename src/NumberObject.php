<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:44
 */

namespace rossoneri\pdfparser;

class NumberObject extends Object
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function read_from_stream($stream) {
        $name = '';
        while (true) {
            $tok = fread($stream, 1);
            if (($tok != '+') && ($tok != '-') && ($tok != '.') && (!ctype_digit($tok))) {
                fseek($stream, -1, 1);
                break;
            }
            $name .= $tok;
        }
        if (strpos($name, '.') !== false) {
            return (float) $name;
        } else {
            return (int) $name;
        }
    }
}