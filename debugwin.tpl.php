<script type="text/javascript">
	<?php echo file_get_contents(DGDEBUG_ROOT_PATH . 'jquery.js');?>
	myjQuery.noConflict();

(function($){
	$.fn.rClick = function(fnc) {
		if($.browser.msie){
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
					var e = $("*", this).get(0);
					if('undefined' != typeof(e))
						e.oncontextmenu = function(){return false;};
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
					var e = $("*", this).get(0);
					if('undefined' != typeof(e)){
						e.oncontextmenu = function(){
							fnc.apply(me, arguments);
							return false;
						};
					}
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
					.hide(0);
		}else{
			$(elm)
				.data('closed', false)
				.siblings(sibling)
					.show(0);
		}
	}

	function debugMaxNormal(){
		var win = $("#__DG_DEBUG__");
		var list = $("#__DG_DEBUG__ .__list");
		if($(win).data('max') != true){
			debugMaximize();
		}else{
			debugNormalize();
		}
	}

	function debugMaximize(){
		var win = $("#__DG_DEBUG__");
		var list = $("#__DG_DEBUG__ .__list");
		
		win.height($(window).height());
		list.height($(window).height() - 18);
		$(win).data('max', true)
	}
	
	function debugNormalize(){
		var win = $("#__DG_DEBUG__");
		var list = $("#__DG_DEBUG__ .__list");

		win.height(300);
		list.height(282);
		$(win).data('max', false);
		$('#__DG_DEBUG__,#__DG_DEBUG_PUSHER__').height(300);
	}

	$(document).ready(function(){
		// dbgwin events
		$("#__DG_DEBUG__ .__title").click(function(){
			if($('#__DG_DEBUG__').height() > 300)	debugMaxNormal();
		
			if(!$('#__DG_DEBUG__ .__list').is(":visible"))	$('#__DG_DEBUG__,#__DG_DEBUG_PUSHER__').height(300);
			else											$('#__DG_DEBUG__,#__DG_DEBUG_PUSHER__').height(18);

			hideShow($("#__DG_DEBUG__"), '.__list');
		});
		$("#__DG_DEBUG__ .__title").rClick(function(event){
			if($("#__DG_DEBUG__ .__list").is(':hidden')) hideShow($("#__DG_DEBUG__"), '.__list', 0);

			debugMaxNormal();
		});
		$(document).keyup(function(e){
			var win = $("#__DG_DEBUG__");
			if(e.ctrlKey && e.shiftKey && e.keyCode === 38){ // CtrlShiftUP
			
				if(!$('#__DG_DEBUG__').data("closed")){
					debugNormalize();
				}else if($(win).data('max') != true){
					debugMaximize();
				}
			}
			
			if(e.ctrlKey && e.shiftKey && e.keyCode === 40){ // CtrlShiftDown
				if($(win).data('max') == true){
					debugNormalize();
				}else{
					$('#__DG_DEBUG__,#__DG_DEBUG_PUSHER__').height(18);
					hideShow(win, '.__list');
				}
			}
		});
		
		// group events		
		$("#__DG_DEBUG__ .__line").click(function(){hideShow(this, '.__data');});
		$("#__DG_DEBUG__ .__collapsablebtn__").click(function(){hideShow(this, '.__collapsableelm__');});

		$("#__DG_DEBUG__ .__line").rClick(function(){
			var elm = $(this).parent();
			if(elm.hasClass('__viewed'))	elm.removeClass('__viewed')
			else							elm.addClass('__viewed')
		});
		
		
	});
}(myjQuery));
</script>
<style type="text/css">
div#__DG_DEBUG__ ul.__list,
div#__DG_DEBUG__ ul.__sublist{list-style: none; padding:0;margin:0;}
div#__DG_DEBUG__ ul.__list li{list-style: none; padding:0;margin:0;}
div#__DG_DEBUG__ ul.__sublist .__line,
div#__DG_DEBUG__ ul.__sublist .__data{padding-left: 20px;}

div#__DG_DEBUG__{background: #f0f0f0; height: 300px; position: fixed; left: 0; bottom:0; width: 100%; z-index:9000000;}
div#__DG_DEBUG__ .__title{background: #808080; color: #fff; padding: 0px 4px; font: normal 12px/18px 'Trebuchet MS';width:100%;cursor:pointer;}
div#__DG_DEBUG__ ul.__list{height: 282px; overflow-y: auto; overflow-x: none;}
div#__DG_DEBUG__ li{border-bottom: solid 1px black;font: normal 12px/15px 'Lucida Console', System;}
div#__DG_DEBUG__ .__sublist li:last-child{border-bottom: none;}
div#__DG_DEBUG__ .__sublist li{background: lightyellow; overflow: none; padding: 3px 0px;}
div#__DG_DEBUG__ li.__even{background: lightblue;}
div#__DG_DEBUG__ li .__line{padding-bottom: 3px; background:#f1f192; cursor: pointer;}
div#__DG_DEBUG__ li .__collapsablebtn__{cursor: pointer;}
div#__DG_DEBUG__ li.__even .__line{background:#89c4d7;}
div#__DG_DEBUG__ li.__viewed{opacity: 0.5;}
div#__DG_DEBUG__ li pre{margin:0; padding:0;}

div#__DG_DEBUG__ li.__group .__gTitle{background:darkSeaGreen;}
div#__DG_DEBUG__ li.__group.__even .__gTitle{background:darksalmon;}

div#__DG_DEBUG__ .__string{color:green;}
div#__DG_DEBUG__ .__key{color:blue;}
div#__DG_DEBUG__ .__type{color:#800080;font-style:italic;}
div#__DG_DEBUG__ .__block{color:blue;font-weight:bold;}
div#__DG_DEBUG__ .__operator{color:#000080;font-weight:bold;}

div#__DG_DEBUG_PUSHER__{height: 300px;}
</style>
<div id="__DG_DEBUG_PUSHER__">&nbsp;</div>

<div id="__DG_DEBUG__">
	<div class="__title"><strong>dgDebug <?php echo $t->version ?></strong> by Daniel Goberitz</div>
	<ul class="__list">
	
<?php
$cut=false;
$groupi=0;$elementi=0;
for($i=0,$cC=count($t->stack);$i<$cC;$i++){
	if(!is_array($t->stack[$i])){
		
		if($cut){?>
			</ul>
		</li>
		<?php
		}
		
		$cut=true;
		?>
		<li class="__group <?php echo $groupi%2 ? '__even' : ''?>">
			<div class="__line __gTitle"><?php echo $t->stack[$i]?></div>
			<ul class='__sublist __data'>
<?php
		$groupi++;

	}else{
?>
				<li class="<?php echo $i%2 ? '__even' : ''?>">
					<div class="__line"><?php echo $t->stack[$i]["trace"]?></div>
					<div class="__data"><pre><?php echo $t->stack[$i]["html"]?></pre></div>
				</li>
<?php
	}
}

if($cut){ ?>
			</ul>
		</li>
<?php
	}
?>
	</ul>
</div>