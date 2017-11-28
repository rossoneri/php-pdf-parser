<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:49
 */

namespace rossoneri\pdfparser;


class DecodedStreamObject extends StreamObject
{

    public function get_data() {
        return $this->stream;
    }

    public function set_data($data) {
        $this->stream = $data;
    }
}