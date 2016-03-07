<?php
	/**
	 *   封装图片管理类，实现剪切，缩略图
	 *   file:ImageTool.class.php
	 */

	//防止非法访问
	defined('WAITED') || exit('非法访问');

	class ImageTool{
		//错误信息索引
		protected $index = 4;
		//水印类型
		protected $waterType = array(0,1);
		//默认水印位置
		protected $posType = array(0,1,2,3,4);
		//错误信息
		protected $error = array(
						0=>'文件不存在',
						1=>'不能读取文件信息',
						2=>'暂时不能使用该功能,请检查传入的参数',
						3=>'当前还没有相关错误信息',
						4=>'类型只能是0或1',
						5=>'水印位置只能是0-4',
						6=>'自定义位置只能为索引数组，包含相对高度和宽度',
						7=>'水印图过大',
						8=>'自定义相对宽高度不合理',
						9=>'长度不合理'
					);

		/**
		 *   分析图片信息
		 *   @param string 图片名称
		 *   @return fix 失败返回false,成功返回图片信息(array)
		 */
		protected static function imageInfo($image){
			//判断图片是否存在
			if(! file_exists($image)){
				$this->index = 0;
				return false;
			}
			//判断文件的信息是否可读
			$info = getimagesize($image);
			if(! $info){
				$this->index = 1;
				return false;
			}
			//返回图片的相关信息
			$img['width'] = $info[0];
			$img['height'] = $info[1];
			$img['type'] = strtolower(substr($info['mime'],strpos($info['mime'],'/')+1));
			return $img;
		}

		/**
		 *   创建图片画布函数
		 *   @param $type string 类型
		 *   @return string
		 */
		protected static function imageFun($type){
			return 'imagecreatefrom'.$type;
		}

		/**
		 *   销毁画布
		 *   @param obj 类型
		 */
		protected static function destroy($obj){
			imagedestroy($obj);
		}

		/**
		 *   获取错误信息
		 *   @return string
		 */
		public static function getError(){
			return $this->error[$this->index];
		}

		/**
		 *   图片加水印
		 *   @param $dst string 目标图片
		 *   @param $water string 源图
		 *   @param $save string 保存路径,默认覆盖原图
		 *   @param $alpha int 透明度，默认50
		 *   @param $type int 位置类型,0代表系统默认，1代表用户自定义
		 *   @param $pos fix 如果选择系统默认，直接输入0-4选择位置，默认2，中间位置，如array(1);否则输入相应位置array(width,height)
		 *   @return bool
		 */
		public static function water($dst,$water,$save=null,$alpha=50,$type=0,$pos=2){
			//判断文件是否存在
			if(! file_exists($dst) || ! file_exists($water)){
				$this->index = 0;
				return false;
			}
			//获取文件信息
			$dstInfo = self::imageInfo($dst);
			$waterInfo = self::imageInfo($water);
			//判断读取是否成功
			if(! $dstInfo || ! $waterInfo){
				return false;
			}
			//判断水印图与目的图的大小
			if($waterInfo['width'] > $dstInfo['width'] || $waterInfo['height'] > $dstInfo['height']){
				$this->index = 7;
				return false;
			}
			//判断功能是否可用
			$dstFun = self::imageFun($dstInfo['type']);
			$waterFun = self::imageFun($waterInfo['type']);
			if(! function_exists($dstFun) || ! function_exists($waterFun)){
				$this->index = 2;
				return false;
			}
			//加水印,加载图片
			$dim = $dstFun($dst);
			$wim = $waterFun($water);
			if (! $dim || ! $wim) {
				$this->index = 2;
				return false;
			}
			//判断水印类型
			if(! is_numeric($type) || ! in_array($type, $this->waterType)){
				$this->index = 4;
				return false;
			}
			//系统默认类型
			if(0 == $type){
				//判断位置是否合法
				if(! is_numeric($pos) || ! in_array($type, $this->posType)){
					$this->index = 5;
					return false;
				}
				switch ($pos) {
					//左上方
					case 0:
						$posX = 0;
						$posY = 0;
						break;
					//右上方
					case 1:
						$posX = $dstInfo['width']-$waterInfo['width'];
						$posY = 0;
						break;
					//左下方
					case 3:
						$posX = 0;
						$posY = $dstInfo['height']-$waterInfo['height'];
						break;
					//右下方
					case 4:
						$posX = $dstInfo['width']-$waterInfo['width'];
						$posY = $dstInfo['height']-$waterInfo['height'];
						break;
					//默认中间位置
					default:
						$posX = ($dstInfo['width']-$waterInfo['width'])/2;
						$posY = ($dstInfo['height']-$waterInfo['height'])/2;
				}
			}else{
				//判断是否为数组
				if(! is_array($pos) || ! isset($pos[0]) || ! isset($pos[1])){
					$this->index = 6;
					return false;
				}
				//判断宽高是否合理
				if($waterInfo['width']+$pos[0] > $dstInfo['width'] || $waterInfo['height']+$pos[1] > $dstInfo['height']){
					$this->index = 8;
					return false;
				}
				$posX = $pos[0];
				$posY = $pos[1];
			}

			$bool = imagecopymerge($dim, $wim, $posX, $posY, 0, 0, $waterInfo['width'], $waterInfo['height'], $alpha);
			if (! $bool) {
				$this->index = 2;
				return false;
			}
			//保存
			if(! $save){
				$save = $dst;
				unlink($dst);
			}
			$saveFun = 'image'.$dstInfo['type'];
			if(! function_exists($saveFun)){
				$this->index = 2;
				return false;
			}
			$saveFun($dim,$save);
			//销毁
			self::destroy($dim);
			self::destroy($wim);
			return true;
		}

		/**
		 *   生成缩略图(等比例)
		 *   @param $dst string 目标图片
		 *   @param $save string 保存路径,默认覆盖原图
		 *   @param $width int 缩放最大宽度,默认200
		 *   @param $height int 缩放最大高度，默认200
		 *   @param $type int 0默认不合并，1合并为最小
		 *   @return bool
		 */
		public static function thumb($dst,$save=null,$width=200,$height=200,$type=0){
			//判断文件是否存在
			if(! file_exists($dst)){
				$this->index = 0;
				return false;
			}
			//获取图片信息
			$dstInfo = self::imageInfo($dst);
			if(! dstInfo){
				return false;
			}
			//判断宽高是否合理
			if($width < $dstInfo['width'] || $height< $dstInfo['height']){
				$this->index = 8;
				return false;
			}
			//计算缩放比例
			$rate = min($width/$dstInfo['width'],$height/$dstInfo['height']);
			$dstFun = self::imageFun($dstInfo['type']);
			if(! function_exists($dstFun)){
				$this->index = 2;
				return false;
			}
			//加载图片
			$dim = $dstFun($dst);
			if(1 == $type){
				$width = $rate * $dstInfo['width'];
				$height = $rate * $dstInfo['height'];
				//计算缩放后大小
				$dwidth= $width;
				$dheight= $height;
			}else{
				//计算缩放后大小
				$dwidth=$dstinfo['width'] * $rate;
				$dheight=$dstinfo['height'] * $rate;
			}
			//创建画布
			$tim = imagecreatetruecolor($width, $height);
			if (! $tim) {
				$this->index = 2;
				return false;
			}
			//加载颜色
			$white = imagecolorallocate($tim, 255, 255, 255);
			//填充
			imagefill($tim, 0, 0, $white);
			//计算偏移量
			$parddingx = ($width-$dwidth)/2;
			$parddingy = ($height-$dheight)/2;
			//缩放
			$bool = imagecopyresampled($tim, $dim, $parddingx, $parddingy, 0, 0, $dwidth, $dheight, $dstInfo['width'], $dstInfo['height']);
			if (! $bool) {
				$this->index = 2;
				return false;
			}
			//保存
			if(! $save){
				$save = $dst;
				unlink($dst);
			}
			$saveFun = 'image'.$dstInfo['type'];
			if(! function_exists($saveFun)){
				$this->index = 2;
				return false;
			}
			$saveFun($dim,$save);
			self::destroy($dim);
			self::destroy($tim);
			return true;
		}

		/**
		 *   生成验证码
		 *   @param $width int 缩放最大宽度,默认200
		 *   @param $height int 缩放最大高度，默认200
		 *   @param $font fix 字体，默认5
		 *   @param $posX int 坐标，默认7
		 *   @param $posY int 坐标，默认5
		 *   @param $length int 长度，默认4
		 *   @param $time int 扭曲程度,默认4
		 *   @return bool
		 */
		public static function code($width=200,$height=200,$font=5,$posX=7,$posY=5,$length=4,$time=4){
			//造画布
            $image = imagecreatetruecolor($width,$height);
            $img = imagecreatetruecolor($width, $height);

            if (! $image || ! $img) {
				$this->index = 2;
				return false;
			}
           
            //造背影色
            $gray = imagecolorallocate($image, 200, 200, 200);
           
            //填充背景
            imagefill($image, 0, 0, $gray);
            imagefill($img, 0, 0, $gray);
            imagefill($dimg, 0, 0, $gray);
           
            //造随机字体颜色
            $color = imagecolorallocate($image, mt_rand(0, 125), mt_rand(0, 125), mt_rand(0, 125)) ;
            //造随机线条颜色
            $color1 =imagecolorallocate($image, mt_rand(100, 125), mt_rand(100, 125), mt_rand(100, 125));
            $color2 =imagecolorallocate($image, mt_rand(100, 125), mt_rand(100, 125), mt_rand(100, 125));
            $color3 =imagecolorallocate($image, mt_rand(100, 125), mt_rand(100, 125), mt_rand(100, 125));
           
            //在画布上画线
            imageline($image, mt_rand(0, 50), mt_rand(0, 25), mt_rand(0, 50), mt_rand(0, 25), $color1) ;
            imageline($image, mt_rand(0, 50), mt_rand(0, 20), mt_rand(0, 50), mt_rand(0, 20), $color2) ;
            imageline($image, mt_rand(0, 50), mt_rand(0, 20), mt_rand(0, 50), mt_rand(0, 20), $color3) ;
           
            //在画布上写字
            $str = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijkmnprstuvwxyz23456789';
            if(! is_numeric($length) || $length > strlen($str)){
            	$this->index = 9;
            	return false;
            }
            $text = substr(str_shuffle($str), 0,$length) ;
            imagestring($image, 5, $posX, $posY, $text, $color) ;

            //扭曲验证码
            for ($i=0; $i < $width; $i++) { 
            	$posY = sin($i*4*M_PI/$width)*$time;
                $bool = imagecopy($img, $image, $i, $posY, $i, 0, 1, $height);
                if (! $bool) {
					$this->index = 2;
					return false;
				}
			}
            //显示、销毁
            header('content-type: image/jpeg');
            imagejpeg($img);
            self::destroy($image);
            self::destroy($img);
            return true;
		}
	}
?>