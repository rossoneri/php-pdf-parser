<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:39
 */

namespace rossoneri\pdfparser;

use Exception;
class NullObject extends Object
{
    public static function read_from_stream($stream) {
        $nulltxt = fread($stream, 4);
        if ($nulltxt != "null") {
            throw new Exception("Error reading PDF: error reading null object.");
        }
        return new NullObject();
    }
}