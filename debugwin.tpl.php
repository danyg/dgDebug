<?php
/*
 * Copyright (C) 2011-2012 Daniel Goberitz
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */?><script type="text/javascript">
if(typeof(jQuery) == 'undefined'){
	<?php echo file_get_contents(DGDEBUG_ROOT_PATH . 'jquery.js');?>
	$.noConflict();
}

(function($){
	$.fn.rClick = function(fnc) {
			if(jQuery.browser.msie){
				this.each(function() {
					var me = this;
					$(this).mousedown( function(e) {
						var event = e;
						$(this).mouseup( function() {
							$(this).unbind('mouseup');
							if( event.button == 2 ) {
								fnc.apply(me, [event]);
								return false;
							} else
								return true;
						});
					});
					this.oncontextmenu = function(){return false;};
					try{
						$("*", this).get(0).oncontextmenu = function(){return false;};
					}catch(e){}
				});
			}else{
				this.each(function() {
					var me = this;
					this.oncontextmenu = function(){
						fnc.apply(me, arguments);
						return false;
					};
					try{
						$("*", this).get(0).oncontextmenu = function(){
							fnc.apply(me, arguments);
							return false;
						};
					}catch(e){}
				});
			}

			return this;
		};

	function hideShow(elm, sibling, t){
		if(typeof(t) != 'number') t = 200;
		if($(elm).data('closed') != true){
			$(elm)
				.data('closed', true)
				.siblings(sibling)
					.slideUp(t);
		}else{
			$(elm)
				.data('closed', false)
				.siblings(sibling)
					.slideDown(t);
		}
	}

	function debugMaxNormal(){
		var win = $("#__DG_DEBUG__");
		var list = $("#__DG_DEBUG__ .__list");
		if($(win).data('max') != true){
			win.height($(window).height());
			list.height($(window).height() - 18);
			$(win).data('max', true)
		}else{
			win.height(300);
			list.height(282);
			$(win).data('max', false)
		}
	}

	$(document).ready(function(){
		$("#__DG_DEBUG__ .__title").click(function(){
			if($('#__DG_DEBUG__').height() > 300)	debugMaxNormal();
		
			if($('#__DG_DEBUG__ .__list').is(":hidden"))	$('#__DG_DEBUG__,#__DG_DEBUG_PUSHER__').height(300);
			else											$('#__DG_DEBUG__,#__DG_DEBUG_PUSHER__').height(18);
			hideShow(this, '.__list');
		});
		$("#__DG_DEBUG__ .__line").click(function(){hideShow(this, '.__data');});
		$("#__DG_DEBUG__ .__collapsablebtn__").click(function(){hideShow(this, '.__collapsableelm__');});
		
		$("#__DG_DEBUG__ .__title").rClick(function(event){
			if($("#__DG_DEBUG__ .__list").is(':hidden')) hideShow(this, '.__list', 0);

			debugMaxNormal();
		});
		$("#__DG_DEBUG__ .__line").rClick(function(){
			var elm = $(this).parent();
			if(elm.hasClass('__viewed'))	elm.removeClass('__viewed')
			else							elm.addClass('__viewed')
		});
	});
}(jQuery));
</script>
<style type="text/css">
div#__DG_DEBUG__ ul.__list{list-style: none; padding:0;margin:0;}
div#__DG_DEBUG__ ul.__list li{list-style: none; padding:0;margin:0;}

div#__DG_DEBUG__{background: #f0f0f0; height: 300px; position: fixed; left: 0; bottom:0; width: 100%;}
div#__DG_DEBUG__ .__title{background: #808080; color: #fff; padding: 0px 4px; font: normal 12px/18px 'Trebuchet MS';width:100%;cursor:pointer;}
div#__DG_DEBUG__ ul.__list{height: 282px; overflow-y: auto; overflow-x: none;}
div#__DG_DEBUG__ li{border-bottom: solid 1px black; background: lightyellow; overflow: none; font: normal 12px/15px 'Lucida Console', System;padding: 3px 0px;}
div#__DG_DEBUG__ li.__even{background: lightblue;}
div#__DG_DEBUG__ li .__line{padding-bottom: 3px; background:#f1f192; cursor: pointer;}
div#__DG_DEBUG__ li .__collapsablebtn__{cursor: pointer;}
div#__DG_DEBUG__ li.__even .__line{background:#89c4d7;}
div#__DG_DEBUG__ li.__viewed{opacity: 0.5;}
div#__DG_DEBUG__ li pre{margin:0; padding:0;}

div#__DG_DEBUG__ .__string{color:green;}
div#__DG_DEBUG__ .__key{color:blue;}
div#__DG_DEBUG__ .__type{color:#800080;font-style:italic;}
div#__DG_DEBUG__ .__block{color:blue;font-weight:bold;}
div#__DG_DEBUG__ .__operator{color:#000080;font-weight:bold;}

div#__DG_DEBUG_PUSHER__{height: 300px;}
</style>
<div id="__DG_DEBUG_PUSHER__">&nbsp;</div>
<div id="__DG_DEBUG__">
	<div class="__title"><strong>dgDebug 0.4.0</strong> by Daniel Goberitz</div>
	<ul class="__list">
	
<?php
for($i=0,$cC=count($t->stack);$i<$cC;$i++){
?>
		<li class="<?php echo $i%2 ? "__even" : ""?>">
			<div class="__line"><?php echo $t->stack[$i]["trace"]?></div>
			<div class="__data"><pre><?php echo $t->stack[$i]["html"]?></pre></div>
		</li>
<?php
}
?>
	</ul>
</div>