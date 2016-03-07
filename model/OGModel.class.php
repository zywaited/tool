<?php 
/****
	waited
****/

//防非法访问
defined('KEY') || exit('非法访问');


class OGModel extends Model {
    protected $table = 'ordergoods';
    protected $pk = 'og_id';

    // 把订单的商品写入ordergoods表
    public function addOG($data) {
        if($this->add($data)) {
            $sql = 'update goods set goods_number = goods_number - ' . $data['goods_number'] . ' where goods_id = ' . $data['goods_id'];

            return $this->db->query($sql); // 减少库存
        }

        return false;

    }

    private static $model = null;

	protected final function __construct(){
		parent::__construct();
	}
	protected function __clone(){}

	//初始化
	public static function getIns(){
		if(self::$model instanceof self){
			return self::$model;
		}else{
			self::$model=new self();
			return self::$model;
		}
	}

}



?>