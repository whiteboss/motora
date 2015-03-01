<?php

class Post_Model_Table_Post extends Zend_Db_Table_Abstract {

    protected $_name = 'post_posts';
    protected $_rowClass = 'Post_Model_Post';
    protected $_feedTable = 'feed_feeds';
    protected $_usersTable = 'users';
    protected $_referenceMap = array(
        'Users' => array(
            'columns' => 'user_id',
            'refTableClass' => 'Application_Model_Table_Users',
            'refColumns' => 'id'
        ),
        'Report' => array(
            'columns' => array('gallery_id'),
            'refTableClass' => 'Events_Model_Table_PhotoReports',
            'refColumns' => array('id')
        ),
        'Rubric' => array(
            'columns' => 'rubric_id',
            'refTableClass' => 'Feed_Model_Table_Rubric',
            'refColumns' => 'id'
        ),
        'Company' => array(
            'columns' => 'company_id',
            'refTableClass' => 'Companies_Model_Table_Companies',
            'refColumns' => 'id'
        )
    );

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
    }

    // обнуление супер-новостей
    public function nullindex() {
        $select = $this->select()->where('is_main = 1');
        $rows = $this->fetchAll($select);

        foreach ($rows as $row) {
            $row->is_main = 0;
            $row->save();
        }
    }

    // обнуление фан-новостей
    public function nullfun() {
        $select = $this->select()->where('is_fun = 1');
        $rows = $this->fetchAll($select);

        foreach ($rows as $row) {
            $row->is_fun = 0;
            $row->save();
        }
    }

    public function addPost($params) {
        $this->_db->insert($this->_name, $params);

        return $this->_db->lastInsertId($this->_name);
    }

    public function getPostWithParams($params) {
        //$result = null;
        //$result = App_Cache_Cache::getInstance()->load("getPost".md5(implode('|',$params)));
        //if(!$result){
        $qs = $this->select()->setIntegrityCheck(false)->from(array("p" => $this->_name));

        if (isset($params['postId']))
            $qs->where("p.id = ?", $params['postId']);

        if (isset($params['feedId']))
            $qs->where("p.rubric_id = ?", $params['feedId']);

        if (!isset($params['isRemoveStatus']))
            $qs->where("p.status != ?", App_Data_Constants::$POST_STATUS_IS_REMOVE);

        if (isset($params['userId']))
            $qs->where("p.user_id = ?", $params['userId']);

        if (isset($params['tag'])) {
            $qs->where("p.tags REGEXP ?", "^" . $params['tag'] . ",");
            $qs->orWhere("p.tags REGEXP ?", "," . $params['tag'] . ",");
            $qs->orWhere("p.tags REGEXP ?", "," . $params['tag'] . "$");
            $qs->orWhere("p.tags REGEXP ?", $params['tag'] . "$");
        }

        $qs->joinLeft(array("u" => $this->_usersTable), "u.id = p.user_id", array("userFirstname" => "u.firstname", "userLastname" => "u.lastname"));

        //$qs->joinLeft(array("f" => $this->_feedTable), "f.id = p.feed_id", array("feedName" => "f.name"));

        $qs->order('date DESC');
        $result = $this->fetchAll($qs);
        //App_Cache_Cache::getInstance()->save($result, "getPost".md5(implode('|',$params)));
        //}
        return $result;
    }
    
    public function getPostByUrl($url) {

        $qs = $this->select()->setIntegrityCheck(true)->from(array("p" => $this->_name));

        $qs->where("p.url = ?", $url)->limit(1)->order('date DESC');

        $result = $this->fetchRow($qs);
        
        if ($result)        
            return $result;
        else
            return NULL;
    }  
    
    public function getPostsByIds($ids_post) {
        $items = array();
        $select = $this->select()->from($this, array('id', 'viewed'));
        
        $select->where('id IN (?)', $ids_post);
        
        $select->where('status = 1');

        //throw new Exception($select);
        $select->order('date DESC');
        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        return $items;
    }

    public function getPosts($feedId = null, $from = 0, $limit = 0, $video = true) {
        $items = array();
        $select = $this->select();
        if (!is_null($feedId)) {
            $select->where('rubric_id = ?', $feedId);
        }
        
        if (!$video) {
            $select->where('rubric_id <> 1');
        }
        
        $select->where('status = 1');

        if ($from > 0) {
            $select->limit(Post_Model_Post::$post_per_lazypage, $from);
        } elseif ($limit > 0) {
            $select->limit($limit);
        }

        //throw new Exception($select);
        $select->order('date DESC');
        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        return $items;
    }
    
    // матрица
    public function getCatalogPosts($feedId = 0, $limit = 0, $from = 0, $tag = null)
    {
        $data = array(); 
        $select = $this->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
            ->setIntegrityCheck(false)
            ->where('status = 1');
        
        if ($feedId > 0) {
            $select->where('rubric_id = ?', $feedId);
        }
        
        if (!is_null($tag)) {
            $select->where("tags REGEXP ?", "^" . $tag . ",");
            $select->orWhere("tags REGEXP ?", "," . $tag . ",");
            $select->orWhere("tags REGEXP ?", "," . $tag . "$");
            $select->orWhere("tags REGEXP ?", $tag . "$");
        }        
        
        if ($from > 0) {
            $select->limit(Post_Model_Post::$post_per_lazypage, $from);
        } elseif ($limit > 0) {
            $select->limit($limit);
        }

        $select->order('date DESC');

        //throw new Exception($select);
        $rows = $this->fetchAll( $select );

        // создадим матрицу постов, если постов на несколько строк
        if (count($rows) > 3) {
            $vert_size = ceil(count($rows) / 3);
            $col_limit = count($rows) - (($vert_size-1) * 3) - 1;  //($vert_size * 4) - count($rows) - 1; 
            $row_limit = $vert_size - 1;
            $row = 0;
            $col = 0;
            for ($i=0; $i < count($rows); $i++ ) {
                if ($row == $vert_size) {
                    $row = 0;
                    $col++;
                }
                // ограничение по столбцам
                if (($col > $col_limit) && ($row == $row_limit)) {
                    $row = 0;
                    $col++;                        
                }
                $ind = $col + $row*3;
                $data[$col][$row] = $rows[$ind];                                
                $row++;
            }
        } else {
            $j = 0;
            foreach ( $rows as $row ) {
                $data[$j][0] = $row;
                $j++;
            }    
        }                

        return $data; 

    }   

    public function getPostsByRubric($rubricId = null, $limit = 0, $from = 0, array $exludes_ids = null) {
        $items = array();
        $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false);
        //->joinInner( 'rating', 'rating.object_id = post_posts.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `rating` WHERE `rating`.object_id = `post_posts`.id AND `rating`.object_type = "post") AS post_rating') );

        if (count($exludes_ids) > 0) {
            $select->where('post_posts.id NOT IN (?)', $exludes_ids);
        }

        if ($rubricId > 0) {
            $select->where('post_posts.rubric_id = ?', $rubricId);
        }

        if ($from > 0) {
            $select->limit(Post_Model_Post::$post_per_lazypage, $from);
        } elseif ($limit > 0) {
            $select->limit($limit);
        }

        $select->order('date DESC');

        //throw new Exception($select);

        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        return $items;
    }
    
    public function getPostWithTags()
    {
        
        $items = array();

        $select = $this->select()->from($this, array('id', 'tags'))->where('tags != ""')->where('tags IS NOT NULL');
                
        //throw new Exception($select);
        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        
        return $items;        
        
    }   
    

    public function getMostCommentedPost($feedId = null, $limit = 0) {
        $items = array();

        $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinLeft('comments', 'post_posts.id = comments.object_id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `comments` WHERE `comments`.object_id = `post_posts`.id AND `comments`.object_type = "post") AS comment_count'))
                ->where('comments.object_type = "post"')
                ->where('TO_DAYS(NOW()) - TO_DAYS(post_posts.date) <= 7'); // за последнюю неделю

        if (!is_null($feedId)) {
            $select->where('rubric_id = ?', $feedId)->where('status = 1');
        }
        if ($limit > 0)
            $select->limit($limit);

        $select->order(array('comment_count DESC', 'date DESC'));
        //throw new Exception($select);
        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        return $items;
    }

    // главная
    public function getMainPost($limit = 0) { // $time in horas
        $items = array();

        $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinLeft('feed_feeds', 'feed_feeds.id = post_posts.rubric_id', null)
                //->where('feed_feeds.rubric_id = 9')                
                ->where('is_main = 1')
                //->where('TO_DAYS(NOW()) - TO_DAYS(post_posts.date) = 1')
                ->order('date DESC')
        ;

        if ($limit > 0)
            $select->limit($limit);

        //throw new Exception($select);
        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        return $items;
    }

    // лидеры по просмотрам за какое-то время
    public function getPopularPosts($limit = 0, $day = 7) { // $time in horas
        $items = array();

        $select = $this->select()
                ->from(array('p' => $this->_name), array('id', 'rubric_id', 'name', 'url', 'photo', 'date', 'SUM(cv.counter) as posts_views'))
                ->setIntegrityCheck(false)
                ->joinLeft(array('cv' => 'posts_view'), 'cv.id_post = p.id', null)
                ->where('p.rubric_id <> 1')
                ->where('TO_DAYS(NOW()) - TO_DAYS(cv.date) <= ?', $day)
                ->order('posts_views DESC')
                ->group(array('p.id'))
        ;

        if ($limit > 0)
            $select->limit($limit);

        //throw new Exception($select);
        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        return $items;
    }

    public function getPopularVideos($limit = 0, $day = 7) { // $time in horas
        $items = array();

        $select = $this->select()
                ->from(array('p' => $this->_name), array('id', 'rubric_id', 'name', 'photo', 'date', 'SUM(cv.counter) as posts_views'))
                ->setIntegrityCheck(false)
                ->joinLeft(array('cv' => 'posts_view'), 'cv.id_post = p.id', null)
                ->where('p.rubric_id = 1')
                ->where('TO_DAYS(NOW()) - TO_DAYS(cv.date) <= ?', $day)
                ->order('posts_views DESC')
                ->group(array('p.id'))
        ;

        if ($limit > 0)
            $select->limit($limit);

        //throw new Exception($select);
        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        return $items;
    }

    public function getMostViewedVideo($limit = 0) { // $time in horas
        $items = array();

        $select = $this->select()->where('rubric_id = 1')->order('date DESC');

        if ($limit > 0)
            $select->limit($limit);

        //throw new Exception($select);
        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        return $items;
    }
    
    public function getLastPosts($limit = 0, $json = false) {
        $items = array();
        $select = $this->select()
                ->from(array('p' => $this->_name), array('id', 'rubric_id', 'name', 'description', 'photo', 'date'))
                ->where('video IS NULL')->where('rubric_id <> 1')->where('status = 1');

        if ($limit > 0)
            $select->limit($limit);

        $select->order('date DESC');

        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            if ($json)
                $items[$row->id] = $row->name;
            else    
                $items[$row->id] = $row;
        
        return $items;
    }

    // выбираем 3 последних поста с картинками
    public function getLastVideo($limit = 0) {
        $items = array();
        $select = $this->select()->where('video IS NOT NULL')->where('rubric_id = 1')->where('status = 1');

        if ($limit > 0)
            $select->limit($limit);

        $select->order('date DESC');

        $rows = $this->fetchAll($select);
        foreach ($rows as $row)
            $items[$row->id] = $row;
        
        return $items;
    }

    public function createRow($data = array(), $defaultSource = null) {
        $new = parent::createRow($data, $defaultSource);
        $new->date = date_create()->format('Y-m-d H:i:s');
        return $new;
    }

}

