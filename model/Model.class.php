<?php
	/**
	 *   基础的模型类
	 *   file：model.class.php
	 */

	//防止非法访问
	defined('WAITED') || exit('非法访问');

	class Model{
		//操作的表名
		protected $table = null;
		//数据库对象
		protected $db = null;
		//关键字
		protected $key = null;
		//字段名
		protected $field = array();
		//自动填充,形如array(array('字段名','类型','值'))
		protected $auto = array();
		//自动检测,形如array(array('字段名','类型','提示信息','要求'))
		protected $valid = array();
		//错误信息
		protected $error = array();

		/**
		 *   构造函数，实例化Mysql
		 */
		protected function __construct(){
			$this->db = Mysql::getIns();
		}

		/**
		 *   克隆
		 */
		protected function __clone(){}

		/**
		 *   修改表名函数
		 *   @param $table string 表名
		 */
		public function table($table){
			$this->table = $table;
		}

		/**
		 *   增加函数
		 *   @param $data array 数据
		 *   @return bool
		 */
		public function add($data){
			return $this->db->autoQuery($this->table,$data);
		}

		/**
		 *   删除函数
		 *   @param $data array 数据
		 *   @return int 影响的行数
		 */
		public function del($id){
			$str = 'delete from '.$this->table.' where '.$this->key.'='.$id;
			$this->db->query($str);
			return $this->db->affected_rows();
		}

		/**
		 *   修改函数
		 *   @param $data array 数据
		 *   @param $id int 关键字序号
		 *   @return int 影响的行数
		 */
		public function update($data,$id){
			$this->db->autoQuery($this->table,$data,'update',' where '.$this->key.'='.$id);
			return $this->db->affected_rows();
		}

		/**
		 *   获取所有数据函数
		 *   @return array
		 */
		public function select(){
			$str = 'select * from '.$this->table;
			return $this->db->getAll($str);
		}

		/**
		 *   获取一行数据函数
		 *   @param $id int 关键字序号
		 *   @return array
		 */
		public function findRow($id){
			$str = 'select * from '.$this->table.' where '.$this->key.'='.$id;
			return $this->db->getRow($str);
		}

		/**
		 *   格式化数据函数
		 *   @param $data array 数据
		 *   @return fix 成功返回数据,失败返回false
		 */
		public function format($data){
			$dt = array();
			foreach($data as $k =>$data){
				if(in_array($k, $this->field)){
					$dt[$k] = $data;
				}
			}

			//自动填充数据
			$dt = $this->autoFill($dt);
			//检测数据
			$dt = $this->test($dt);
			if(false == $dt){
				return false;
			}

			return $dt;
		}

		/**
		 *   自动填充数据函数
		 *   @param $data array 数据
		 *   @return array
		 */
		protected function autoFill($data){
			if(! empty($this->auto)){
				foreach ($this->auto as $auto) {
					if(! array_key_exists($auto[0], $data)){
						//判断类型
						switch ($auto[1]) {
							case 'value':
								$data[$auto[0]] = $auto[2];
								break;
							
							default:
								$data[$auto[0]] = call_user_func($auto[2]);
								break;
						}
					}
				}
			}
			return $data;
		}

		/**
		 *   检测数据函数
		 *   @param $data array 数据
		 *   @return fix 成功返回数据,失败返回false
		 */
		protected function test($data){
			if(! empty($this->valid)){
				foreach($this->valid as $dt){
					//判断类型
					switch ($dt[1]) {
						case 0:
							if(isset($data[$dt[0]])){
								if(! $this->check($data[$dt[0]],$dt[3],$dt[4])){
									$this->error[] = $dt[2];
									return false;
								}
							}
							break;

						case 1:
							if(! isset($data[$dt[0]])){
								$this->error[] = $dt[2];
								return false;
							}
							if(! $this->check($data[$dt[0]],$dt[3])){
								$this->error[] = $dt[2];
								return false;
							}
							break;

						case 2:
							if(! isset($data[$dt[0]]) || empty($data[$dt[0]])){
								$this->error[] = $dt[2];
								return false;
							}
							if(! $this->check($data[$dt[0]],$dt[3],$dt[4])){
								$this->error[] = $dt[2];
								return false;
							}
							break;
					}
				}
			}
			return $data;
		}

		/**
		 *   检测数据函数
		 *   @param $data array 数据
		 *   @param $rule string 规则
		 *   @param $path string 检测类型,默认为空
		 *   @return bool
		 */
		protected function check($data,$rule,$path=''){
			switch($rule) {
	            case 'require':
	                return !empty($data);

	            case 'number':
	                return is_numeric($data);

	            case 'in':
	                $tmp = explode(',',$path);
	                return in_array($data,$tmp);
	            case 'between':
	                list($min,$max) = explode(',',$path);
	                return $data >= $min && $data <= $max;
	            case 'length':
	                list($min,$max) = explode(',',$path);
	                $num = strlen($data);
	                return $num >= $min && $num <= $max;

	            case 'email':
	            	return filter_var($data,FILTER_VALIDATE_EMAIL) !== false;

	            default:
	                return false;
	        }
		}

		/**
		 *   获取最新添加的订单id
		 *   @return int
		 */
		public function getId(){
			return $this->db->insert_id();
		}

		/**
		 *   获取错误信息
		 *   @return array
		 */
		public function getError(){
			return $this->error;
		}
	}
?>
