/*
  <div id="show">显示窗口</div>

	<div class="popWindow hide">
		<h3>弹出窗口的标题<span>关闭</span></h3>
		<div class="content">弹出窗口的内容</div>
	</div>
	*/
	
	$(function(){
	var oBtn = $('#show');
	var popWindow = $('.popWindow');
	var oClose = $('.popWindow h3 span');

	//浏览器可视区域的宽度
	var browserWidth = $(window).width();

	//浏览器可视区域的高度
	var browserHeight = $(window).height();

	//浏览器纵向滚动条距离上边界的值
	var browserScrollTop = $(window).scrollTop();

	//浏览器横向滚动条距离左边界的值
	var browserScrollLeft = $(window).scrollLeft();

	//弹出窗口的宽度
	var popWindowWidth = popWindow.outerWidth(true);
	//弹出窗口的高度
	var popWindowHeight = popWindow.outerHeight(true);

	//窗口居中的算法
	var positionLeft = (browserWidth-popWindowWidth)/2+browserScrollLeft;
	var positionTop = (browserHeight-popWindowHeight)/2+browserScrollTop;

	//遮罩层
	var mask = '<div class="mask"></div>';
	//高宽度
	var maskWidth = $(document).width();
	var maskHeight = $(document).height();

	//点按钮的事件
	oBtn.click(function(){

		//显示窗口
		popWindow.show().animate({
				'left':positionLeft+'px',
				'top':positionTop+'px'
		},300);

		//显示遮罩层
		$('body').append(mask);
		$('.mask').height(maskHeight).width(maskWidth);
	});

	//窗口改变事件
	$(window).resize(function(){
		if(popWindow.is(':visible')){
			//重新计算宽高度
			browserWidth = $(window).width();
			browserHeight = $(window).height();

			positionLeft = (browserWidth-popWindowWidth)/2+browserScrollLeft;
			positionTop = (browserHeight-popWindowHeight)/2+browserScrollTop;

			//显示窗口
			popWindow.show().animate({
					'left':positionLeft+'px',
					'top':positionTop+'px'
			},300);
		}
	});

	//滚动条事件
	$(window).scroll(function(){
		if(popWindow.is(':visible')){
			//需要重新计算宽高度
			browserScrollTop = $(window).scrollTop();
			browserScrollLeft = $(window).scrollLeft();

			positionLeft = (browserWidth-popWindowWidth)/2+browserScrollLeft;
			positionTop = (browserHeight-popWindowHeight)/2+browserScrollTop;

			//显示窗口
			popWindow.show().animate({
					'left':positionLeft+'px',
					'top':positionTop+'px'
			},300).dequeue();
		}
	});

	//关闭事件
	oClose.click(function(){
		popWindow.hide();
		$('.mask').remove();
	});

	/*
		拖动事件
	*/

	//按住鼠标左键事件
	$('.popWindow h3').mousedown(function(ev){
		if($('.popWindow').is(':visible')){
			
			//重新计算宽高度
			var popDiv = $('.popWindow').offset();
			var relativeLeft = ev.pageX - popDiv.left;
			var relativeTop = ev.pageY - popDiv.top;

			//鼠标移动事件
			$(document).mousemove(function(ev){
				positionLeft = ev.pageX - relativeLeft;
				positionTop = ev.pageY - relativeTop;
				$('.popWindow').css({
					'left':positionLeft+'px',
					'top':positionTop+'px'
				});
			});

			//松开鼠标左键
			$(document).mouseup(function(){
				$(document).off('mousemove');
				$(document).off('mouseup');
			});
		}
	});
});
