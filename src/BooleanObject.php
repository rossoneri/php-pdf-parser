<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:35
 */

namespace rossoneri\pdfparser;


class BooleanObject extends Object
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function read_from_stream($stream) {
        $word = fread($stream, 4);
        if ($word == "true") {
            return new BooleanObject(true);
        } else if ($word == "fals") {
            fread($stream, 1);
            return new BooleanObject(false);
        }
    }
}