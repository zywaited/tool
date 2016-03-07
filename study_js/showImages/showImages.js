/*
  <div id="image">
	  <ul>
	  		<li><img src="./images/1.jpg"  /></li>
			<li><img src="./images/2.jpg"  /></li>
			<li><img src="./images/3.jpg"  /></li>
			<li><img src="./images/4.jpg"  /></li>
			<li><img src="./images/5.jpg"  /></li>
	  </ul>
	</div>
	<div id="button">
		<button id="goLeft">向左走</button>
		<button id="goRight">向右走</button>
	</div>	
	<div class="wrap">
		<ul>
			<li><img src="./slider/1.jpg" ></li>
			<li><img src="./slider/2.jpg" ></li>
			<li><img src="./slider/3.jpg" ></li>
			<li><img src="./slider/4.jpg" ></li>
			<li><img src="./slider/5.jpg" ></li>
		</ul>
		<ol>
			<li class="current">1</li>
			<li>2</li>
			<li>3</li>
			<li>4</li>
			<li>5</li>
		</ol>
		<p class="introduce"></p>

	</div>
	*/
	
	
	
$(function(){
	/*
		多张图片的无缝滚动
	*/
	
	//获取ul的内容并把此内容的双倍重新赋给ul
	var ul=$('#image ul');
	var ulHtml=ul.html();
	ul.html(ulHtml+ulHtml);
	//定义一个空的定时器
	var time=null;

	//获取单张图片的大小并计算出ul的总大小
	var li=$('#image ul li');
	var liWidth=li.eq(0).width();
	var liLize=li.length;
	var ulWidth=liLize*liWidth;
	ul.width(ulWidth);

	//运动函数
	//设置运动的速度,默认为-2，向左走
	var speed=-2;
	function sport(){
		if(speed<0){
			if(-ulWidth/2+'px'==ul.css('left')){
				ul.css('left',0);
			}
			ul.css('left','+='+speed+'px');
		}else{
			if('0px'==ul.css('left')){
				ul.css('left',-ulWidth/2+'px');
			}
			ul.css('left','+='+speed+'px');
		}
	}
	time=setInterval(sport,60);
	ul.mouseover(function(){
		clearInterval(time);
	}).mouseout(function(){
		time=setInterval(sport,60);
	});
	$('#goLeft').click(function(){
		speed=-2;
	});
	$('#goRight').click(function(){
		speed=2;
	});



	/*
		单张图的无缝滚动
	*/
	var aul=$('.wrap ul');
	var auli=$('.wrap ul li');
	var numli=$('.wrap ol li');
	var auliWidth=auli.eq(0).width();
	var auliSize=auli.length;
	var UWidth=auliWidth*auliSize;
	aul.width(UWidth);

	var img=$('.wrap ul img');
	var intro=$('.wrap p');

	//定时器
	var timeone=null;
	//记录图片的索引
	var now_1=0;
	//记录数字的索引
	var now_2=0;
	//简介
	var imgAlt=null;

	numli.mouseover(function(){
		var _index=$(this).index();
		now_1=now_2=_index;
		imgAlt=img.eq(now_1).attr('alt');
		intro.html(imgAlt);
		$(this).addClass('current').siblings().removeClass();
		aul.animate({'left':-auliWidth*now_2},500).dequeue();
		clearInterval(timeone);
	}).mouseout(function(){
		timeone=setInterval(slider,1500);
	});

	//运动函数
	function slider(){
		if(now_2==auliSize-1){
			now_2=0;
			auli.eq(0).css({
				'position':'relative',
				'left':UWidth
			});
		}else{
			now_2++;
		}
		now_1++;
		numli.eq(now_2).addClass('current').siblings().removeClass();
		imgAlt=img.eq(now_2).attr('alt');
		intro.html(imgAlt);

		aul.animate({'left':-auliWidth*now_1},500,function(){
			if(now_2==0){
				auli.eq(0).css('position','static');
				aul.css('left',0);
				now_1=0;
			}
		});
	}

	timeone=setInterval(slider,1500);
	aul.hover(function(){
		clearInterval(timeone);
	},function(){
		timeone=setInterval(slider,1500);
	});
})
