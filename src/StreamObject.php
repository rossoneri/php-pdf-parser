<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:45
 */

namespace rossoneri\pdfparser;


class StreamObject extends Object
{
    public $stream;
    public $data;

    public function __construct()
    {
        $this->stream = null;
        $this->data = array();
    }

    public static function init_from_dict($dict) {
        if (in_array('/Filter', array_keys($dict))) {
            $retval = new EncodedStreamObject();
        } else {
            $retval = new DecodedStreamObject();
        }
        $retval->stream = $dict['__streamdata__'];
        unset($dict['__streamdata__']);
        unset($dict['/Length']);
        foreach ($dict as $key=>$val) {
            $retval->data[$key] = $val;
        }
        return $retval;
    }

    function flate_encode() {
        if (in_array('/Filter', array_keys($this->data))) {
            $f = $this->data['/Filter'];
            if (is_array($f)) {
                array_unshift($f, new NameObject('/FlateDecode'));
            } else {
                $newf = array();
                $newf[] = new NameObject('/FlateDecode');
                $newf[] = $f;
                $f = $newf;
            }
        } else {
            $f = new NameObject('/FlateDecode');
        }

        $retval = new EncodedStreamObject();
        $filter = new NameObject('/Filter');
        $retval[$filter] = $f;
        $retval->stream = FlateDecode::encode($this->stream);
        return $retval;
    }
}