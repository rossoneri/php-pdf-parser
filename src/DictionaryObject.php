<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:50
 */

namespace rossoneri\pdfparser;


use Exception;

class DictionaryObject extends Object
{
    public $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public static function read_from_stream($stream, $pdf) {
        $tmp = fread($stream, 2);
        if ($tmp != '<<') {
            throw new Exception("Error reading PDF: dictionary read error.");
        }
        $data = array();
        while (true) {
            $tok = read_non_whitespace($stream);
            if ($tok == '>') {
                fread($stream, 1);
                break;
            }
            fseek($stream, -1, 1);
            $key = read_object($stream, $pdf);
            $tok = read_non_whitespace($stream);
            fseek($stream, -1, 1);
            $value = read_object($stream, $pdf);
            if (in_array($key, array_keys($data))) {
                throw new Exception("Error reading PDF: multiple definitions in dictionary.");
            }
            $data[$key] = $value;
        }

        $pos = ftell($stream);
        $s = read_non_whitespace($stream);
        if (($s == 's') && (fread($stream, 5) == 'tream')) {
            $eol = fread($stream, 1);
            while ($eol == ' ') {
                $eol = fread($stream, 1);
            }
            assert(($eol == "\n") || ($eol == "\r"));
            if ($eol == "\r") {
                fread($stream, 1);
            }
            $length = $data['/Length'];
            if (is_a($length, 'IndirectObject')) {
                $t = ftell($stream);
                $length = $pdf->get_object($length);
                fseek($stream, $t, 0);
            }
            $data['__streamdata__'] = fread($stream, $length);
            $e = read_non_whitespace($stream);
            $ndstream = fread($stream, 8);
            if (($e + $ndstream) != "endstream") {
                $pos = ftell($stream);
                fseek($stream, -10, 1);
                $end = fread($stream, 9);
                if ($end == "endstream") {
                    $data['__streamdata__'] = substr($data['__streamdata__'], 0, -1);
                } else {
                    fseek($stream, $pos, 0);
                    throw new Exception("Error reading PDF: Unable to find 'endstream' marker after stream.");
                }
            }
        } else {
            fseek($stream, $pos, 0);
        }
        if (in_array('__streamdata__', array_keys($data))) {
            return StreamObject::init_from_dict($data);
        } else {
            return $data;
        }
    }
}