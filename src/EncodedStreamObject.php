<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:47
 */

namespace rossoneri\pdfparser;


class EncodedStreamObject extends StreamObject
{
    public $decoded_self;

    /**
     * EncodedStreamObject constructor.
     */
    public function __construct()
    {
        $this->decoded_self = null;
    }

    public function get_data() {
        if ($this->decoded_self) {
            return $this->decoded_self->get_data();
        }

        $decoded = new DecodedStreamObject();
        $decoded->stream = decode_stream_data($this);
        foreach ($this->data as $key=>$value) {
            if (!in_array($key, array("/Length", "/Filter", "/DecodeParms"))) {
                $decoded->data[$key] = $value;
            }
        }
        $this->decoded_self = $decoded;
        return $decoded->stream;
    }

    public function set_data($data) {
        $this->stream = $data;
    }
}