<style type="text/css">
	.file-box{ position:relative;width:340px}
	.btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
	.file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
	.article-content{width: 625px; float: left;}
	.article-content p{padding-bottom: 10px; font-size: 14px; color: #555;line-height: 24px; text-indent: 2em;}
</style>
<div class="pageContent">
    <form method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_ticket');?>" class="pageForm required-validate" onsubmit="return iframeCallback(this, navTabAjaxDone);">
        <div class="pageFormContent" layoutH="55">
        	<fieldset style="width: 90%">
        	    <dl class="nowrap">
        			<dt>标题：</dt>
        			<dd>
						<input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
						<?php echo $head->title;?>
        			</dd>
        		</dl>
        	</fieldset >
			<fieldset style="width: 90%">

				<dl class="nowrap">
					<dt>创建时间：</dt>
					<dd>
						<?php echo $head->created;?>
					</dd>
				</dl>

			</fieldset >

			<fieldset style="width: 90%" class="article-content">

				<textarea id="content" name="content" class="" rows="20" cols="80" style="width: 100%">
                      <?php echo $head->content;?>
                </textarea>
			</fieldset>


        </div>
        <div class="formBar">
    		<ul>
    			<li><div class="button"><div class="buttonContent"><button type="button" class="close icon-close">取消</button></div></div></li>
    		</ul>
        </div>
	</form>
</div>

<script type="text/javascript" src="/static/js/zh-cn.js"></script>
<script>
    //可视化编辑器实例调用
    $('#content').xheditor({
        html5Upload : false,
        upImgUrl:"/news/upload_news_pic",
        upImgExt:"jpg,jpeg,gif,png"
    });
	var total = 0
	$('.xiaoji').each(function(){
		total += parseInt($(this).html());
	})
	$("#total").html(total)
	$(".fahuo",navTab.getCurrentPanel())
		.button()
		.click(function( event ) {
			event.preventDefault();
		});
</script>


