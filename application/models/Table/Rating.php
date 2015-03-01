<?php

class Application_Model_Table_Rating extends Zend_Db_Table_Abstract
{

    protected $_name = 'rating';
    protected $_primary = array('id');

    protected $_referenceMap = array (
		'Users' => array (
			'columns' => 'user_id',
			'refTableClass' => 'Application_Model_Table_Users',
			'refColumns' => 'id'
		),
                'Post' => array (
			'columns' => 'object_id',
			'refTableClass' => 'Post_Model_Table_Post',
			'refColumns' => 'id'
		),
                'Ad' => array (
			'columns' => 'object_id',
			'refTableClass' => 'Auto_Model_Table_CarAds',
			'refColumns' => 'id'
		),
                'Map' => array (
			'columns' => 'object_id',
			'refTableClass' => 'Application_Model_Table_Map',
			'refColumns' => 'id'
		)        
    );
    
    public function getRatingWithParams($params)
    {
        
        $items = array();
        $select = $this->select()->from(array("r" => $this->_name), array('r.id', 'COUNT( IF(r.good=1,1,NULL) ) as good'));
        
        if(isset($params['user_id'])) $select->where("r.user_id = ?", $params['user_id']);        
        if(isset($params['object_id'])) $select->where("r.object_id = ?", $params['object_id']);        
        if(isset($params['object_type'])) $select->where("r.object_type = ?", $params['object_type']);        
        
        $select->group('id')->limit(1);

        $row = $this->fetchRow($select);
        if ($row && $row->good >= 0) {
            return $row;
        } else {
            return NULL;
        }
        
    }
    
    // http://habrahabr.ru/post/107685/
    protected function calckarma($karma_from, $karma_to)
    {
        $a = (int) $karma_from; // Рейтинг пользователя голосующего
        $b = (int) $karma_to;  // Рейтинг пользователя получающего голос

        if($b <= 0){ $b = 1; }
        ###	Получаем квадрат стороны А * 2	###
        $aInSquare = ($a * 2) * ($a * 2);

        /* Сейчас вы спросите, а почему мы увеличиваем значение переменной в 2 раза?
        Методом научного тыка, я увидел что когда у переменной $a число больше в 2 раза, то и результат получается более естественным */

        ###	Получаем квадрат стороны B	###
        $bInSquare = $b * $b;

        ###Получаем квадрат стороны гипотенузы	###
        $cInSquare = $bInSquare + $aInSquare;

        ###	Получаем длину гипотенузы###
        $c = sqrt($cInSquare);

        $result = (int) round($c / $b);
        /* Делим полученный результат гипотенузы на содержимое стороны $b и сводим значение в целое с помощью округления и превращения числа в int */

        if($result > $b / 2){ $result = (int) round($b / 2); }
        /* Проверим чтоб пользователю не прилетело слишком много баллов. Если ему поставл очень авторитетный человек, то карма максимум возрастет на 50% */

        return $result;            
    }
    
    public function calcUserKarma($user)
    {
        
        $items = array();
        
        // карма за посты
        $postManager = new Post_Model_Table_Post();
        $select = $postManager->select()->from($postManager, array('id'))
                ->setIntegrityCheck(false)                
                ->joinInner( 'rating', 'rating.object_id = post_posts.id', array('good', 'poor', 'date') )
                ->joinLeft( 'users_karma', 'users_karma.id_user = rating.user_id', new Zend_Db_Expr('(SELECT karma FROM `users_karma` WHERE `users_karma`.id_user = `rating`.user_id AND `users_karma`.date <= `rating`.date ORDER BY `users_karma`.date DESC LIMIT 1) AS karma_from') )
                ->where( 'rating.object_type = "post"' )
                ->where( 'post_posts.user_id = ?', $user->id )
                ->order( 'rating.date ASC' )
                ->group( 'post_posts.id' )
                ;
                //throw new Exception($select);
                
        $rows = $this->fetchAll($select);
        //throw new Exception($karma);
        
        foreach ($rows as $row) {            
            //throw new Exception($userkarma);
            if ($row->good == 1)
                $karma = $karma + $this->calcKarma($row->karma_from, $user->getKarma($row->date));
            else
                $karma = $karma - $this->calcKarma($row->karma_from, $user->getKarma($row->date));
                
            // сохраним карму
            //if ($karma != 0) {                        
                // проверим была ли карма на данный день
                $new_karma = $table->getKarmaWithParams(array('id_user' => $user->id, 'date' => $row->date));
                if (is_null($new_karma)) {
                    $new_karma = $table->createRow();                
                }       
                $new_karma->date = $row->date;
                $new_karma->id_user = $user->id;
                $new_karma->karma = $karma;
                $new_karma->save();                
                //throw new Exception();
            //}
            
            // снимем карму и у голосующего
            //$who_rated
        }    
        
        // обновим сразу рейт
        $user->rate = (int) $table->getKarmaWithParams(array('id_user' => $user->id))->karma;
        $user->save();
        
    }

    public function createRow( $data = array(), $defaultSource = null ) {        
        $new = parent::createRow( $data, $defaultSource );
        $new->date = date_create()->format( 'Y-m-d H:i:s' );
        return $new;
    }    
    
}