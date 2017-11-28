<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午5:05
 */

namespace rossoneri\pdfparser;


class DocumentInformation
{
    const DOC_INFO_KEYS = [
        'Title' => 'title',
        'Author' => 'author',
        'Keywords' => 'keywords',
        'Pages' => 'pages',
        'Subject' => 'subject',
        'Creator' => 'creator',
        'Producer' => 'producer',
        'CreationDate' => 'creation_date',
        'ModDate' => 'mod_date'
    ];

    public $data;

    public function __construct($info)
    {
        $data = array();
        foreach ($info as $key=>$value) {
            $key = str_replace('/', '', $key);
            $data[self::DOC_INFO_KEYS[$key]] = $value;
        }
        $this->data = $data;
    }
}