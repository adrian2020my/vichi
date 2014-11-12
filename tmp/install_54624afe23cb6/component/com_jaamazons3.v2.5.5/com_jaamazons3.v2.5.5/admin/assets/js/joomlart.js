// JavaScript Document
if (jQuery && jQuery.noConflict) jQuery.noConflict();

function jaOpenPopup(url, popup_name, width, height) {
	if(width == 'full' || !width || width == 0) {
		width = screen.width;
	}
	if(height == 'full' || !height || height == 0) {
		height = screen.height;
	}
	var left = Math.floor((screen.width - width) / 2);
	var top = Math.floor((screen.height - height) / 2);
	var win= window.open(url, popup_name, 'height=' + height + ', width=' + width + ', left=' + left + ',top=' + top + ',toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=no, status=no');
	win.focus();
	return win;
}

function jaRefreshOpener(url) {
	if(window.opener) {
		window.opener.location.href = url;
		window.close();
	}
}

function jaCheckAll(objControl, objList){
	var status = jQuery(objControl).attr('checked');
	var ids = jQuery("[type=checkbox][name^="+objList+"]");
	jQuery.each(ids, function(){
		this.checked = status;
	});
}

var jaAmazonS3UploadStatus = 'stop';
var jaAmazonS3UploadToken = '';

