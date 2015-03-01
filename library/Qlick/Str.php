<?php

class Qlick_Str
{

    function ucfirst_utf8($stri){
        if($stri{0}>="\xc3")
            return (($stri{1}>="\xa0")?
            ($stri{0}.chr(ord($stri{1})-32)):
            ($stri{0}.$stri{1})).substr($stri,2);
        else return ucfirst($stri);
    }
    
    function convertToLink($str) {
        return mb_strtolower(str_replace(' ', '-', $str), 'UTF-8');
    }
    
    function convertLinkToStr($link) {
        return mb_strtolower(str_replace('-', ' ', $str), 'UTF-8');
    }
    
}