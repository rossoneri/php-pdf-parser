<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:31
 */

namespace rossoneri\pdfparser;

function decompress($data) {
    return gzuncompress($data);
}
function compress($data) {
    return gzcompress($data);
}

function decode_stream_data($stream) {
    $filters = $stream->data['/Filter'];
    if (!$filters) {
        $filters = array();
    }

    if (count($filters) && !is_a($filters[0], 'NameObject')) {
        $filters = array($filters);
    }

    $data = $stream->stream;
    foreach ($filters as $filter_type) {
        if ($filter_type == '/FlateDecode') {
            $data = FlateDecode::decode($data, $stream->data['/DecodeParms']);
        }
    }
    return $data;
}

function read_object($stream, $pdf) {
    $tok = fread($stream, 1);
    fseek($stream, -1, 1);
    if (($tok == 't') || ($tok == 'f')) {
        return BooleanObject::read_from_stream($stream);
    } else if ($tok == '(') {
        return read_string_from_stream($stream);
    } else if ($tok == '/') {
        return NameObject::read_from_stream($stream);
    } else if ($tok == '[') {
        return ListObject::read_from_stream($stream, $pdf);
    } else if ($tok == 'n') {
        return NullObject::read_from_stream($stream);
    } else if ($tok == '<') {
        $peek = fread($stream, 2);
        fseek($stream, -2, 1);
        if ($peek == '<<') {
            $r = DictionaryObject::read_from_stream($stream, $pdf);
            return $r;
        } else {
            return read_hex_string_from_stream($stream);
        }
    } else if ($tok == '%') {
        while (($tok != "\r") && ($tok != "\n")) {
            $tok = fread($stream, 1);
        }
        $tok = read_non_whitespace($stream);
        fseek($stream, -1, 1);
        return read_object($stream, $pdf);
    } else {
        if (($tok == '+') || ($tok == '-')) {
            return NumberObject::read_from_stream($stream);
        }
        $peek = fread($stream, 20);
        fseek($stream, -strlen($peek), 1);
        if (preg_match("/^(\d+)\s(\d+)\sR[^a-zA-Z]/", $peek)) {
            return IndirectObject::read_from_stream($stream, $pdf);
        } else {
            return NumberObject::read_from_stream($stream);
        }
    }
}

function create_string_object($string) {
    // UTF16_BIG_ENDIAN_BOM
    if (substr($string, 0, 2) == chr(0xFE) . chr(0xFF)) {
        return utf16_decode($string);
    }

    return $string;
}
function read_hex_string_from_stream($stream) {
    fread($stream, 1);
    $txt = '';
    $x = '';
    while (true) {
        $tok = read_non_whitespace($stream);
        if ($tok == '>') {
            break;
        }
        $x .= $tok;
        if (strlen($x) == 2) {
            $txt .= chr(base_convert($x, 16, 10));
            $x = '';
        }
    }
    if (strlen($x) == 1) {
        $x .= '0';
    }
    if (strlen($x) == 2) {
        $txt .= chr(base_convert($x, 16, 10));
    }

    return create_string_object($txt);
}
function read_string_from_stream($stream) {
    $tok = fread($stream, 1);
    $parens = 1;
    $txt = '';
    while (true) {
        $tok = fread($stream, 1);
        if ($tok == '(') {
            $parens += 1;
        } else if ($tok == ')') {
            $parens -= 1;
            if ($parens == 0) {
                break;
            }
        } else if ($tok == '\\') {
            $tok = fread($stream, 1);
            if ($tok == 'n') {
                $tok = "\n";
            } else if ($tok == 'r') {
                $tok = "\r";
            } else if ($tok == 't') {
                $tok = "\t";
            } else if ($tok == 'b') {
                $tok = "\b";
            } else if ($tok == 'f') {
                $tok = "\f";
            } else if ($tok == '(') {
                $tok = '(';
            } else if ($tok == ')') {
                $tok = ')';
            } else if ($tok == '\\') {
                $tok = "\\";
            } else if (ctype_digit($tok)) {
                for ($i=0; $i<2; $i++) {
                    $ntok = fread($stream, 1);
                    if (ctype_digit($ntok)) {
                        $tok += $ntok;
                    } else {
                        break;
                    }
                }
                $tok = chr(base_convert($tok, 8, 10));
            } else if (($tok == "\n") || ($tok == "\r") || ($tok == "\n\r")) {
                $tok = fread($stream, 1);
                if (!(($tok == "\n") || ($tok == "\r") || ($tok == "\n\r"))) {
                    fseek($stream, -1, 1);
                }
                $tok = '';
            } else {
                throw new Exception("Error reading PDF: unexpected escaped string.");
            }
        }
        $txt .= $tok;
    }
    return create_string_object($txt);
}

function read_until_whitespace($stream, $maxchars=null) {
    $txt = '';
    while (true) {
        $tok = fread($stream, 1);
        if (trim($tok) == '') {
            break;
        }
        $txt .= $tok;
        if (strlen($txt) == $maxchars) {
            break;
        }
    }
    return $txt;
}
function read_non_whitespace($stream) {
    $tok = ' ';
    while (($tok == "\n") || ($tok == "\r") || ($tok == " ") || ($tok == "\t")) {
        $tok = fread($stream, 1);
    }
    return $tok;
}
function utf16_decode($str) {
    if (strlen($str) < 2) {
        return $str;
    }

    $bom_be = true;
    $c0 = ord($str{0});
    $c1 = ord($str{1});
    if (($c0 == 0xfe) && ($c1 == 0xff)) {
        $str = substr($str, 2);
    } else if (($c0 == 0xff) && ($c1 == 0xfe)) {
        $str = substr($str, 2);
        $bom_be = false;
    }
    $len = strlen($str);
    $newstr = '';
    for ($i=0; $i<$len; $i+=2) {
        if ($bom_be) {
            $val = ord($str{$i}) << 4;
            $val += ord($str{$i+1});
        } else {
            $val = ord($str{$i+1}) << 4;
            $val += ord($str{$i});
        }
        $newstr .= ($val == 0x228) ? "\n" : chr($val);
    }
    return $newstr;
}

function convert_to_int($d, $size) {
    $out = bin2hex($d);
    if ($out) {
        $out = base_convert($out, 16, 10);
    }
    return (int) $out;
}

# debugging
function hi($o) {
    print _hi($o) . "\n";
}
function _hi($o) {
    if ($o === null) {
        return "None";
    } else if (is_array($o)) {
        $out = "{";
        foreach ($o as $k=>$v) {
            $out .= _hi($k) . " => " . _hi($v) . ", ";
        }
        $out .= "}";
        return $out;
    } else {
        return $o;
    }
}