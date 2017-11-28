<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:40
 */

namespace rossoneri\pdfparser;

use Exception;
class ListObject extends Object
{
    public static function read_from_stream($stream, $pdf) {
        $arr = array();
        $tmp = fread($stream, 1);
        if ($tmp != '[') {
            throw new Exception("Error reading PDF: error reading array.");
        }
        while (true) {
            $tok = fread($stream, 1);
            while (trim($tok) == '') {
                $tok = fread($stream, 1);
            }
            fseek($stream, -1, 1);
            $peekahead = fread($stream, 1);
            if ($peekahead == ']') {
                break;
            }
            fseek($stream, -1, 1);
            $arr[] = read_object($stream, $pdf);
        }
        return $arr;
    }
}