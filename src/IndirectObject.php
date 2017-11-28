<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:40
 */

namespace rossoneri\pdfparser;

use Exception;
/**
 * @property  idnum
 */
class IndirectObject extends Object
{
    public $idnum;
    public $generation;
    public $pdf;

    public function __construct($idnum, $generation, $pdf)
    {
        $this->idnum = $idnum;
        $this->generation = $generation;
        $this->pdf = $pdf;
    }

    public function get_object() {
        return $this->pdf->get_object($this);
    }

    public static function read_from_stream($stream, $pdf) {
        $idnum = '';
        while (true) {
            $tok = fread($stream, 1);
            if (trim($tok) == '') {
                break;
            }
            $idnum .= $tok;
        }
        $generation = '';
        while (true) {
            $tok = fread($stream, 1);
            if (trim($tok) == '') {
                break;
            }
            $generation .= $tok;
        }
        $r = fread($stream, 1);
        if ($r != "R") {
            throw new Exception("Error reading PDF: error reading indirect object reference.");
        }
        return new IndirectObject((int) $idnum, (int) $generation, $pdf);
    }

    public function __toString() {
        return "IndirectObject({$this->idnum}, {$this->generation})";
    }
}