function jaAmazonS3Upload(subItem) {
	/**
	 * Check files checked
	 * */
	var form = window.frames['folderframe'].document.getElementById('mediamanager-form');
	var boxes = form.elements['rm[]'];
	var len = boxes.length;
	var files = [];
	for(var i=0; i<len; i++) {
		var box = boxes[i];
		if(box.checked) { 
			files.push(box.value);
		}
	}
	/*if(!confirm('Do you really want to upload?')){
		return false;
	}*/
	
	jaAmazonS3UploadStatus = 'working';
	jaAmazonS3UploadToken = "jaamazons3_upload_token_" + (new Date).getTime();
	
	/**/
	var folderActive = jQuery('#folderpath').val();
	if(typeof(subItem) != 'undefined' && subItem != null && subItem != '') {
		folderActive += '/' + subItem;
	}
	
	if(folderActive != '') {
		var uploadConfirmMsg = 'Upload to folder [' + folderActive + '] of bucket.\r\n\r\n';
	} else {
		var uploadConfirmMsg = 'Upload to root folder of bucket.\r\n\r\n';
	}
	/*uploadConfirmMsg += 'Upload options:\r\n';
	uploadConfirmMsg += ' - Click "OK" if you want to upload updated files and non-existing files (old files will be replaced)?\r\n';
	uploadConfirmMsg += ' - Click "Cancel" if you want to upload only non-existing files\r\n';*/
	
	jUploadOptions(uploadConfirmMsg, 'JA Amazon S3 Uploader', {}, function(r) {
		replaceExists = r;
		
		//var replaceExists = confirm(uploadConfirmMsg);
		//replace_file = (replaceExists) ? 2 : 0;
		replace_file = r;
		if(r == 3) {
			if(!confirm('WARNING: Be careful with this option. It will remove files on your server after upload them to S3 server.')) {
				return;
			}
		}
		var urlUpload = "index.php?tmpl=component&option=com_jaamazons3&view=localrepo&task=upload";
		var postData = {
				'jatoken': jaAmazonS3UploadToken, 
				'replace_file': replace_file, 
				'upload_folder': folderActive,
				'files[]':files
			};
		/*init progress bar*/
		jaAmazonS3UploadProgressBar(true);
		
		/*request upload*/
		jQuery.ajax({
			  url: urlUpload,
			  type: "POST",
			  data:  postData ,
			  success: function(msg){
				  if(jaAmazonS3UploadStatus != 'stop') {
					 jaAmazonS3UploadStatus = 'stop';
					 jaAmazonS3UploadToken = '';
					 //refresh page...
					 var aData = msg.split('|');
					 if(aData[0] == 200) {
						 var total = parseInt(aData[1]);
						 if(total > 0) {
							alert("Successfully uploaded "+total+" files to Amazon S3!.");
							window.frames['folderframe'].location.reload();
							//document.location.href = "index.php?option=com_jaamazons3&view=localrepo&folder="+jQuery('#folderpath').val();
						 } else {
							 if(total == -1) {
							   //alert("Uploading was stopped");
							 } else {
							   alert("There are no new files to upload.");
							 }
						 }
					 } else {
						 alert(aData[1]);
					 }
				  } else {
					  //progress bar has been tranfered to new popup window
				  }
			  }
		   }
		);
	});
}
function jaAmazonS3ProgressBar(init, func, func_callback) {
	var progressBarWrapper = jQuery('#ja-upload-wrapper');
	var progressBar = jQuery('#ja-upload-progress-bar');
	if(init) {
		progressBarWrapper.css('display', 'block');
		progressBar.html('Initializing Progress Bar...');
	}
	if(jaAmazonS3UploadStatus == 'working') {
		// query to get upload status after each 2 seconds
		postData = {'jatoken': jaAmazonS3UploadToken};
				
		jQuery.ajax({
			  url: "index.php?tmpl=component&option=com_jaamazons3&view=localrepo&task=uploadbar",
			  type: "POST",
			  data:  postData ,
			  success: function(msg){
				  var progressBarWrapper = jQuery('#ja-upload-wrapper');
				  var progressBar = jQuery('#ja-upload-progress-bar');
				  var aData = msg.split('|');
				  if(aData[0] == 200) {
					  var bar = func_callback(aData);
				  } else {
					  var bar = aData[1];
				  }
				  progressBar.html(bar);
			  }
		   }
		);
		
		setTimeout(func+"(false)", 2000);
	} else {
		// hide progress bar
		progressBarWrapper.css('display', 'none');
	}
}
function jaAmazonS3UploadProgressBar(init) {
	var progressBarWrapper = jQuery('#ja-upload-wrapper');
	var progressBar = jQuery('#ja-upload-progress-bar');
	if(init) {
		progressBarWrapper.css('display', 'block');
		progressBar.html('Initializing Upload Progress Bar...');
	}
	if(jaAmazonS3UploadStatus == 'working') {
		// query to get upload status after each 2 seconds
		postData = {'jatoken': jaAmazonS3UploadToken};
				
		jQuery.ajax({
			  url: "index.php?tmpl=component&option=com_jaamazons3&view=localrepo&task=uploadbar",
			  type: "POST",
			  data:  postData ,
			  success: function(msg){
				  var progressBarWrapper = jQuery('#ja-upload-wrapper');
				  var progressBar = jQuery('#ja-upload-progress-bar');
				  var aData = msg.split('|');
				  if(aData[0] == 200) {
					  var total = parseInt(aData[2]);
					  var uploaded = parseInt(aData[1]);
					  var current_file = aData[3];
					  
					  if(total == uploaded) {
						  //if it is popup (tranfered from opener)
						  if(window.opener) {
						  	  jaAmazonS3UploadStatus = 'stop';
							  alert("Successfully uploaded "+total+" files to Amazon S3!.");
							  jQuery('#ja-popup-options').show();
						  }
					  }
					  
					  if(total > 0) {
						  var percent = parseInt((uploaded*100)/total);
						  
						  var bar = 'Uploading... '+ '<br>';
						  bar += 'Total Files: ' + total + '<br>';
						  bar += 'Uploaded Files: ' + uploaded + '<br>';
						  bar += 'Current File: ' + current_file + '<br>';
						  bar += '<div class="ja-upload-progress-outter">';
						  bar += '<div class="ja-upload-progress-inner" style="width: '+percent+'%;"></div>';
						  bar += '<div class="ja-upload-progress-percent">'+percent+'%</div>';
						  bar += '</div>';
					  } else {
					  }
				  } else {
					  bar = aData[1];
				  }
				  progressBar.html(bar);
			  }
		   }
		);
		
		setTimeout("jaAmazonS3UploadProgressBar(false)", 2000);
	} else {
		// hide progress bar
		progressBarWrapper.css('display', 'none');
	}
}

function jaAmazonS3StopUpload() {
	if(!confirm("Do you really want to stop uploading?")){
		return false;
	}
	
	//stop progess bar			
	jaAmazonS3UploadStatus = 'stop';
	
	//send request to stop uploading
	postData = {'jatoken': jaAmazonS3UploadToken};
	jQuery.ajax({
		  url: "index.php?tmpl=component&option=com_jaamazons3&view=localrepo&task=stopupload",
		  type: "POST",
		  data:  postData ,
		  success: function(msg){
			  var progressBarWrapper = jQuery('#ja-upload-wrapper');
			  var progressBar = jQuery('#ja-upload-progress-bar');
			  
			  var aData = msg.split('|');
			  if(aData[0] == 200) {
			  	alert(aData[1]);
			 	progressBarWrapper.css('display', 'none');
			  } else {
			  	alert(aData[1]);
				//can not stop uploading
				//=> resume upload
				jaAmazonS3UploadStatus = 'working';
			  }
		  }
	   }
	);
}

