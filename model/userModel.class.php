<?php 
/****
	waited
****/

defined('KEY') || exit('非法访问');

class userModel extends Model{
	private static $model = null;
	protected $table = 'user';
	protected $key = 'user_id';

	protected $field = array('user_id','username','email','passwd','regtime','lastlogin');

	protected $auto = array(
                            array('regtime','function','time')
                            );

	protected $valid = array(
							array('username',1,'用户名必须6-16个字符','require'),
                            array('username',2,'用户名必须6-16个字符','length','6,16'),
                            //array('email',1,'email不能为空','require'),
                            array('email',1,'email非法','email'),
                            array('passwd',1,'密码不能位少于6','require'),
                            array('passwd',2,'密码不能位少于6','length','6,32')
    						);

	protected final function __construct(){
		parent::__construct();
	}

	//初始化
	public static function getIns(){
		if(self::$model instanceof self){
			return self::$model;
		}else{
			self::$model=new self();
			return self::$model;
		}
	}

	public function regPasswd($data){
		if($data['passwd']){
			$data['passwd'] = $this->endPasswd($data['passwd']);
		}
		return $this->add($data);
	}

	//加密
	public function endPasswd($passwd){
		return sha1($passwd);
	}

	 /*
    根据用户名查询用户信息
    */
    public function checkUser($username,$passwd='') {
        if($passwd == '') {
            $sql = 'select count(*) from ' . $this->table . " where username='" .$username . "'";
            return $this->db->getOne($sql);
        } else {
            $sql = "select user_id,username,email,passwd from " . $this->table . " where username='" . $username . "'";

            $row = $this->db->getRow($sql);

            if(empty($row)) {
                return false;
            }

            if($row['passwd'] != $this->endPasswd($passwd)) {
                return false;
            }

            unset($row['passwd']);
            return $row;
        }
    }
}

?>