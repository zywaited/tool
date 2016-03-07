<?php 
/****
	waited
****/

//防非法访问
defined('KEY') || exit('非法访问');

class GoodsModel extends Model{
	protected $table = 'goods';
	protected $key = 'goods_id';

	protected $field = array('goods_id','goods_sn','cat_id','brand_id','goods_name','shop_price','market_price','goods_number','click_count','goods_weight','goods_weight','goods_brief','goods_desc','thumb_img','goods_img','ori_img','is_on_sale','is_delete','is_best','is_new','is_hot','add_time','last_update');
	protected $auto = array(
                            array('is_hot','value',0),
                            array('is_new','value',0),
                            array('is_best','value',0),
                            array('add_time','function','time')
                            );
	protected $valid = array(
                            array('goods_name',1,'必须有商品名','require'),
                            array('cat_id',1,'栏目id必须是整型值','number'),
                            array('goods_weight',1,'商品重量必须是数值','number'),
                            array('shop_price',1,'本店价格必须是数值','number'),
                            array('market_price',1,'市场价格必须是数值','number'),
                            array('is_new',0,'is_new只能是0或1','in','0,1'),
                            array('goods_number',1,'商品数量必须是整型值','number'),
                            array('goods_brief',2,'商品简介应在5到100字符','length','5,100')
    						);

	private static $model = null;

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

	//回收站功能id_delete=1
	public function trash($id){
		return $this->update(array('is_delete'=>1),$id);
	}

	public function trashBck($id){
		return $this->update(array('is_delete'=>0),$id);
	}

	//获取is_delete=0的商品
	public function getGoods(){
		$sql='select * from goods where is_delete=0';
		return $this->db->getAll($sql);
	}

	//获取回收站商品
	public function getTrash(){
		$sql='select * from goods where is_delete=1';
		return $this->db->getAll($sql);
	}

	//创建商品货号
	public function createSn(){
		$goods_sn = 'WD'.date('Ymd').mt_rand(1000,99999);
		$sql = 'select count(*) from '.$this->table.' where goods_sn='.$goods_sn;

		return $this->db->getOne($sql)?$this->createSn():$goods_sn;
	}

	//取出指定的新品
	public function getNew($num=5){
		$sql = 'select goods_id,goods_name,shop_price,market_price,thumb_img from '.$this->table.' order by add_time limit 5';

		return $this->db->getAll($sql);
	}

	//获取栏目商品
	public function getcategoods($cat_id,$offset=0,$limit=5){
		$model = CatModel::getIns();
		$cats = $model->select();
		$sons = $model->getTree($cats,$cat_id);

		$sub = array($cat_id);
		if(! empty($sons)){
			foreach ($sons as $value) {
				$sub[] = $value['cat_id'];
			}
		}
		$in = implode(',', $sub);
		$sql = 'select goods_id,goods_name,shop_price,market_price,thumb_img from '.$this->table.' where cat_id in (' . $in . ') order by add_time limit '.$offset.','.$limit;
		return $this->db->getAll($sql);
	}

	//获取栏目商品的数量
	public function getcount($cat_id){
		$model = CatModel::getIns();
		$cats = $model->select();
		$sons = $model->getTree($cats,$cat_id);

		$sub = array($cat_id);
		if(! empty($sons)){
			foreach ($sons as $value) {
				$sub[] = $value['cat_id'];
			}
		}
		$in = implode(',', $sub);
		$sql = 'select count(*) from '.$this->table.' where cat_id in (' . $in . ')';
		return $this->db->getOne($sql);
	}

	//取出购物车指定的商品
	public function getInfo($item){
		/*
		$id = array();
		$id = array_keys($item);

		$sql = 'select goods_id,goods_name,thumb_img,shop_price,market_price from '.$this->table.' where goods_id in ('.implode(',', $id).')';

		return $this->db->getAll($sql);
		*/

		foreach ($item as $key => $value) {
			$sql = 'select goods_id,goods_name,thumb_img,shop_price,market_price from '.$this->table.' where goods_id='.$key;
			$row = $this->db->getRow($sql);

			$item[$key]['thumb_img'] = $row['thumb_img'];
			$item[$key]['market_price'] = $row['market_price'];
		}

		return $item;
	}

}

?>