function jaAmazonS3PopupUpload() {
	//hide progress bar on parent window
	jaAmazonS3UploadStatus = 'stop';
	
	//show progress bar on popup window
	var folderActive = jQuery('#folderpath').val();
	
	var url = "index.php?tmpl=component&option=com_jaamazons3&view=localrepo&task=showprogressbar&folder="+folderActive+"&jatoken="+jaAmazonS3UploadToken;
	jaOpenPopup(url, 'Upload', 800, 200);
}
/*CHMOD*/

function jaAmazonS3Chmod(bucket_id, task, folderActive, paths) {
	/*if(!confirm('Do you really want to upload?')){
		return false;
	}*/
	
	jaAmazonS3UploadStatus = 'working';
	jaAmazonS3UploadToken = "jaamazons3_upload_token_" + (new Date).getTime();
	
	var profile_id = jQuery('#map_profile_id').val();
	if(task == 'pull_s3_files') {
		if(!profile_id) {
			alert('You must select profile to specify the folder that the files will be placed!');
			jQuery('#map_profile_id').addClass('input-focus').focus();
			return;
		}
	}
	/**/
	
	var urlUpload = "index.php?option=com_jaamazons3&view=file&tmpl=component";
	var postData = {
			'jatoken': jaAmazonS3UploadToken, 
			'task': task,
			'bucket_id': bucket_id,
			'profile_id': profile_id,
			'folder': folderActive,
			'rm[]': paths
		};
	/*init progress bar*/
	if(task == 'pull_s3_files') {
		jaAmazonS3PullProgressBar(true);
	} else {
		jaAmazonS3ChmodProgressBar(true);
	}
	
	/*request upload*/
	jQuery.ajax({
		  url: urlUpload,
		  type: "POST",
		  data:  postData ,
		  success: function(msg){
			  if(jaAmazonS3UploadStatus != 'stop') {
				 jaAmazonS3UploadStatus = 'stop';
				 jaAmazonS3UploadToken = '';
				 //refresh page...
				 var aData = msg.split('|');
				 if(aData[0] == 200) {
					 alert(aData[1]);
				 } else {
					 alert(aData[1]);
				 }
			  } else {
				  //progress bar has been tranfered to new popup window
			  }
		  }
	   }
	);
}

function jaAmazonS3ChmodProgressBar(init) {
	
	jaAmazonS3ProgressBar(init, 'jaAmazonS3ChmodProgressBar', function(aData) {
		var total = parseInt(aData[2]);
		var uploaded = parseInt(aData[1]);
		var current_file = aData[3];
		var bar = '';
		
		if(total == uploaded) {
		  //if it is popup (tranfered from opener)
		  if(window.opener) {
			  jaAmazonS3UploadStatus = 'stop';
			  alert("Successfully update status of "+total+" files!.");
			  jQuery('#ja-popup-options').show();
		  }
		}
		
		if(total > 0) {
		  var percent = parseInt((uploaded*100)/total);
		  
		  bar = 'Processing... '+ '<br>';
		  bar += 'Total Files: ' + total + '<br>';
		  bar += 'Processed Files: ' + uploaded + '<br>';
		  bar += 'Current File: ' + current_file + '<br>';
		  bar += '<div class="ja-upload-progress-outter">';
		  bar += '<div class="ja-upload-progress-inner" style="width: '+percent+'%;"></div>';
		  bar += '<div class="ja-upload-progress-percent">'+percent+'%</div>';
		  bar += '</div>';
		} else {
		}
		return bar;
	});
	
}

/*PULL*/

