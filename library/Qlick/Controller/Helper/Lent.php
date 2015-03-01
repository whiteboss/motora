<?php

/**
 * 
 * Загрузка файлов
 * Примеры
 * http://www.mywebmymail.com/?q=content/easyphpthumbnail-class
 *
 */
class Qlick_Controller_Helper_Lent extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Реализует загрузку изображений
     * @return string название загруженного файла
     */
    public function sksort(&$array, $subkey="id", $sort_ascending=false) {
        if (count($array))
            $temp_array[key($array)] = array_shift($array);

        foreach($array as $key => $val){
            $offset = 0;
            $found = false;
            foreach($temp_array as $tmp_key => $tmp_val)
            {
                if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                {
                    $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                                array($key => $val),
                                                array_slice($temp_array,$offset)
                                              );
                    $found = true;
                }
                $offset++;
            }
            if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
        }

        if ($sort_ascending) $array = array_reverse($temp_array);

        else $array = $temp_array;
        
        return $array;
    }

//    public function direct(&$array, $subkey="id", $sort_ascending=false) {
//        return $this->sksort(&$array, $subkey="id", $sort_ascending=false);
//    }

}