<?php

class Application_Views_Helper_dateFormatComments extends Zend_View_Helper_Abstract 
{
   public function dateFormatComments($date)
   {
       
       $dateDB = date("Y-m-d", strtotime($date));
       $dateNow = date("Y-m-d", time());
       if(strtotime($dateDB) == strtotime($dateNow)) {
            $date = explode(" ", $date);
            $time = explode(":", $date[1]);                  
            $result = "Hoy, ".$time[0].":".$time[1];
       } else if((strtotime($dateNow) - strtotime($dateDB)) == 60*60*24) {
            $date = explode(" ", $date);
            $time = explode(":", $date[1]);        
            $result = "Ayer, ".$time[0].":".$time[1];
       }
       
       if(!isset($result) || !$result)
       {
           $date = explode(" ", $date);
           $date = explode("-", $date[0]);
      
           $month = array();
           $month['01'] = "enero";
           $month['02'] = "febrero";
           $month['03'] = "marzo";
           $month['04'] = "abril";
           $month['05'] = "mayo";
           $month['06'] = "junio";
           $month['07'] = "julio";
           $month['08'] = "agosto";
           $month['09'] = "septiembre";
           $month['10'] = "octubre";
           $month['11'] = "noviembre";
           $month['12'] = "deciembre";
       
           $result = (int) $date[2]." ".$month[$date[1]]." ".$date[0];
       }       
       
       return $result; 
   }
}

?>
