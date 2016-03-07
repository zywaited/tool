/*
  <div id="zoom">
		<div class="middle">
			<img src="1.jpg" />
			<div class="mask"></div>
		</div>
		<div class="small">
			<span class="left disable">&lt;</span>
			<div id="img">
				<ul>
					<li class="current"><img src="1.jpg" ></li>
					<li><img src="2.jpg" ></li>
					<li><img src="3.jpg" ></li>
					<li><img src="4.jpg" ></li>
					<li><img src="5.jpg" ></li>
					<li><img src="6.jpg" ></li>
				</ul>
			</div>	
			<span class="right">&gt;</span>
		</div>
		<div class="large"><img src="1.jpg" ></div>
	</div>
	*/
	
	$(function(){
	var imgLi=$('#img ul li');
	var imgLiSize=imgLi.length;
	var imgLiWidth=imgLi.outerWidth(true);
	var imgUl=$('#img ul');
	var leftBtn=$('span.left');
	var rightBtn=$('span.right');
	var mian=$('.middle');
	var now=0;
	var index=0;
	var src=null;

	var mask=$('.mask');
	var maskWidth=mask.width();
	var maskHeight=mask.height();

	var middleImg=$('.middle img');
	var middle=$('.middle');
	var largeImg=$('.large img');
	var large=$('.large');

	imgLi.mouseover(function(){
		$(this).addClass('current').siblings().removeClass('current');
		src=$(this).children('img').attr('src');
		middleImg.attr('src',src);
		largeImg.attr('src',src);
	});

	rightBtn.click(function(){
		if(!rightBtn.hasClass('disable')){
			leftBtn.removeClass('disable');
			now++;
			if(now>=imgLiSize-4){
				$(this).addClass('disable');
				now=imgLiSize-4;
			}
			imgUl.animate({
				'left':'-='+imgLiWidth+'px'
			},300);
		}
	});

	leftBtn.click(function(){
		if(!leftBtn.hasClass('disable')){
			rightBtn.removeClass('disable');
			now--;
			if(now<=0){
				now=0;
				$(this).addClass('disable');
			}
			imgUl.animate({
				'left':'+='+imgLiWidth+'px'
			},300);
		}
	});

	middle.mousemove(function(ev){
		mask.show();
		large.show();

		var distance=$('.middle').offset();
		var left=ev.pageX-distance.left-maskWidth/2;
		var right=ev.pageY-distance.top-maskHeight/2;

		var nowLeft=middle.width()-maskWidth;
		var nowRight=middle.height()-maskHeight;

		if(left<=0){
			left=0;
		}else if(left>=nowLeft){
			left=nowLeft;
		}

		if(right<=0){
			right=0;
		}else if(right>=nowRight){
			right=nowRight;
		}

		var percentX=left/nowLeft;
		var percentY=right/nowRight;

		mask.css({
			'left':left+'px',
			'top':right+'px'
		});

		largeImg.css({
			'left':-percentX*(600-$('.large').width())+'px',
			'top':-percentY*(600-$('.large').height())+'px'
		}).dequeue();
	}).mouseout(function(){
		mask.hide();
		large.hide();
	});
});
