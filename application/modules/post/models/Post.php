<?php

class Post_Model_Post extends Post_Model_Abstract
{
    protected $_user;
    protected $_rubric;
    protected $_feed;
    protected $_company;
    
    public static $qlick_news = array('1', '5', '7', '8', '9');
    
    public static $post_index_size = array('post_size_big' => 'Tamaño grande', 'post_size_mid' => 'Tamaño medio', 'post_size_small' => 'Tamaño pequeño');    

    public static $post_status = array('0' => 'No publicado', '1' => 'Publicado');
    
    public static $post_per_lazypage = 24;
    public static $limit_photos = 20;
    
    public function calcRating()
    {
        $table = new Application_Model_Table_Rating();
        
        $rating = $table->getRatingWithParams(array('object_id' => $this->id, 'object_type' => 'post'));        
        
        $this->rating = $rating;
        $this->save();
    }

    /**
     * @return string
     */
    public function __toString() {
        return (string) $this->name;
    }
    
    public function getUrl()
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        if (!is_null($this->url))
            if ($this->rubric_id == 1)
                return $viewRenderer->view->url(array('post_url' => $this->url), 'video_seo');
            else
                return $viewRenderer->view->url(array('post_url' => $this->url), 'post_seo');
        else
            return $viewRenderer->view->url(array('postId' => $this->id, 'action' => 'view'), 'post');
        
    }        

    public function getUser()
    {
        if ( !empty($this->_user) ) return $this->_user;
        $this->_user = $this->findParentRow( 'Application_Model_Table_Users' );
        return $this->_user;
    }
    
    public function getFeed()
    {
        if ( !empty($this->_feed) ) return $this->_feed;
        $this->_feed = $this->findParentRow( 'Feed_Model_Table_Feed' );
        return $this->_feed;
    }
    
    public function getRubric()
    {
        if ( !empty($this->_rubric) ) return $this->_rubric;
        $this->_rubric = $this->findParentRow( 'Feed_Model_Table_Rubric' );
        return $this->_rubric;
    }    
    
    public function getCompany()
    {
        if ( !empty($this->_company) ) return $this->_company;
        $this->_company = $this->findParentRow( 'Companies_Model_Table_Companies' );
        return $this->_company;
    } 
    
    public function getIndexClass()
    {
        if (!is_null($this->type_photo_index)) {
            return $this->type_photo_index;
        } else {
            return 'post_size_small';
        }                
    }
    
    public function getIndexPhoto()
    {
        if (!is_null($this->photo_index)) {
            return $this->photo_index;
        } else {
            return NULL;
        }                
    }
    
    public function getPhoto()
    {
        if (!is_null($this->photo_list)) {
            return $this->photo_list;
        } else {
            if (!is_null($this->photo)) {
                return $this->photo;
            } else {
                return NULL;
            }
        }                
    }
    
    public function getMainPhoto()
    {
        if (!is_null($this->photo)) {
            return $this->photo;
        } else {
            return NULL;
        }                
    }    
    
    public function getVideoPoster()
    {
        if (!is_null($this->photo)) {
            //$photo = json_decode($this->photo);
            $poster = Zend_Registry::get('upload_path') . '/post/video_' . $this->photo;
            if (file_exists($poster))
                return '/files/post/video_' . $this->photo;
            else
                return '/zeta/prevideo.jpg';
        } else {
            return NULL;
        }                
    }    
    
    public function getPhotos()
    {
        if (!is_null($this->photos)) {
            return json_decode($this->photos, true);
        } else {
            return NULL;
        }         
    }
    
    public function inNoticias() {
        $rows = $this->findDependentRowset('Application_Model_Table_Noticias', 'Post', $this->select()->where('id_type = ?', $this->id)->where('type = "post"')->limit(1));  
        if (count($rows) > 0) 
            return $rows->current()->id; 
        else
            return false;
    }    
    
    public function count()
    {
        
        $table = new Post_Model_Table_Views();
        $select = $table->select( Zend_Db_Table::SELECT_WITH_FROM_PART )->setIntegrityCheck(false)->where('id_post = ?', $this->id)->where('date = ?', date('Y-m-d'))->limit(1);
        
        $counter = $table->fetchRow($select);
        
        // система накрутки
        $delta_days = ceil( (time() - mktime(
                                date('H', strtotime($this->date)), date('i', strtotime($this->date)), 0,
                                date('m', strtotime($this->date)), date('d', strtotime($this->date)), date('Y', strtotime($this->date))) ) / 86400 );
        
        if ($delta_days <= 4) {
            srand((float) microtime() * 10000000);
            $boost_counter = rand(1, 4);
        } else {
            $boost_counter = 1;
        }
        
        if ($counter) {
            $counter->counter = $counter->counter + $boost_counter;
            $counter->save();
        } else {
            $counter = $table->createRow( array('id_post' => $this->id, 'counter' => $boost_counter) );
            $counter->save();
        }
        
        $this->viewed = $this->viewed + $boost_counter;
        
        $this->save();
        return $this;
    }
    
    public function getTags() {
        if (!is_null($this->tags))
            return explode(',', trim($this->tags));
        else
            return NULL;
    }     
    
    public function getDate() {
        
    }

    public function commentCount() {
        $rows = $this->findDependentRowset('Application_Model_Table_Comments', null, $this->select()->where("object_type = 'post'")->where('published = 1'), array('id'));
        if (count($rows) > 0) 
            return count($rows) + $this->fb_comments; 
        else 
            if (!is_null($this->fb_comments))
                return $this->fb_comments;
            else
                return NULL;
    }
    
    public function shareCount() {
        if (!is_null($this->fb_shares))
            return $this->fb_shares;
        else
            return NULL;
    }    

    public function isLiked( $user_id ) {
        $rows = $this->findDependentRowset('Application_Model_Table_Rating', 'Post', $this->select()->where('user_id = ?', $user_id)->where("object_type = 'post'"));
        return (bool) count($rows);
    }

    public function isFavorite( $user_id ) {
        $rows = $this->findDependentRowset('Post_Model_Table_Favorites', null, $this->select()->where('user_id = ?', $user_id));
        if (count($rows)) return $rows->current(); else return NULL;
    }
    
    /**
     * Возвращает данные в массиве колонка=>значение
     * @return array
    */
    public function toArray() {
            $data = parent::toArray ();
            $data['id'] = (int) $data['id'];
            return $data;
    }    

}

