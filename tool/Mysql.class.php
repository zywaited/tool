<?php
	/**
	 *  封装一个数据库类，
	 *  完成mysql的相关操作
	 *  file:Mysql.class.php
	 */

	//防非法访问
	defined('WAITED') || exit('非法访问');

	class Mysql{
		//初始数据
		private $conf;
		//数据库对象
		private $con;
		//单一对象
		private static $conn = null;

		/**
		 *   单一模式
		 */
		private final function __construct(){
			$this->conf = require_once(ROOT.'include/config.inc.php');
			//连接数据库
			try{
				$this->connect($this->conf->host,$this->conf->user,$this->conf->password);
				$this->chooseDb($this->conf->db);
				$this->setChar($this->conf->char);
			} catch(Exception $e){
				$result = $e->getMessage();
				Log::write($result);
			}
		}
		private function __clone(){}

		/**
		 *   单一模式
		 *   对外的单一接口
		 */
		public static function getIns(){
			if(self::$conn instanceof self){
				return self::$conn;
			}else{
				self::$conn = new self();
				return self::$conn;
			}
		}

		/**
		 *   连接数据库
		 *   @param $host string 主机名
		 *   @param $user string 用户名
		 *   @param $password strign 密码
		 */
		private function connect($host,$user,$password){
			$this->con = mysql_connect($host,$user,$password);
			if(! $this->con){
				$e = new Exception('系统正在维护中');
				throw $e;
			}
		}

		/**
		 *   选择数据库数据库
		 *   @param $db string 数据库名
		 */
		private function chooseDb($db){
			return mysql_select_db($db,$this->con);
		}

		/**
		 *   选择数据库
		 *   @param $char string 字符集
		 */
		private function setChar($char){
			$str = 'set names '.$char;
			return $this->query($str);
		}

		/**
		 *   发送语句
		 *   @param $str string 发送语句
		 *   @return fix 失败返回false;成功返回资源
		 */
		public function query($str){
			//记录语句
			Log::write($str);
			//发送到数据库
			$rs = mysql_query($str,$this->con);
			//如果出错就记录
			if(! $rs){
				Log::write('语句出错');
				return false;
			}
			return $rs;
		}

		/**
		 *   获取表中的所有数据
		 *   @param $str string 发送语句
		 *   @return array 
		 */
		public function getAll($str){
			$rs = $this->query($str);
			if(! $rs){
				return array();
			}
			$data = array();
			while($row = mysql_fetch_assoc($rs)){
				$data[] = $row;
			}
			return $data;
		}

		/**
		 *   获取表中的一行数据
		 *   @param $str string 发送语句
		 *   @return array 
		 */
		public function getRow($str){
			$rs = $this->query($str);
			if(! $rs){
				return array();
			}
			return mysql_fetch_assoc($rs);
		}

		/**
		 *   获取表中的一行中的第一个数据
		 *   @param $str string 发送语句
		 *   @return fix 
		 */
		public function getOne($str){
			$rs = $this->query($str);
			if(! $rs){
				return array();
			}
			$data = mysql_fetch_row($rs);
			return $data[0];
		}

		/**
		 *   自动解析数据并执行insert、update函数
		 *   @param $table string 表名
		 *   @param $data array 需要执行的数据
		 *   @param $model string 模型，默认insert
		 *   @param $where string 限制条件,默认where 1 limit 1
		 *   @return bool
		 */
		public function autoQuery($table,$data,$model = 'insert',$where = ' where 1 limit 1'){
			if(is_array($data)){
				return false;
			}
			//解析数据
			if('update' == $model){
				$str = 'update '.$table.' set ';
				foreach ($data as $k => $data) {
					$str .= $k."='".$v."',";
				}
				$str = rtrim($str);
				$str .= $where;
			}else if('insert'== $model) {
				$str = 'insert into '.$table.'('.implode(',', array_keys($data)).') values(\''.implode(',', array_values($data)).'\')';
			}else{
				return false;
			}
			return $this->query($str);
		}

		/**
		 *   返回影响行数的函数
		 *   @return int
		 */
	    public function affected_rows() {
	        return mysql_affected_rows($this->con);
	    }

	    /**
		 *   返回最新的auto_increment列的自增长的值
		 *   @return int
		 */ 
	    public function insert_id() {
	        return mysql_insert_id($this->con);
	    }
	}
?>