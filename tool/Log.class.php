<?php
	/**
	 * 封装一个日志记录类，
	 * 用于记录程序的运行状况，
	 * 并记录在文件和数据库中
	 * file:log.class.php
	 */

	//防止非法访问
	defined('WAITED') || exit('非法访问');

	class Log{
		//日志文件相关信息
		private $logFile = 'current.log';
		private $logDir = './data/log/';
		//文件备份的大小
		private $logSize = 1;
		//判断是否已经存在
		private $exist = true;
		//单一对象
		private static $LOG = null;

		/**
		 *   单一模式
		 */
		private final function __construct(){}
		private function __clone(){}

		/**
		 *   单一模式
		 *   对外的单一接口
		 */
		public static function getIns(){
			if(self::$LOG instanceof self){
				return self::$LOG;
			}else{
				self::$LOG = new self();
				return self::$LOG;
			}
		}

		/**
		 *  日志文件相关信息修改函数
		 *  @param $logDir fix 当为string 表示文件路径;如果为array,则根据数组赋值,形如array('logFile'=>)
		 *  @param $logFile string 文件名称
		 *  @return bool
		 */
		public function set($logDir = false,$logFile = false,$logSize = false){
			if(is_array($logDir)){
				foreach ($logDir as $k => $data) {
					if($this->$k){
						$this->$k = $data;
					}else{
						return false;
					}
				}
			}else{
				$this->logDir = $logDir;
				$this->logFile = $logFile;
				$this->logSize = $logSize;
			}
			return true;
		}

		/**
		 *  日志内容记录函数
		 *	@param $content string 需要记录的内容
		 *  @return bool
		 */
		public static function write($content){
			//判断是否为目录
			if(! $this->isDir()){
				return false;
			}

			//判断是否需要备份
			$time = time()+8*3600;
			$log = $this->isBak($time);
			if($log){
				$content = gmdate('Ymd H:i',$time)."\t".$content."\r\n";
				$fh=fopen($log, 'ab');
				fwrite($fh, $content);
				fclose($fh);
				return true;
			}
			return false;
		}

		/**
		 *  判断目录是否存在,不存在就自动生成
		 *  @return bool
		 */
		private function isDir(){
			if(! is_dir($this->logDir)){
				if(! @mkdir($this->logDirr,0777,true)){
					return false;
				}
			}
			return true;
		}

		/**
		 *  判断文件是否存在,不存在就自动生成
		 *  @param $filename string 文件路径，默认不存在
		 *  @return fix 如果失败则返回false，成功返回路径
		 */
		private function isFile($filename=false){
			if(false == $filename)
				$filename = $this->logDir.$this->logFile;
			if(! file_exists($filename)){
				if(! @touch($filename)){
					return false;
				}
				$this->exist = false;
			}
			return $filename;
		}

		/**
		 *  获取文件大小，判断是否需要备份函数
		 *	@param $time string 记录的时间
		 *  @return fix 文件路径存在，返回$log,否则返回false;
		 */
		private function isBak($time){
			//判断文件是否存在
			$log = $this->isFile();
			if($log){
				if(! $this->exist){
					$this->exist = true;
					return $log;
				}

				//清楚缓存
				clearstatcache(true,$log);

				//获取文件大小
				$size = filesize($log);

				if($size <= $this->logSize){
					return $log;
				}

				//备份
				if(! $this->bak($log,$time)){
					return $log;
				}else{
					if($this->isFile($log)){
						$this->exist = true;
						return $log;
					}
					return false;
				}
			}
		}

		/**
		 *  获备份函数
		 *  @param $filename string 文件路径
		 *	@param $time string 记录的时间
		 *  @return bool
		 */
		public function bak($filename,$time){
			$log = $this->logDir.gmdate('YmdHi',$time).'.bak';
			return rename($filename, $log);
		}
	}
?>