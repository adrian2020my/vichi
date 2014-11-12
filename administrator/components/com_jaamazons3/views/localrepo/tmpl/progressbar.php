<?php
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

$uploadToken = JRequest::getVar('jatoken');
$folder = JRequest::getVar('folder', '');

$model = JModelLegacy::getInstance ( 'localrepo', 'jaAmazonS3Model' );
$profile = $model->getActiveProfile();
if($profile !== false) {
	$profile_id = "&profile_id=".$profile->id;
}

$redirectUrl = "index.php?option=com_jaamazons3&view=localrepo{$profile_id}&folder={$folder}";
?>
<?php if(empty($uploadToken)): ?>
<h2><?php echo JText::_("INVALID_TOKEN"); ?></h2>
<?php else: ?>
<div id="ja-upload-wrapper">
    <div id="ja-upload-actions">
      <a href="#" onclick="jaAmazonS3StopUpload(); return false;" title="<?php echo JText::_("CLICK_HERE_TO_STOP_UPLOADING"); ?>"><?php echo JText::_("CANCEL_UPLOADING"); ?></a>
	</div>
    <div id="ja-upload-progress-bar">
    </div>
</div>
<div id="ja-popup-options" style="display:none">
	<input type="button" value="<?php echo JText::_("CLOSE"); ?>" onclick="window.close();" />
	<input type="button" value="<?php echo JText::_("REFRESH_PARENT_WINDOW"); ?>" onclick="jaRefreshOpener('<?php echo $redirectUrl; ?>');" />
</div>

<script type="text/javascript" language="javascript">
/*<![CDATA[*/
var jaAmazonS3UploadStatus = 'working';
var jaAmazonS3UploadToken = '<?php echo $uploadToken; ?>';
jaAmazonS3UploadProgressBar();

/*]]>*/
</script>
<?php endif; ?>