/*
  <div id="name">
		<ul>
			<li class="current">HTML</li>
			<li>CSS</li>
			<li>JavaScript</li>
			<li>PHP</li>
			<li>Mysql</li>
		</ul>
	</div>
	<div id="msg">
		<div>HTML</div>
		<div class="hide">CSS</div>
		<div class="hide">JavaScript</div>
		<div class="hide">PHP</div>
		<div class="hide">Mysql</div>
	</div>
	
	<script>
	  $(function(){
	  	$('#name ul').waited({
	 	  one:'li',
	  	  another:'#msg div',
	 	  style:'current',
	 	  eventone:'mouseenter',
	  	});
	  });
	</script>
	*/
	
	/*
	自定义tab便签的jquery插件
	@options json
	  one another 需要改变样式的便签
	  style 样式，默认当前current
	  eventone 事件，默认mouseenter
*/
;(function($){
  $.fn.waited=function(options){
  	var defaulter={
  	  one:'li',
  	  another:'div',
   	  style:'current',
   	  eventone:'mouseenter'
  	};
  	var options=$.extend(defaulter,options);
  	this.each(function(){
      var _this = $(this);
  	  _this.find(options.one).bind('mouseenter',function(){
  	  	_this.addClass(options.style).siblings().removeClass(options.style);
  	  	var _index=_this.index();
  	  	$(options.another).eq(_index).show(500).siblings().stop() .hide();
  	  });
  	});
  	return this;
  }
})(jQuery);
