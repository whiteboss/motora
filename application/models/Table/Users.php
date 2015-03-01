<?php

class Application_Model_Table_Users extends Zend_Db_Table_Abstract
{

    protected $_name = 'users';
    protected $_rowClass = 'Application_Model_User';
    protected $_primary = array('id');
    // зависмые таблицы
    protected $_dependentTables = array(
        'Feed_Model_Table_Feed',
        'Events_Model_Table_Walks',
        'Events_Model_Table_PhotoReports',
        'Post_Model_Table_Post',
        'Music_Model_Table_Musics',
        'Users_Model_Table_Restores'
    );

    public function isUnique($email) {
        $select = $this->_db->select()->from($this->_name)->where('email = ?', $email);
        $result = $this->getAdapter()->fetchOne($select);
        if ($result) {
            return TRUE;
        } else {
            return FALSE;

        }
    }

    public function register(array $user) {
        if ($user['password'] != $user['confirmPassword']) {
            $result = 'Пароли не совпадают';
            return $result;
        } else {
            $user['password'] = md5(md5($user['password']));
        }

        unset($user['confirmPassword']);
        $user['signup_date'] = date("Y-m-d H:i:s");
        $this->insert($user);
        $result = 'Вы успешно зарегистрировались! Можете <a href="user/signin">entrar</a>';
        return $result;
    }

    public function getUser($id)
    {
        $id = (int)$id;
        $row = $this->fetchRow('id = '. $id);
        if (!$row) {
            throw new Exception("Такой пользователь не существует");
        }
        return $row->toArray();
    }
    
    public function getUserByEmail($email)
    {        
        $select = $this->select(array('id', 'username', 'email'))->where('email = ?', $email)->limit(1);        
        $row = $this->fetchRow($select);
        if ($row) return $row; else return NULL;
    }
    
    // из соц. сетей
    public function getUserByUid($uid)
    {        
        $select = $this->select(array('id', 'username', 'uid'))->where('uid = ?', $uid)->limit(1);        
        $row = $this->fetchRow($select);
        if ($row) return $row; else return NULL;
    }    
    
