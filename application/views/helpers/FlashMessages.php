<?php

class Zend_View_Helper_FlashMessages extends Zend_View_Helper_Abstract
{
    public function flashMessages()
    {
        $messages = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->getMessages(); 
        $output = '';
        
        //die(print_r($messages));
        
        if (count($messages) > 0) {
            $output .= '<script>
        $(document).ready(function() {
            $("#system_message_cont").delay(2000).fadeOut(1500);
        });
        </script>
        <div id="system_message_cont">
        
            ';
            foreach ($messages as $message) {
                $output .= '<span class="' . key($message) . '">' . current($message) . '</span>';
            }            
            $output .= '</div>';            
        }
        
        return $output;
    }
}

?>
