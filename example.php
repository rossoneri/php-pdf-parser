<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午6:46
 */

use rossoneri\pdfparser\Reader;

require_once 'vendor/autoload.php';

$pdf = new Reader(fopen('test.pdf', 'rb'));
print $pdf->get_page_count();