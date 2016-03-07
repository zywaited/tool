<?php 
/****
	waited
****/

//防非法访问
defined('KEY') || exit('非法访问');

class OrderModel extends Model{
    protected $table = 'orderinfo';
    protected $key = 'order_id';
	protected $field = array('order_id','order_sn','user_id','username','zone','address','zipcode','reciver','email','tel','mobile','building','best_time','add_time','order_amount','pay');

    protected $valid = array(
                            array('reciver',1,'收货人不能为空','require'),
                            array('address',1,'收货地址不能为空','require'),
                            array('email',1,'email非法','email'),
                             array('pay',1,'必须选择支付方式','require'),
                            array('pay',0,'必须选择支付方式','in','4,5') //代表在线支付与到付.
    );

    protected $auto = array(
                            array('add_time','function','time')
                            );
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

    public function orderSn() {
        $sn = 'OI' . date('Ymd') . mt_rand(10000,99999);
        $sql = 'select count(*) from ' . $this->table  . ' where order_sn=' . "'$sn'";
        return $this->db->getOne($sql)?$this->orderSn():$sn;
    }

    //下订单失败
    public function back($order_id) {
        $this->del($order_id); // 先删掉订单

        $sql = 'delete from ordergoods where order_id = ' . $order_id; // 再删订单对应的商品
        
        return $this->db->query($sql);
    }
}

?>