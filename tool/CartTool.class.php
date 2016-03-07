<?php 
/****
	waited
****/

/*
	购物车类
*/
defined('KEY') || exit();

class CartTool{
	private static $ins = null;
	private $items = array();

	final protected function __construct(){
	}
	final protected function __clone(){
	}

	//初始化
	protected static function getIns(){
		if(!(self::$ins instanceof self)){
			self::$ins = new self();
		}
		return self::$ins;
	}

	//SESSION中放入购物车
	public static function getCart(){
		if(!isset($_SESSION['cart']) || !($_SESSION['cart'] instanceof self)){
			$_SESSION['cart'] = self::getIns();
		}
		return $_SESSION['cart'];
	}

	//修改数量
	public function alterNum($goods_id,$num=1){
		if(! $this->hasItem($goods_id)){
			return false;
		}else{
			$this->items[$goods_id]['num'] = $num;
		}
	}

	//增加数量1
	public function addNum($goods_id,$num){
		if(!$this->hasItem($goods_id)){
			return false;
		}else{
			$this->items[$goods_id]['num'] += $num;
		}
	}

	//减少数量1
	public function removeNum($goods_id,$num){
		if(! $this->hasItem($goods_id)){
			return false;
		}else{
			$this->items[$goods_id]['num'] -= $num;

			if($this->items[$goods_id]['num']<1){
				$this->removeItem($goods_id);
			}
		}
	}

	//判断某商品是否存在
	public function hasItem($goods_id){
		return array_key_exists($goods_id, $this->items);
	}

	//添加商品
	public function addItem($goods_id,$goods_name,$price,$num=1){
		if($this->hasItem($goods_id)){
			$this->items[$goods_id]['num'] += $num;
			return;
		}

		$this->items[$goods_id] = array('name'=>$goods_name,'price'=>$price,'num'=>$num);
	}

	//删除商品
	public function removeItem($goods_id){
		unset($this->items[$goods_id]);
	}

	//清空购物车
	public function clearItme(){
		$this->items = array();
	}

	//查询购物车商品种类
	public function getItem(){
		return count($this->items);
	}

	//查询购物车商品个数
	public function getNum(){
		if($this->getItem() == 0){
			return 0;
		}else{
			$sum = 0;
			foreach ($this->items as $value) {
				$sum += $value['num'];
			}
			return $sum;
		}
	}

	//返回总价
	public function getMoney(){
		if($this->getItem() == 0){
			return 0;
		}else{
			$sum = 0;
			foreach ($this->items as $value) {
				$sum += $value['price']*$value['num'];
			}
			return $sum;
		}
	}

	//返回购物车中所有商品
	public function getItemAll(){
		return $this->items;
	}

	//获取数量以便判断是否超出库存
	public function getItemNum($goods_id){
		return $this->items[$goods_id]['num'];
	}
}

?>