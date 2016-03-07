<?
	/**
	 *  文件上传封装类
	 *  file: upload.class.php
	 */

	//防止非法访问
	defined('WAITED') || exit('非法访问');

	class Upload{
		//默认保存目录
		protected $dir = 'data/upload';
		//上传文件类型
		protected $ext = array('jpg','jpeg','gif','bmp','png');
		//规定上传最大值
		protected $size = 5;
		//错误信息索引
		protected $index = 0;
		//文件错误信息
		protected $error = array(
							0=>'文件上传成功',
					        1=>'上传文件超出系统限制',
					        2=>'上传文件大小超出网页表单页面',
					        3=>'文件只有部分被上传',
					        4=>'没有文件被上传',
					        6=>'找不到临时文件夹',
					        7=>'文件写入失败',
					        8=>'不允许的文件后缀',
					        9=>'文件大小超出的类的允许范围',
					        10=>'创建目录失败',
					        11=>'移动失败',
					        12=>'名称长度过大'
						);

		/**
		 *   文件上传
		 *   @param $key string 上传的文件的键值
		 *   @param $type bool 是否保留原文件名,默认不
		 *   @param $length int 不保留原名情况下选择名称长度
		 *   @return fix 成功返回相对路径，失败返回false
		 */
		public function up($key,$type = false,$length = 6){
			//判断文件是否存在
			if(! isset($_FILES[$key])){
				$this->index = 4;
				return false;
			}
			$file = $_FILES[$key];
			//判断上传是否成功
			if($file['error']){
				$this->index = $file['error'];
				return false;
			}
			//获取文件类型
			$ext = $this->getExt($file['name']);
			//判断是否类型合法
			if(! $this->isExt($ext)){
				$this->index = 8;
				return false;
			}
			//判断大小是否合法
			if(! $this->isSize($file['size'])){
				$this->index = 9;
				return false;
			}
			//创建目录
			$time = time()+3600*8;
			$dir = $this->makeDir($time);
			if(false == $dir){
				$this->index = 10;
				return false;
			}
			//判断是否保留原名
			if(! $type){
				//验证长度是否合法
				if(! is_numeric($length)){
					$length = 6;
				}
				$newName = $this->randName($length,$time);
				if(false == $newName){
					$this->index = 12;
					return false;
				}
				$dir .= '/'.$newName.$ext;
			}else{
				$dir = $dir.'/'.$file['name'];
			}
			//移动
			if(! move_uploaded_file($file['tmp_name'], $dir)){
				$this->index = 11;
				return false;
			}
			return str_replace(ROOT, '', $dir);
		}

		/** 
		 *   修改保存路径
		 *   @param $dir sting 路径
		 */
		public function setDir($dir){
			$this->dir = $dir;
		}

		/** 
		 *   创建保存路径
		 *   @param $time data 时间
		 *   @return fix 成功返回路径，失败返回false
		 */
		protected function makeDir($time){
			$dir = ROOT.$this->dir.gmdate('Ym/d',$time);
			if(is_dir($dir) || @mkdir($dir,0777,true))
				return $dir;
			return false;
		}

		/** 
		 *   修改上传最大值
		 *   @param $size int 允许上传的最大值
		 */
		public function setSize($size){
			$this->size = $size;
		}

		/**
		 *   判断上传文件大小是否合法
		 *   @param $size string 上传的文件大小
		 *   @return bool
		 */
		protected function isSize($size){
			return $size <= ($this->size)*1024*1024;
		}

		/**
		 *   修改上传文件类型
		 *   @param $ext array 允许上传的类型（索引数组）
		 *   @param $type string 修改类型,add表示增加，delete表示删除,change表示替换
		 *   @return bool
		 */
		public function setExt($ext,$type='change'){
			if(is_array($ext)){
				if('change' == $type){
					$this->ext = $ext;
				}else if('add' == $type){
					$this->ext = array_merge($this->ext,$ext);
				}else if('delete' == $ext){
					foreach ($ext as $k => $data) {
						if($index = array_search($data, $this->ext) >=0){
							unset($this->ext[$index]);
						}
					}
				}
				return true;
			}
			return false;
		}

		/**
		 *   获取上传文件类型
		 *   @param $file string 上传的文件名
		 *   @return string 返回类型
		 */
		protected function getExt($file){
			return trim(strrchr($file, '.'),'.');
		} 

		/**
		 *   判断上传文件类型是否合法
		 *   @param $ext string 上传的文件类型
		 *   @return bool
		 */
		protected function isExt($ext){
			return in_array(strtolower($ext), $this->ext);
		}

		/**
		 *   随机名称
		 *   @param $length int 名称长度
		 *   @param $time data 时间
		 *   @return fix 成功返回随机名称，失败返回false
		 */
		protected function randName($length=6,$time){
			$str = 'abcdefghijklmnopqrstuvwxyz0123456789';
			if($length > strlen($str))
				return false;
			return gmdate('H-i',$time).substr(str_shuffle($str),$length);
		}

		/**
		 *   获取错误信息
		 *   @return string
		 */
		public function getError(){
			return $this->error[$this->index];
		}
	}
?>