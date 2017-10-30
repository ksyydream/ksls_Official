(function($){
	function _SelectSimu(here,options,index){
		var _this = this;
		this.$e = $(here),
			this.opts = options,
			this.index = index;
		this.init();
	}
	_SelectSimu.prototype = {
		init : function(){
			var _this = this;
			var className = (_this.$e.attr('id') ? '#'+_this.$e.attr('id')+' ' : 0) || (_this.$e.attr('class') ? '.'+_this.$e.attr('class')+' ' : 0);
			var cssStr = 	'<style>'+
				className + '{position:relative; width:'+_this.opts.width+'px;}'+
				className + '.selectInput{position: relative; border:1px solid #1abc9c;border-radius:3px;  background:#fff; width:100%; font-family: "Microsoft YaHei";}'+
				className + '.selectInput p{width:80%; height:34px; line-height:34px; display: block; overflow: hidden; border:none; font-size:16px; text-align: center; }'+
				className + '.selectInput p a{ color:#5a5a5a;}'+className + '.selectInput p .item-icon{ margin-top:5px;}'+
				className + '.selectInput i{display:block;position:absolute; right:10px;top:15px;width:9px; height:5px; background: url(/static/images/logo_in.png) no-repeat 0 -7px;}'+
				className + '.select-list{margin-top:-1px;width: 100%;position:absolute; left:0; top:100%; z-index: 999; overflow:hidden;border-top:none; background: #f5f5f5;display: none; }'+
		className + '.select-list ul li{height:36px; line-height:36px; overflow:hidden;zoom:1;font-size:14px;}'+
		className + '.select-list li .item-icon{margin-top:6px;}'+
		className + '.select-list li a{color:#5a5a5a;display: block;}'+
		className + '.select-list li a:hover{background: #1abc9c; color:#fff;}'+
		'</style>';
	//css插入页面
	_this.$e.before(cssStr);
	//html代码导入
	var defaultStr = '';
	if(!_this.opts.defaultValue){
		defaultStr = '<input type="hidden" name="selectInput" value="'+_this.opts.data[0].name +'" />'+'<p>'+ _this.opts.data[0].name +'</p>';
	}
	else {
		defaultStr ='<input type="hidden" name="selectInput" value="'+ _this.opts.data[_this.opts.defaultValue[this.index]].name+'" />'+
		'<p><a href="javascript:"><span class="item-icon item-icon-' + _this.opts.data[_this.opts.defaultValue[this.index]].icon + '"></span>'+ _this.opts.data[_this.opts.defaultValue[this.index]].name+'</a></p>';
	}
	var	selectStr = '<div class="selectInput">'+
		defaultStr+
		'<i></i>'+
		'</div>'+
		'<div class="select-list">'+
		'<ul>';
		for(var i = 0; i < _this.opts.listNum; i++){
			if(i==0){
				selectStr += '<li value="' + (_this.opts.data[i].id.toString() ? _this.opts.data[i].id.toString() : "" ) + '" data-score="'+ _this.opts.data[i].score+'" data-unit="'+ _this.opts.data[i].unit+'"><a href="javascript:"><span></span>' + (_this.opts.data[i].name ? _this.opts.data[i].name : "") + '</a></li>'
			}
			else{
				selectStr += '<li value="' + (_this.opts.data[i].id.toString() ? _this.opts.data[i].id.toString() : "") + '" data-score="'+ _this.opts.data[i].score+'" data-unit="'+ _this.opts.data[i].unit+'"><a href="javascript:"><span class="item-icon item-icon-' + _this.opts.data[i].icon + '"></span>' + (_this.opts.data[i].name ? _this.opts.data[i].name : "") + '</a></li>'
			}

			}
		selectStr += '</ul></div>';
		_this.$e.append(selectStr);

		_this.event();
	},
			event : function(){
				var _this = this;
				_this.$e.on('click','.selectInput',function(e){
					e.stopPropagation();
					$(document).find('.select-list').hide();
					_this.$e.find('.select-list').toggle();
				});
				$(document).click(function(){
					_this.$e.find('.select-list').hide();
				});
				_this.$e.find('.select-list').on('click','li',function(){
					var liValue = $(this).val(),
						liTxt = $(this).html(),
						singleScore = $(this).attr('data-score'),
						itemsUnit = $(this).attr('data-unit');
				//	alert(itemsUnit);
					_this.$e.find('.selectInput input').val(liValue);
					_this.$e.find('.selectInput p').html(liTxt);
					_this.$e.parent().children().find('.single-score').html(singleScore);
					_this.$e.parent().children().find('.item-unit').html(itemsUnit);
					_this.opts.change(_this.$e.parent().children(), liValue, singleScore);
				});
			}
		}
		$.fn.SelectSimu = function(options){
		var opts = $.extend({},$.fn.SelectSimu.defaults,options);
		return this.each(function(index){
			this.SelectSimu = new _SelectSimu(this,opts,index);
		});
	}
	$.fn.SelectSimu.defaults = {
		width : 150,
		zIndex : 0,
		listNum : 1,
		listValue : ['0'],
		listOption : ['请选择']
	}
})(jQuery);