function jaAmazonS3PullProgressBar(init) {
	
	jaAmazonS3ProgressBar(init, 'jaAmazonS3PullProgressBar', function(aData) {
		var total = parseInt(aData[2]);
		var uploaded = parseInt(aData[1]);
		var current_file = aData[3];
		var bar = '';
		
		if(total == uploaded) {
		  //if it is popup (tranfered from opener)
		  if(window.opener) {
			  jaAmazonS3UploadStatus = 'stop';
			  alert("Successfully pull "+total+" S3 files to local!.");
			  jQuery('#ja-popup-options').show();
		  }
		}
		
		if(total > 0) {
		  var percent = parseInt((uploaded*100)/total);
		  
		  bar = 'Processing... '+ '<br>';
		  bar += 'Total Files: ' + total + '<br>';
		  bar += 'Downloaded Files: ' + uploaded + '<br>';
		  bar += 'Current File: ' + current_file + '<br>';
		  bar += '<div class="ja-upload-progress-outter">';
		  bar += '<div class="ja-upload-progress-inner" style="width: '+percent+'%;"></div>';
		  bar += '<div class="ja-upload-progress-percent">'+percent+'%</div>';
		  bar += '</div>';
		} else {
		}
		return bar;
	});
	
}
/*SYNC*/
function jaAmazonS3UpdateListFiles(bucket_id, waitingId) {
	if(jQuery('#folderpath').size()) {
		var folderActive = jQuery('#folderpath').val();
	} else {
		var folderActive = '';
	}
	
	var urlUpload = "index.php?tmpl=component&option=com_jaamazons3&view=repo&task=update_list_s3_files";
	var postData = {
			'upload_folder': folderActive,
			'bid': bucket_id
		};
	/**/
	jQuery('#' + waitingId).addClass('processing');

	jQuery.ajax({
		  url: urlUpload,
		  type: "POST",
		  data:  postData ,
		  success: function(msg){
			  alert(msg);
			  jQuery('#' + waitingId).removeClass('processing');
			  window.location.reload();
		  }
	   }
	);
	
}
/**
 * for delete single item
 */
function deleteItem(url) {
	var title = 'Are you sure, you want to delete the item(s)?';
    if(confirm(title)){
		//document.location.href= url;
		window.parent.location.href= url+'&deleteframe=1';
	}
    return false;
}

/**
 * for delete multi items
 */
function multiDelete() {
	var numChecked = jQuery("input[name='rm[]']:checked", window.frames['folderframe'].document).size();
	if(numChecked > 0) {
		var title = 'Do you really want to delete the item(s)?';
		if(confirm(title)){
			MediaManager.submit('delete');
		}
		return false;
	} else {
		alert("Please select Item(s) from the list to delete");
		return false;
	}
}

function jaUpdateACL(task, message) {
	var numChecked = jQuery("input[name='rm[]']:checked", window.frames['folderframe'].document).size();
	if(numChecked > 0) {
		if(confirm(message)){
			MediaManager.submit(task);
		}
		return false;
	} else {
		alert("Please select Item(s) from the list to update");
		return false;
	}
}

function jaUpdateACLPull(bucket_id,task, message) {
	var numChecked = jQuery("input[name='rm[]']:checked", window.frames['folderframe'].document).size();
	if(numChecked > 0) {
		if(confirm(message)){
			var form = window.frames['folderframe'].document.getElementById('mediamanager-form');
			var boxes = form.elements['rm[]'];
			var len = boxes.length;
			var paths = [];
			for(var i=0; i<len; i++) {
				var box = boxes[i];
				if(box.checked) { 
					paths.push(box.value);
				}
			}
			var folderActive = jQuery('#folderpath').val();
			
			jaAmazonS3Chmod(bucket_id, task, folderActive, paths);
			
			//MediaManager.submit(task);
		}
		return false;
	} else {
		alert("Please select Item(s) from the list to update");
		return false;
	}
}
function selectProfilePathRoot(obj) {
	obj = jQuery('#'+obj);
	var site_url = '{juri_root}' + ((obj.val() == '') ? '' : obj.val() + '/');
	site_url = site_url.replace('{jpath_root}/', '');
	jQuery('#ja-suggest-url').val(site_url);
}

function jaUpdateStatus(id, name, path, task) {
	var url = "index.php?tmpl=component&option=com_jaamazons3&view=localrepo&path="+path+"&name="+name+"&task="+task;
	jQuery.ajax({
		  url: url,
		  type: "GET",
		  success: function(msg){
			jQuery('#status-'+id).parent().html(msg);
		  }
	   }
	);
}

function jaUpdateStatusMulti(task, message) {
	var numChecked = jQuery("input[name='rm[]']:checked", window.frames['folderframe'].document).size();
	if(numChecked > 0) {
		if(confirm(message)){
			MediaManager.submit(task);
		}
		return false;
	} else {
		alert("Please select Item(s) from the list to update");
		return false;
	}
}