    public function getAllUsers($from = 0, $limit = 0, $sex = 0)
    {
        $users = array();
        $select = $this->select()->where('rate >= 0')->where('is_deleted = 0')->where('is_confirmed = 1');
        
        if ($sex > 0)
            $select->where('sex = ?', (int) $sex);
        
        $select->order('rate desc');
        
        if ($from > 0) {            
            $select->limit(Application_Model_User::$user_per_lazypage, $from);
        } elseif ($limit > 0) {
            $select->limit($limit);
        }
        
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $users[ $row->id ] = $row;
	}
	return $users;
    }
    
    public function getBadUsers($from = 0, $limit = 0)
    {
        $users = array();
        $select = $this->select()->where('rate < 0')->order('rate asc');

        if ($from > 0) {            
            $select->limit(Application_Model_User::$user_per_lazypage, $from);
        } elseif ($limit > 0) {
            $select->limit($limit);
        }        
        
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $users[ $row->id ] = $row;
	}
	return $users;
    } 
    
    public function getPopularPeoples($limit = 6)
    {
        
//select b.username from
//(select u.username, sum(a.c_count) as c_count from
//(SELECT 
// ew.user_id
//, count(ew.event_id) as c_count
//FROM events_walks ew 
//inner join 
//   (SELECT id, name, start_date  FROM events_event
//   where (start_date >= NOW())) ee on (ee.id = ew.event_id)
//group by ew.user_id
// union all
//SELECT 
//user_id, COUNT(comment) as c_count
//FROM comments 
//where date between TO_DAYS(NOW()-7) and TO_DAYS(NOW())
//group by user_id) a
//inner join users u on (a.user_id = u.id)
//group by u.username) b
//order by b.c_count desc limit 6
        
        $items = Array(); 
        
        $db = $this->getAdapter();
        
        $subselect = $this->select()
                ->from(array('ew' => 'events_walks'), array('ew.user_id', 'COUNT(ew.event_id) as c_count'))
                ->setIntegrityCheck(false)
                ->joinInner(array('ee' => 'events_event'), 'ee.id = ew.event_id', null)
                ->where('TO_DAYS(end_date) >= TO_DAYS(NOW())')
                ->group('ew.user_id')
                ;
        
        $table = new Application_Model_Table_Comments();
        $subselect1 = $table->select()
                ->from(array('c' => 'comments'), array('user_id', 'COUNT(comment) as c_count'))
                ->setIntegrityCheck(false)
                ->where('published = 1')
                ->where('TO_DAYS(NOW()) - TO_DAYS(date) <= 7')
                ->group('user_id')
                ;
        
        $table = new Events_Model_Table_PhotoReports();
        $subselect2 = $table->select()
                ->from(array('ep' => 'events_photoreport'), array('user_id', 'COUNT(ep.id) as c_count'))
                ->setIntegrityCheck(false)
                ->where('is_confirmed = 1')
                ->where('TO_DAYS(NOW()) - TO_DAYS(ep.date) <= 7')
                ->group('user_id')
                ;
        
        $table = new Events_Model_Table_Events();
        $subselect3 = $table->select()
                ->from(array('ee' => 'events_event'), array('author_id as user_id', 'COUNT(ee.id) as c_count'))
                ->setIntegrityCheck(false)
                ->where('is_confirmed = 1')
                ->where('TO_DAYS(NOW()) - TO_DAYS(ee.date) <= 7')
                ->group('user_id')
                ;
        
        $table = new Music_Model_Table_Musics();
        $subselect4 = $table->select()
                ->from(array('m' => 'musics_music'), array('id_user as user_id', 'COUNT(m.id) as c_count'))
                ->setIntegrityCheck(false)
                //->where('TO_DAYS(NOW()) - TO_DAYS(m.date) <= 7')
                ->group('user_id')
                ;
        
        $table = new Post_Model_Table_Post();
        $subselect5 = $table->select()
                ->from(array('p' => 'post_posts'), array('user_id', 'COUNT(p.id) as c_count'))
                ->setIntegrityCheck(false)
                ->where('status = 1')
                //->where('TO_DAYS(NOW()) - TO_DAYS(m.date) <= 7')
                ->group('user_id')
                ;        
        
        $subselect30 = $db->select()->union(array($subselect, $subselect1, $subselect2, $subselect3, $subselect4, $subselect5), Zend_Db_Select::SQL_UNION_ALL);
        
        $subselect40 = $this->select()
                ->from(array('u' => 'users'), array('u.id', 'u.avatar', 'u.username', 'u.firstname', 'u.lastname', 'sum(a.c_count) as c_count'))
                ->joinInner( array('a' => new Zend_Db_Expr('('.$subselect30.')')), 'a.user_id = u.id', null )
                ->order('c_count DESC')
                ->group('u.username');

       
        $select = $this->select()
                ->from(array('b' => $subselect40), array('b.id', 'b.avatar', 'b.username', 'b.firstname', 'b.lastname', 'b.c_count'))
                //->where('b.id > 6')
                ->setIntegrityCheck(false)
                ;
        
        if ($limit > 0)
            $select->limit( $limit );
        
//        throw new Exception($select);
        $rows = $this->fetchAll($select);
        foreach ( $rows as $row ) {
            $items[] = $row;
	}
	return $items;        
        
    }    


    public function generateSalt() {
        $salt = '';
        $length = rand(5,10); // длина соли (от 5 до 10 сомволов)
        for($i=0; $i<$length; $i++) {
             $salt .= chr(rand(33,126)); // символ из ASCII-table
        }
        return $salt;
    }

     /**
     * Создание нового
     * @param  array $data OPTIONAL данные инициализации
     * @param  string $defaultSource OPTIONAL флаг указывающий установить значения по умолчанию
     * @return Application_Model_User
     */
    public function createRow( $data = array(), $defaultSource = null ) {
            $new = parent::createRow( $data, $defaultSource );
            $new->signup_date = date_create()->format( 'Y-m-d H:i:s' );
            return $new;
    }

}

