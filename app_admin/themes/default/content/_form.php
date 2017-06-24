<?php

use yii\helpers\Html;
//use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

$this->registerJsFile('js/edit.min.js');

/* @var $this yii\web\View */
/* @var $model common\models\Content */
/* @var $form yii\widgets\ActiveForm */
?>
<link rel="stylesheet" href="<?= STATIC_URL ?>bower/flatpickr/dist/flatpickr.min.css">
<script src="<?= STATIC_URL?>bower/flatpickr/dist/flatpickr.min.js"></script>
<script src="<?= STATIC_URL?>bower/flatpickr/dist/l10n/zh.js"></script>

<?= \wskm\web\Bower::getUploadStatic() ?>

<div class="content-form">

    <?php $form = ActiveForm::begin([
		'id' => 'form-content',
		'layout' => 'horizontal',
		'fieldConfig' => [
			'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
			'horizontalCssClasses' => [
				'label' => 'col-sm-2',
				'offset' => 'col-sm-offset-2',
				'wrapper' => 'col-sm-9',
				'error' => '',
				'hint' => '',
			],
		],
	]); ?>

	<?= $form->field($model, 'category_id')->dropDownList(\service\Category::getListOptions(false, $model->category_id), [
		'encode'=>false,
		//'style' => 'width:280px',
		])->label(\Wskm::t('Category', 'admin'))?>	
	
	<?php //echo $form->field($model, 'thumb')->textInput(['maxlength' => true])->label(\Wskm::t('Thumb', 'content')) ?>
	<?php echo Html::hiddenInput('Content[thumb]', $model->thumb, ['id' => 'content-thumb'])	?>

	<div class="form-group field-content-thumbup required">
		<label class="control-label col-sm-2" ><?= \Wskm::t('Upload').\Wskm::t('Thumb')?></label>
		<div class="col-sm-9">
			<div class="kv-avatar" style="width:200px">
				<input id="thumbUpload" name="Uploads[file]" type="file" class="file-loading" >
				<!--<div class="help-block help-block-error " id="thumbUploadError" ></div>-->
			</div>
		</div>
	</div>
		
    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label(\Wskm::t('Title')) ?>

	<?= $form->field($article, 'detail')->textarea([
		'rows' => 20,
	])->label(\Wskm::t('Detail')) ?>

	<?= $form->field($model, 'summary')->textarea([
		'rows' => 3,
	])->label(\Wskm::t('Summary')) ?>
			
    <?= $form->field($model, 'status')->radioList(\wskm\Status::getPublishedOrUnpublished()) ?>
	
	<?php if (!$model->isNewRecord) { ?>
	<?= $form->field($model, 'updated_at')->textInput([
		'style' => 'width:180px',
		'readonly' => true,
		'value' => Yii::$app->formatter->asDatetime($model->updated_at),
	])->label(\Wskm::t('UpdatedAt')) ?>
	<?php } ?>

    <div class="form-group field-content-thumbup required">
		<div class="col-sm-offset-2 col-sm-2">
        <?= Html::submitButton($model->isNewRecord ? \Wskm::t('Add') : \Wskm::t('Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		</div>
	</div>
	
	<div id="content-filelink" style="display:none" >
		
	</div>
    <?php ActiveForm::end(); ?>
	
	<script>
	$(function(){
		$("#thumbUpload").fileinput({
			language : "zh",
			uploadUrl : "<?= \yii\helpers\Url::to(['upload/file']) ?>",
			overwriteInitial: true,
			showClose: false,
			showCaption: false,
			showBrowse: false,
			browseOnZoneClick: true,
			removeLabel: '',
			removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
			removeTitle: 'Cancel or reset changes',
			elErrorContainer: '#thumbUploadError',
			msgErrorClass: 'alert alert-block alert-danger',
			defaultPreviewContent: '<img src="<?= \Wskm::getStaticUrl().'img/thumb-add.png' ?>" alt="" style="width:130px;cursor: pointer;">',
			layoutTemplates: {main2: '{preview}'},
			uploadExtraData : { 
				'<?= Yii::$app->request->csrfParam ?>' : '<?= Yii::$app->request->getCsrfToken() ?>',
				content_id : '<?= $model->id ?>',
				category_id : '<?= $model->category_id ?>',
				preview : 1 
			},
			<?php if($model->thumb){ ?>
			'initialPreview' : [
				"<img src='<?= $model->thumb ?>' class='file-preview-image kv-preview-data' style='width:160px' >"
			],
			'initialPreviewConfig' : [
				{
					'width' : '160px',
					'url' : '<?= \yii\helpers\Url::to(['upload/del']) ?>',
					'extra' : {
						'url' : '<?= $model->thumb ?>'
					}
				},
			],
			layoutTemplates : {
				footer : '<div class="file-thumbnail-footer">\n' +
			'    {actions}\n' +
			'</div>'
			},
			<?php } ?>
			allowedFileExtensions: ["jpg", "png", "gif"]
		}).on('fileloaded', function(event, file, previewId, index, reader) {
			$('#thumbUpload').fileinput('upload');
		}).on('fileuploaded', function(event, data, previewId, index) {
			var response = data.response; //files = data.files, extra = data.extra, 
			if (response && response.initialPreviewConfig) {
				var imgurl = response.initialPreviewConfig[0].extra.img;
				var id = response.initialPreviewConfig[0].extra.id;
				$('#content-thumb').val(imgurl);
				$('#content-filelink').append('<input type="hidden" name="fids[]" id="fids-'+ id +'" value="'+ id +'">');
			}
			
			console.log('fileuploaded');
		}).on('filedeleted', function(event, key, jqXHR, data) {
			console.log('filedeleted');
			$('#content-thumb').val('');
		}).on('fileuploaderror', function(event, data, msg) {
			tip(msg, '.kv-avatar');
			
			$('#thumbUpload').fileinput('refresh').fileinput('enable');
		 }).on('filedeleteerror', function(event, data, msg) {
			tip(msg, '.kv-avatar');
			$('#content-thumb').val('');
			$('#thumbUpload').fileinput('refresh').fileinput('enable');
		 });
			
	});
	</script>
	
	<script id="tplUploadRow" type="text/html" >
		{{ if type == 'image' }}
		<div id="uploadFile-{{id}}" style="margin: 5px;text-align: center;width:70px; height:70px; position: relative;overflow: hidden;"  class="pull-left" >		
			<img src="{{url}}" height="70"   class="img-rounded">
			<a href="javascript:;" onclick="uploadRowDel({{id}})"  ><span class="glyphicon glyphicon-trash text-danger " style="position: absolute;font-size: 10px;left:18px;bottom: 6px;top: auto;" ></span></a>
			<a href="javascript:;" onclick="uploadRowFile('{{url}}', '{{name}}', '{{type}}', '{{id}}', '{{width}}')" ><span class="glyphicon glyphicon-plus" style="position: absolute;font-size: 10px;right: 18px;bottom: 6px;top: auto;color: #333;" ></span></a>
		</div>
		{{ else }}
		<div id="uploadFile-{{id}}" style="margin: 5px;text-align: center;width:70px; height:70px;border-radius: 6px; border: 1px solid #dcdcdc; position: relative;overflow: hidden;"  class="pull-left" >		
				<b style="font-size: 14px;display: inline-block;margin-top: 18px;" class="glyphicon glyphicon-file" >{{type}}</b>
				<a href="javascript:;" onclick="uploadRowDel({{id}})" ><span class="glyphicon glyphicon-trash text-danger " style="position: absolute;font-size: 10px;left:18px;bottom: 6px;top: auto;" ></span></a>
				<a href="javascript:;" onclick="uploadRowFile('{{url}}', '{{name}}', '{{type}}', '{{id}}')" ><span class="glyphicon glyphicon-plus" style="position: absolute;font-size: 10px;right: 18px;bottom: 6px;top: auto;" ></span></a>
		</div>
		{{ /if }}
	</script>

	<script>
		var layerIndex;
		var defaultWidth = parseInt('<?= \service\Setting::getConf('content', 'defaultWidth') ?>');
		var setupCallback = function(editor){
			editor.addButton('upload', {
				icon: 'upload',
				tooltip: "Upload",
				onclick: function(){					
					loadShow();
					$.get('<?= Url::to(['upload/content', 'content_id' => $model->id ]) ?>', { z : Math.random() *1000}, function(str){
						loadHide();
						
						layerIndex = layer.open({
							type: 1
							,title: '<?=\Wskm::t('Upload')?>'
							,area: ['380px', '380px']
							,shade: 0
							,anim: -1
							,offset: [ 
							]
							,id : 'uploadLayer'
							,content: str
						});

						$('#layerUpload').fileinput().on('fileloaded', function(event, file, previewId, index, reader) {
							console.log("fileloaded");
							$('#layerUpload').fileinput('upload');
						}).on('fileuploaded', function(event, data, previewId, index) {
							var response = data.response; 
							var html = template('tplUploadRow', response);
							
							$('#uploadContentWrap').prepend(html);
							$('#layerUpload').fileinput('refresh').fileinput('enable');
						}).on('fileuploaderror', function(event, data, msg) {
							tip(msg, '#uploadLayer');
							$('#layerUpload').fileinput('refresh').fileinput('enable');
						 });

					});

				}
			});
		}
		
		//flatpickr.l10ns.default.firstDayOfWeek = 1; 
		flatpickr("#content-updated_at", {
			locale : "zh",
			time_24hr : true,
			allowInput : true,
			minuteIncrement : 1,
			enableSeconds : true,
			enableTime: true
		});
		
		function uploadRowDel(id) {
			confirmBox('<?=\Wskm::t('Are you sure you want to delete this item?')?>', function(){
				$('#content-filelink').find('#fids-' + id).remove();
				
				$.post('<?= Url::to(['upload/del']) ?>',{ id : id},function(data){
					if (data.error) {
						tip(data.error, '#uploadFile-'+id);
						return;
					}
					$('#uploadFile-' + id).fadeOut(function(){ 
						$(this).remove();
					});
				}, 'json');
			});
		}
		
		function uploadRowFile(url, name, type, id, width) {
			defaultWidth = defaultWidth ? defaultWidth : 300;
			if (type == 'image') {
				var w = width > defaultWidth ? defaultWidth : width;
				editorAdd('<img src="' + url + '" width="'+ w +'" alt="'+ name + '" />');
			}else{
				editorAdd('<a href="' + url + '" target="_blank"><span class="glyphicon glyphicon-file" >' + name + '</span></a>');
			}
			
			$('#content-filelink').append('<input type="hidden" name="fids[]" id="fids-'+ id +'" value="'+ id +'">');
			//layer.close(layerIndex);
		}
	
		//$("#form-content").submit( function () {
		//} );
		$("#form-content").bind('beforeValidate', function(){ 
			tinyMCE.triggerSave();
			return true;
		});
	</script>
	
	<?= \wskm\web\Bower::getEditor('article-detail', [
		'url' => Url::to(['/upload/file', 'edit' => 1]),
		'name' => 'Uploads[file]',
		'setupCallback' => 'setupCallback',
	]) ?>

</div>
