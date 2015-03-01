<?php

class Qlick_Num
{

    function num2word($num, $words)
    {
        $num = $num % 100;
        if ($num > 19) $num = $num % 10;
        switch ($num) {
            case 1: return($words[0]);
            case 2: case 3: case 4: return($words[1]);
            default: return($words[2]);
        }
    }

    function normPrice($price)
    {
        if ($price < 1000000) {
            return number_format( floor($price / 1000), 0, ',', ' ' )." mil";
        } elseif ($price < 1000000000) {
            $million = floor($price / 1000000);
            $thousand = floor($price - $million * 1000000) / 1000;
            if ($thousand > 0) {
                return number_format( $million, 0, ',', ' ' )." млн. ".number_format( $thousand, 0, ',', ' ' )." mil";
            } else {
                return number_format( $million, 0, ',', ' ' )." млн.";
            }
        }
    }

    function normMileage($mileage)
    {
        if ($mileage > 1000) {
            return number_format( floor($mileage / 1000), 0, ',', ' ' )." mil";
        } else {
            return number_format( $mileage, 0, ',', ' ' );
        }
    }
    
    // нормализация числа с плавающей точкой
    function normFloat($value)
    {
        if (($value - floor($value)) == 0) {
            // есть число после запятой
            return floor($value);
        } else {
            return $value;
        }
    }
    

}