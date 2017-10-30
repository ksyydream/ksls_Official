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


				<dl>
					<dt>试题类型：</dt>
					<dd>
						<?php
						if($head->style==1){
							echo '单选题';
						}
						if($head->style==2){
							echo '多选题';
						}
						if($head->style==3){
							echo '陈述题';
						}
						?>
					</dd>
				</dl>

				<dl>
					<dt>类别：</dt>
					<dd>
						<?php echo $head->name;?>
					</dd>
				</dl>
        	</fieldset >

			<fieldset style="width: 90%" class="article-content">
				<dl>
					<dt>题目:：</dt>

				</dl>
				 <textarea  readonly="readonly" name="content" ROWS="20" cols="80" style="width: 100%">
                                    <?php echo "标题:".$head->title;?>
									 <?php
									if($head->style==3){

									}else{
										echo "\n";
										echo "A.".$head->op1;
										echo "\n";
										echo "B.".$head->op2;
										echo "\n";
										echo "C.".$head->op3;
										echo "\n";
										echo "D.".$head->op4;
										echo "\n";
										echo "答案:";
										if($head->as1==1){
											echo 'A';
										}
										if($head->as2==1){
											echo 'B';
										}
										if($head->as3==1){
											echo 'C';
										}
										if($head->as4==1){
											echo 'D';
										}
									}

									?>
                </textarea>
			</fieldset >
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
    $('#xh_content').xheditor({
        html5Upload : false,
        upImgUrl:"/news/upload_news_pic",
        upImgExt:"jpg,jpeg,gif,png"
    });

</script>


