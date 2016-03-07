<?php 
/****
	waited
****/

//防非法访问
defined('KEY') || exit('非法访问');

class CatModel extends Model{
	protected $table = 'category';

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

	//删除分类
	public function add($data){
		return $this->db->autoQuery($this->table,$data);
	}

	//取出分类
	public function select(){
		$sql='select cat_id,cat_name,parent_id from '.$this->table;
		return $this->db->getAll($sql);
	}

	//取出一行
	public function findOne($cat_id){
		$sql = 'select * from '.$this->table.' where cat_id='.$cat_id;
		
		$result=$this->db->getRow($sql);
		if($this->db->affected_rows())
			return $result;
		else
			return array();
	}

	//获取家族谱
	public function getTree($data,$id=0,$lev=1){
		$tree = array();

		foreach ($data as $key => $value) {
			if( $value['parent_id'] == $id){
				$value['lev'] = $lev;
				$tree[] = $value;

				$tree = array_merge($tree,$this->getTree($data,$value['cat_id'],$lev+1));
			}
		}
		return $tree;
	}

	//查子栏目
	public function getSon($cat_id){
		$sql = 'select cat_id,cat_name,parent_id from '.$this->table.' where parent_id='.$cat_id;

		return $this->db->getAll($sql);
	}

	//向上查家谱
	public function getFamily($parent_id){
		$family = array();
		$cats = $this->select();

		while ($parent_id>0) {
			foreach ($cats as $value) {
				if($value['cat_id'] == $parent_id ){
					$family[]=$value;
					$parent_id=$value['parent_id'];
					break;
				} 
			}
		}
		return array_reverse($family);
	}

	//删除分类
	public function del($cat_id=0){
		$sql = 'delete from '.$this->table.' where cat_id='.$cat_id;
		$this->db->query($sql);

		return $this->db->affected_rows();
	}

	//更新分类
	public function update($data,$cat_id){
		$this->db->autoQuery($this->table,$data,'update',' where cat_id='.$cat_id);

		return $this->db->affected_rows();
	}
}

?>