<?php
	/**
	 *  封装一个分页类
	 *  file: PageTool.class.php
	 */

	//防止非法访问
	defined('WAITED') || exit('非法访问');

	class PageTool{
		//总的数量
		protected $total;
		//一页显示的数量,默认10
		protected $perPage = 10;
		//当前页数
		protected $page = 1;
		//显示的页码数量
		protected $pageNum = 5;
		//错误信息
		protected $error = array(
					0=>'没有错误信息',
					1=>'总数量必须是正整数',
					2=>'还没有对相关信息进行设置，请先使用set'
			);
		//错误信息索引
		protected $index = 0;
		//判断是否已合理进行设置
		protected $success = false;

		/**
		 *  设置函数
		 *  @param $total int 总的数量
		 *  @param $change bool 默认不修改后续内容,false
		 *  @param $page int 当前页数,默认1
		 *  @param $perPage int 一页显示的数量,默认10
		 *  @param $pageNum int 显示的页码数量,默认5页
		 *  @return bool
		 */
		public function set($total,$chage=false,$page=1,$perPage=10,$pageNum=5){
			//判断total是否合法
			if(! is_numeric($total) || $total < 0){
				$this->index = 1;
				return false;
			}else{
				$this->total = $total;
			}
			//判断是否需要修改
			if($chage){
				//判断page是否合法
				if(is_numeric($page) || $page > 0){
					$this->page = $page;
				}
				//判断perPage是否合法
				if(is_numeric($perPage) || $perPage > 0){
					$this->perPage = $perPage;
				}
				//判断pageNum是否合法
				if(is_numeric($pageNum) || $pageNum > 0){
					$this->pageNum = $pageNum;
				}
			}
			$this->success = true;
			return true;
		}

		/**
		 *  获取错误信息
		 *  @return string
		 */
		public function getError(){
			return $this->error[$this->index];
		}

		/**
		 *  获得总的页数
		 *  @return int
		 */
		public function getPage(){
			return ceil($this->total/$this->perPage);
		}

		/**
		 *  显示分页
		 *  @return fix
		 */
		public function show(){
			//判断是否合理设置
			if(! $success){
				$this->index =2;
				return false;
			}
			//获取页数
			$cnt = $this->getPage();
			//获取URI
			$uri = $_SERVER['REQUEST_URI'];
			$parse = parse_url($uri);
			//记录参数数组
			$param = array();
			//判断uri是否有参数
			if(isset($parse['query'])){
				$param = parse_str($parse['query'],$param);
			}
			//判断是否存在page参数
			if(isset($param['page'])){
				unset($param['page']);
			}
			//创建分页后的链接
			$url = $parse['path'].'?';
			//判断参数数组是否为空
			if(! empty($param)){
				$param = http_build_query($param);
				$url .= $param . '&';
			}
			//计算当前导航页码
			$nav = array();
			$nav[] = '<span class="page_now">' . $this->page . '</span>';
			//其他的页码导航
			for($left=$this->page-1,$right=$this->page+1 ; ($left>=1 || $right<=$cnt) && count($nav)<$this->pageNum;){
				if($left>=1){
					array_unshift($nav,'<a href="' . $url . 'page=' . $left . '">[' . $left . ']</a>');
                	$left -= 1;
				}
				if($right<=$cnt){
					array_push($nav,'<a href="' . $url . 'page=' . $right . '">[' . $right . ']</a>');
                	$right += 1;
				}
			}
			//返回分页
			return implode('', $nav);
		}
	}
?>