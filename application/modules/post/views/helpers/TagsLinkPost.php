<?php

class Post_View_Helper_TagsLinkPost extends Zend_View_Helper_Abstract 
{
   public function tagsLinkPost($tags)
   {
       $tags = explode(",", $tags);
       $tagStr = "";
       foreach ($tags as $tag) {
           if($tagStr != "") $tagStr .="";
           if(trim($tag)!="")
           {
               $tagStr .= '<a class="Qtag lnone" href="' . $this->view->url(array('tag' => $tag), 'postbytag') . '">' . trim($tag) . '</a>';
           }
       }
       return $tagStr; 
   }
}

?>
