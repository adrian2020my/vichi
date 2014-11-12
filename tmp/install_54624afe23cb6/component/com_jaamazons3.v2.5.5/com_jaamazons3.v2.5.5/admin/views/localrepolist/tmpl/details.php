<?php defined('_JEXEC') or die('Restricted access'); ?>

<script type="text/javascript" language="javascript">
/*<![CDATA[*/
function updateListDisabled() {
	jQuery('#ja-localfile').find('tr:has(span.item-disabled)').addClass('item-disabled');
	jQuery('#ja-localfile').find('tr:has(span.item-enabled)').removeClass('item-disabled');
}

jQuery(document).ready(function () {
	updateListDisabled();
	jQuery("input[name='rm[]']").each(function(i){
		jQuery(this).click(function(){
			var id = this.id;
			var trId = id.replace(/^chk/i, 'local-item');
			if(this.checked) {
				jQuery('#' + trId).addClass('selected');
			} else {
				jQuery('#' + trId).removeClass('selected');
			}
		});
	});
	
	jQuery('#chkAll').click(function(){
		if(this.checked) {
			jQuery("tr[id^='local-item-']").addClass('selected');
		} else {
			jQuery("tr[id^='local-item-']").removeClass('selected');
		}
	});
});
/*]]>*/
</script>
<form action="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=file&amp;tmpl=component&amp;folder=<?php echo $this->state->folder; ?>" method="post" id="mediamanager-form" name="mediamanager-form">
  <div class="manager">
    <table width="100%" cellspacing="0" class="adminlist table table-striped" id="ja-localfile">
      <thead>
        <tr>
          <th width="20"><input type="checkbox" name="chkAll" id="chkAll" value="" onclick="jaCheckAll(this, 'rm');" /></th>
          <th><?php echo JText::_('NAME' ); ?></th>
          <th width="20"><span class="hasTip" title="<?php echo JText::_('SYNCHRONIZE' ); ?>::<?php echo JText::_('ENABLEDISALBE_UPLOAD_ITEMS_TO_S3_AND_REPLACE_THEM_WITH_S3_LINKS' ); ?>"><?php echo JText::_('SYNC' ); ?></span></th>
          <th width="150"><?php echo JText::_('LAST_UPLOAD' ); ?></th>
          <th><?php echo JText::_('ACTIONS' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php $this->cnt = 0; ?>
        <?php echo $this->loadTemplate('up'); ?>
        <?php 
		for ($i=0,$n=count($this->folders); $i<$n; $i++) :
			$this->setFolder($i);
			echo $this->loadTemplate('folder');
		endfor; 
		?>
        <?php 
		for ($i=0,$n=count($this->documents); $i<$n; $i++) :
			$this->setDoc($i);
			echo $this->loadTemplate('doc');
		endfor; 
		?>
        <?php 
		for ($i=0,$n=count($this->images); $i<$n; $i++) :
			$this->setImage($i);
			echo $this->loadTemplate('img');
		endfor; 
		?>
      </tbody>
    </table>
  </div>
  <input type="hidden" name="task" value="list" />
  <input type="hidden" name="username" value="" />
  <input type="hidden" name="password" value="" />
  <?php echo JHtml::_( 'form.token' ); ?>
</form>
