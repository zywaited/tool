/*
  <div id="wrap">
        <div class="box">
            <div class="info">
                <div class="pic"><img src=""></div>
                <div class="title"><a href="#"></a></div>
            </div>
        </div>
  </div>
 */

$(function(){

	/**
	*  瀑布流排布
	*/
	//获取外层元素
	var wrap=$('#wrap');
	//列数
	var cols=null;
	var boxWidth=null;
	// var boxHeight=null;
	var windowWidth=null;
	//获取box元素
	var box=null;
	//计算出box的长度
	var length=null;
	//整体排布函数
	function plug(){
		//计算出可显示的列数
		boxWidth=box.eq(0).outerWidth(true);
		// boxHeight=box.eq(0).outerHeight(true);
		windowWidth=$(window).width();
		cols=Math.floor(windowWidth/boxWidth);
		// wrap.css('left',($(window).width()-cols*boxWidth)/2);
		wrap.css('width',cols*boxWidth+'px');
	}

	/**
	*  获取最小高度的列及索引
	*/
	//定义一个数组记录各列的高度
	var height=[];
	//定义一个数组记录最小列的宽高度以及索引
	var msg=[];
	//定义一个索引
	var now=0;
	/*
	*  获取最小高度的列及索引并相应的添加图片函数
	*  @param box    [obj]
	*  @param length [Num]
	*/
	function getMinCol(box,length){
		for(var i=now;i<length;i++){
			if(i<cols){
				height[i]=box.eq(i).outerHeight(true);
			}else{
				msg[0]=getIndex(height,Math.min.apply(null,height));
				msg[1]=boxWidth*msg[0];
				var boxNow=box.eq(i);
				boxNow.css({
					'position':'absolute',
					'left':msg[1]+'px',
					'top':height[msg[0]]+'px',
					'opacity':'0'
				}).stop().animate({'opacity':'1'},1000);
				height[msg[0]]+=boxNow.outerHeight(true);
				msg[2]=height[getIndex(height,Math.min.apply(null,height))];
				if(length-1==i){
					now=length;
				}
			}
		}
	}
	/**
	 *  获取最小列的top值
	 *  @param height[]  [array] 比较的数组
	 *  @param min [Num]  比较数组的最小值
	 */
	function getIndex(height,min){
		for(var index=0;height.length;index++){
			if(min==height[index]){
				return index;
			}
		}
	}

	//判断是否重新加载box
	var load=false;
	var data = null;
	var offset=20;
	var num=10;
	var i=0;

	//避免重复
	var angin=null;

	//执行函数
	function start(){
		if(!load){
			box=$('.box');
			length=box.length;
			load=true;
		}
		if(i++>=length-1){
			load=false;
			angin=false;
			getMinCol(box,length);
			if(time==1)
				time=setInterval(scrollAuto,80);
		}
	}
	$('img').load(function(){
		start();
		plug();
	});

	/**
	 *  设置滚动条控制瀑布流数据
	 */
	$(window).scroll(function(){
		if(!angin){
			if(checkData()){
				angin=true;
				clearInterval(time);
				var child=null;
				$.ajax({
					type:'POST',
					url:'plug.php',
					data:'offset='+offset+'&ajax=true&num='+num,
					// dataType:'json',
					success:function(datas){
						if(datas!='failed'){
							data=eval(datas);
							offset+=10;
							for(var n in data){
								child='<div class="box"><div class="info"><div class="pic"><img src="'+data[n].mimg+'"></div><div class="title"><a href="#">'+data[n].mtitle+'</a></div></div></div>';
								wrap.append(child);
							}
							//重新获相关的数据
							$('img').load(function(){
								start();
							});
						}else{
							clearInterval(time);
						}
					}
				});		
			}
		}
	}).mousedown(function(){
		clearInterval(time);
	}).mouseup(function(){
		time=setInterval(scrollAuto,80);
	});
	//检测是否加载数据函数
	function checkData(){
		return ($(window).height()+$(window).scrollTop())>=msg[2]?true:false;
	}

	//加载滚动条的自动滚动
	//记录滚动条的高度
	var scrollTop=null;
	var time=null;
	function scrollAuto(){
		scrollTop=$(window).scrollTop();
		$(window).scrollTop(++scrollTop);
	}
	time=setInterval(scrollAuto,80);
});
