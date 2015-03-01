<?php

class Qlick_Date
{

    /**
     * Calculate differences between two dates with precise semantics. Based on PHPs DateTime::diff()
     * implementation by Derick Rethans. Ported to PHP by Emil H, 2011-05-02. No rights reserved.
     * 
     * See here for original code:
     * http://svn.php.net/viewvc/php/php-src/trunk/ext/date/lib/tm2unixtime.c?revision=302890&view=markup
     * http://svn.php.net/viewvc/php/php-src/trunk/ext/date/lib/interval.c?revision=298973&view=markup
     */
    function _date_range_limit($start, $end, $adj, $a, $b, &$result)
    {
        if ($result[$a] < $start) {
            $result[$b] -= intval(($start - $result[$a] - 1) / $adj) + 1;
            $result[$a] += $adj * intval(($start - $result[$a] - 1) / $adj + 1);
        }

        if ($result[$a] >= $end) {
            $result[$b] += intval($result[$a] / $adj);
            $result[$a] -= $adj * intval($result[$a] / $adj);
        }

        return $result;
    }

    function _date_range_limit_days(&$base, &$result)
    {
        $days_in_month_leap = array(31, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $days_in_month = array(31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        $this->_date_range_limit(1, 13, 12, "m", "y", $base);

        $year = $base["y"];
        $month = $base["m"];

        if (!$result["invert"]) {
            while ($result["d"] < 0) {
                $month--;
                if ($month < 1) {
                    $month += 12;
                    $year--;
                }

                $leapyear = $year % 400 == 0 || ($year % 100 != 0 && $year % 4 == 0);
                $days = $leapyear ? $days_in_month_leap[$month] : $days_in_month[$month];

                $result["d"] += $days;
                $result["m"]--;
            }
        } else {
            while ($result["d"] < 0) {
                $leapyear = $year % 400 == 0 || ($year % 100 != 0 && $year % 4 == 0);
                $days = $leapyear ? $days_in_month_leap[$month] : $days_in_month[$month];

                $result["d"] += $days;
                $result["m"]--;

                $month++;
                if ($month > 12) {
                    $month -= 12;
                    $year++;
                }
            }
        }

        return $result;
    }

    function _date_normalize(&$base, &$result)
    {
        $result = $this->_date_range_limit(0, 60, 60, "s", "i", $result);
        $result = $this->_date_range_limit(0, 60, 60, "i", "h", $result);
        $result = $this->_date_range_limit(0, 24, 24, "h", "d", $result);
        $result = $this->_date_range_limit(0, 12, 12, "m", "y", $result);

        $result = $this->_date_range_limit_days($base, $result);

        $result = $this->_date_range_limit(0, 12, 12, "m", "y", $result);

        return $result;
    }

    /**
     * Accepts two unix timestamps.
     */
    function _date_diff($one, $two)
    {
        $invert = false;
        if ($one > $two) {
            list($one, $two) = array($two, $one);
            $invert = true;
        }

        $key = array("y", "m", "d", "h", "i", "s");
        $a = array_combine($key, array_map("intval", explode(" ", date("Y m d H i s", $one))));
        $b = array_combine($key, array_map("intval", explode(" ", date("Y m d H i s", $two))));

        $result = array();
        $result["y"] = $b["y"] - $a["y"];
        $result["m"] = $b["m"] - $a["m"];
        $result["d"] = $b["d"] - $a["d"];
        $result["h"] = $b["h"] - $a["h"];
        $result["i"] = $b["i"] - $a["i"];
        $result["s"] = $b["s"] - $a["s"];
        $result["invert"] = $invert ? 1 : 0;
        $result["days"] = intval(abs(($one - $two) / 86400));

        if ($invert) {
            $this->_date_normalize($a, $result);
        } else {
            $this->_date_normalize($b, $result);
        }

        return $result;
    }

    function MonthByNum($num)
    {
        switch ($num) {
            case '1' : return 'Enero'; break;
            case '2' : return 'Febrero'; break;
            case '3' : return 'Marzo'; break;
            case '4' : return 'Abril'; break;
            case '5' : return 'Mayo'; break;
            case '6' : return 'Junio'; break;
            case '7' : return 'Julio'; break;
            case '8' : return 'Agosto'; break;
            case '9' : return 'Septiembre'; break;
            case '10' : return 'Octubre'; break;
            case '11' : return 'Noviembre'; break;
            case '12' : return 'Deciembre'; break;
            default : return false;
        }
    }
    
    function MonthByNumShort($num)
    {
        switch ($num) {
            case '1' : return 'Enero'; break;
            case '2' : return 'Febr.'; break;
            case '3' : return 'Marzo'; break;
            case '4' : return 'Abril'; break;
            case '5' : return 'Mayo'; break;
            case '6' : return 'Junio'; break;
            case '7' : return 'Julio'; break;
            case '8' : return 'Agos.'; break;
            case '9' : return 'Sept.'; break;
            case '10' : return 'Oct.'; break;
            case '11' : return 'Nov.'; break;
            case '12' : return 'Dec.'; break;
            default : return false;
        }
    }    

    function DayOfWeek($num, $up = 0)
    {
        switch ($num) {
            case '1' : if ($up == 1) return 'Lunes'; else return 'lunes'; break;
            case '2' : if ($up == 1) return 'Martes'; else return 'martes'; break;
            case '3' : if ($up == 1) return 'Miércoles'; return 'miércoles'; break;
            case '4' : if ($up == 1) return 'Jueves'; else return 'jueves'; break;
            case '5' : if ($up == 1) return 'Viernes'; else return 'viernes'; break;
            case '6' : if ($up == 1) return 'Sábado'; else return 'sábado'; break;
            case '0' : if ($up == 1) return 'Domingo'; else return 'domingo'; break;
            default : return false;
        }
    }
    
    function DayOfWeekShort($num, $up = 0)
    {
        switch ($num) {
            case '1' : if ($up == 1) return 'Lunes'; else return 'lunes'; break;
            case '2' : if ($up == 1) return 'Mar.'; else return 'mar.'; break;
            case '3' : if ($up == 1) return 'Miér.'; return 'miérco.'; break;
            case '4' : if ($up == 1) return 'Juev.'; else return 'jueves'; break;
            case '5' : if ($up == 1) return 'Vier.'; else return 'viernes'; break;
            case '6' : if ($up == 1) return 'Sáb.'; else return 'sábado'; break;
            case '0' : if ($up == 1) return 'Dom.'; else return 'domin.'; break;
            default : return false;
        }
    }    

    function MonthsForForms()
    {
        $months = array();
        for ($i=1;$i<=12;$i++) {
            $months[$i] = $this->MonthByNum($i);
        }
        return $months;
    }

}