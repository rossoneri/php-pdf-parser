<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/11/28
 * Time: 下午4:55
 */

namespace rossoneri\pdfparser;

use DOMDocument;



class XmpInformation extends Object
{
    const RDF_NAMESPACE = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
    const DC_NAMESPACE = "http://purl.org/dc/elements/1.1/";
    const XMP_NAMESPACE = "http://ns.adobe.com/xap/1.0/";
    const PDF_NAMESPACE = "http://ns.adobe.com/pdf/1.3/";
    const XMPMM_NAMESPACE = "http://ns.adobe.com/xap/1.0/mm/";
    const PDFX_NAMESPACE = "http://ns.adobe.com/pdfx/1.3/";
    const ISO_8601 = "/(?P<year>[0-9]{4})(-(?P<month>[0-9]{2})(-(?P<day>[0-9]+)(T(?P<hour>[0-9]{2}):(?P<minute>[0-9]{2})(:(?P<second>[0-9]{2}(.[0-9]+)?))?(?P<tzd>Z|[-+][0-9]{2}:[0-9]{2}))?)?)?";



    public $stream;

    public $cache;

    public $rdf_root;

    public function __construct($stream)
    {
        $this->stream = $stream;
        $doc_root = new DOMDocument();
        $doc_root->loadXML($this->stream->get_data());
        $rdf_els = $doc_root->getElementsByTagNameNS(self::RDF_NAMESPACE, 'RDF');
        $this->rdf_root = $rdf_els[0];

        $this->cache = array();
    }

    public function get_element($about_uri, $ns, $name) {
        $retval = array();

        $descs = $this->rdf_root->getElementsByTagNameNS(self::RDF_NAMESPACE, 'Description');
        foreach ($descs as $desc) {
            if ($desc->getAttributeNS(self::RDF_NAMESPACE, 'about') == $about_uri) {
                $attr = $desc->getAttributeNodeNS($ns, $name);
                if ($attr) {
                    $retval[] = $attr;
                }
                foreach ($desc->getElementsByTagNameNS($ns, $name) as $el) {
                    $retval[] = $el;
                }
            }
        }

        return $retval;
    }

    public function get_nodes_in_ns($about_uri, $ns) {
        $retval = array();

        $descs = $this->rdf_root->getElementsByTagNameNS(self::RDF_NAMESPACE, 'Description');
        foreach ($descs as $desc) {
            if ($desc->getAttributeNS(self::RDF_NAMESPACE, 'about') == $about_uri) {
                for ($i=0; $i<$desc->attributes->length; $i++) {
                    $attr = $desc->attributes->item($i);
                    if ($attr->namespaceURI == $ns) {
                        $retval[] = $attr;
                    }
                }

                foreach ($desc->childNodes as $child) {
                    if ($child->namespaceURI == $ns) {
                        $retval[] = $child;
                    }
                }
            }
        }

        return $retval;
    }


}