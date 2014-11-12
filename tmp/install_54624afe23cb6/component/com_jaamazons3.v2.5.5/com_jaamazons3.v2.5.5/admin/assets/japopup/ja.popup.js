/*------------------------------------------------------------------------
# $JA#PRODUCT_NAME$ - Version $JA#VERSION$ - Licence Owner $JA#OWNER$
# ------------------------------------------------------------------------
# Copyright (C) 2004-2008 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: J.O.O.M Solutions Co., Ltd
# Websites:  http://www.joomlart.com -  http://www.joomlancers.com
# This file may not be redistributed in whole or significant part.
-------------------------------------------------------------------------*/
// JavaScript Document

// Must re-initialize window position

function jaCreatePopup(target, jaWidth, jaHeight, title, dsave, titlesave, context) {
	if (!jaWidth)
		jaWidth = 700;
	if (!jaHeight)
		jaHeight = 500;
		
	//where to handle response data
	if (!context) {
		context = 'parent';	
	}
		
	if(!titlesave)
		titlesave='Save';
	
	//message holder
	if(jQuery('#system-message').size() == 0){
		jQuery('<div>').attr( {
				'id' :'system-message',
				'style': ''
			}).html(' ').prependTo('#jacom-mainwrap');
	}
	//
	
	var Obj = document.getElementById('ja-popup-wrap');
	if (!Obj) {
		var content = jQuery('<div>').attr( {
			'id' :'ja-popup'
		}).appendTo(document.body);
		var jacForm = jQuery('<div>').attr( {
			'id' :'ja-popup-wrap',
            'style' :'top: 0px;display:none;'
		}).appendTo(content);
		//jacForm.appendTo(content);
		
		/* JA POPUP HEADER */
		jQuery('<div>').attr( {
			'id' :'ja-popup-header-wrap'
		}).appendTo(jacForm);
		jQuery('<div>').attr( {
			'id' :'ja-popup-tl'
		}).appendTo(jQuery('#ja-popup-header-wrap'));
		jQuery('<div>').attr( {
			'id' :'ja-popup-tr'
		}).appendTo(jQuery('#ja-popup-header-wrap'));
		jQuery('<div>').attr( {
			'id' :'ja-popup-header'
		}).appendTo(jQuery('#ja-popup-header-wrap'));
		jQuery('<div>').attr( {
			'class' :'inner'
		}).appendTo(jQuery('#ja-popup-header'));

		if (title) {
			jQuery('<div>').attr( {
				'class' :'ja-popup-title'
			}).html(title).appendTo(jQuery('#ja-popup-header .inner'));

		}
		jQuery('<button>').attr( {
			'id' :'ja-close-button'
		}).html('Close').appendTo(jQuery('#ja-popup-header .inner'));
		
		jQuery("#ja-close-button").click( function() { jacFormHide(); } );
		
		/* end JA POPUP HEADER */

		/* JA POPUP CONTENT */
		jQuery('<div>').attr( {
			'id' :'ja-popup-content-wrap'
		}).appendTo(jacForm);        
		jQuery('<div>').attr( {
			'id' :'ja-popup-content'
		}).appendTo(jQuery('#ja-popup-content-wrap'));
		jQuery('<div>').attr( {
			'class' :'inner'
		}).appendTo(jQuery('#ja-popup-content'));
        jQuery('<div>').attr( {
            'id' :'ja-popup-wait'
        }).appendTo(jQuery('#ja-popup-content .inner'));
		/* end JA POPUP CONTENT */
		
			
		
		/* JA POPUP FOOTER */
		jQuery('<div>').attr( {
			'id' :'ja-popup-footer-wrap'
		}).appendTo(jacForm);
		jQuery('<div>').attr( {
			'id' :'ja-popup-bl'
		}).appendTo(jQuery('#ja-popup-footer-wrap'));		
		jQuery('<div>').attr( {
			'id' :'ja-popup-br'
		}).appendTo(jQuery('#ja-popup-footer-wrap'));
		jQuery('<div>').attr( {
			'id' :'ja-popup-footer'
		}).appendTo(jQuery('#ja-popup-footer-wrap'));
		jQuery('<div>').attr( {
			'class' :'inner'
		}).appendTo(jQuery('#ja-popup-footer'));
        
		if (!dsave) {
			jQuery('<button>').attr( {
				'id' :'ja-save-button'
			}).html(titlesave).appendTo(jQuery('#ja-popup-footer .inner'));	
			//if(jatask != "open_attach_file"){				
				jQuery("#ja-save-button").click( function() { JAsubmitbutton(context); } );
			//}else{				
				//jQuery("#ja-save-button").click( function() { JAsubmitattach(context); } );
			//}
            //jQuery('<button>').attr( {
//                'id' :'ja-cancel-button'
//            }).html('Cancel').appendTo(jQuery('#ja-popup-footer .inner'));    
//            jQuery("#ja-cancel-button").click( function() { jacFormHide(); } );
			
			//custom button
			jQuery('<button>').attr( {
				'id' :'ja-save2-button',
				'class': 'japopup_btn'
			}).css({
				'display':'none'
			}).html(titlesave).appendTo(jQuery('#ja-popup-footer .inner'));	
		}   
		
		jQuery('<span>').appendTo(jQuery('#ja-popup-footer .inner'));
		jQuery('#ja-popup-footer .inner span').html('&copy; Copyright by JoomlArt');
		
		/* end JA POPUP FOOTER */
	}

	// Set jacFormWidth + 40
	if (title)
		jQuery('#ja-popup-title').width(jaWidth-20);

	//jQuery('#ja-popup-content').width(jaWidth);

	var myWidth = 0, myHeight = 0;

	myWidth = jQuery(window).width(); 
	myHeight = jQuery(window).height();
	
	//set frame to center
	var wrapTop = Math.floor((myHeight-jaHeight)/2);
	jQuery('#ja-popup').css('top', wrapTop);
	jQuery('#ja-popup').css('left', 0);
	//
  
	var yPos;

	if (jQuery.browser.opera && jQuery.browser.version > "9.5"
			&& jQuery.fn.jquery <= "1.2.6") {
		yPos = document.documentElement['clientHeight'] - 20;
	} else {
		yPos = jQuery(window).height() - 20;
	}

	var leftPos = (myWidth - jaWidth) / 2;

	jQuery('#ja-popup-wrap').css('zIndex', cGetZIndexMax() + 1);

	/*
	 * jQuery.ajax({ url: jatask, cache: false, success: function(html){
	 * jQuery("#jaFormContent").append(html); } });
	 */
	/*var aException = ['tmpl', 'option', 'view'];
	var aParams = jaGetUrlParams(target);
	var url = siteurl;
	
	jQuery.each(aParams, function(n, val) {
		if(jQuery.inArray(n, aException) == -1) {
			url = url + '&' + n + '=' + val;
		}
	});
	*/
	var url = target;
	
	url = url.replace(/&amp;/gi, '&');
	if (jQuery('#iContent').length >0){
		jQuery('#iContent').attr('src',url);
		jQuery('#ja-popup-title').html(title);
	}
	else{
		jQuery('<iframe>').attr( {
			'id' :'iContent',
			'frameborder': '0',
			'src' :url,
			'width' :jaWidth,
			'height' :jaHeight-80
		}).appendTo(jQuery('#ja-popup-content .inner'));
		jQuery("#iContent").load( function() { loadIFrameComplete(); } );
	}
	/*
	 * Set editor position, center it in screen regardless of the scroll
	 * position
	 */
	
    jQuery("#ja-popup-wrap").css('marginTop', '5px');
    jQuery('#ja-popup-wrap').css('left', leftPos);
    
    if(jQuery.browser.msie){
    	if(jQuery.browser.version=='6.0'){

            jQuery(window).scroll(function() {
                jQuery('#ja-popup-wrap').css({'top': jQuery(this).scrollTop() + "px", 'left': leftPos});
            });
            
            jQuery("#ja-popup-wrap").css('top', jQuery(this).scrollTop() + 'px');
            jQuery('#ja-popup-wrap').css('left', leftPos);
        }
	}
	/*
	 * Set height and width for transparent window
	 */

	jQuery('#ja-popup-header-wrap').css('width', (jaWidth));
	jQuery('#ja-popup-content-wrap').css('width', (jaWidth));
	jQuery('#ja-popup-footer-wrap').css('width', (jaWidth));

	jQuery('#ja-popup-wrap').fadeIn();

}

function hiddenMessage() {
	jQuery('#system-message', window.parent.document).html('');
}
function jaGetUrlParams(url)
{
    var vars = {}, hash;
	url = url.replace(/&amp;/gi, '&');
	
	var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
    return vars;
}

function jacFormHide() {
	
/*	if (jQuery('#ja-popup-footer-wrap').get().length > 0)
		jQuery('#ja-popup-footer-wrap').animate( {
			bottom :"0px",
			height :"0px"
		}, 200);*/
	jQuery('#ja-popup').fadeOut('fast', function() {
		jQuery(this).remove();
	});
	
}
function jacFormHideIFrame() {
	var jacForm = jQuery("#ja-popup", window.parent.document);
/*	if (jQuery('#jacomment_ar').get().length > 0)
		jQuery('#jacomment_ar').animate( {
			top :"-20px"
		}, 200, '');*/

	jacForm.fadeOut('slow', function() {
		jacForm.remove();
	});

}
function loadIFrameComplete(){
	jQuery('#ja-popup-wait',window.parent.document).css('display','none');
	//jQuery('#ja-save-button',window.parent.document).css('display','block');
	//jQuery('#ja-cancel-button',window.parent.document).css('display','block');
	jacFormActions();	
}
function jacFormActions() {
/*	if (jQuery('#ja-popup-footer-wrap').get().length > 0)
		jQuery('#ja-popup-footer-wrap').animate( {
			bottom :"0px",
			height :"30px"
		}, 200);*/

	jQuery('#ja-popup-wrap').fadeIn('fast');
}

function jacFormResize(newheight) {
	jQuery("#ja-popup-content").animate( {
		"left" :"+=50px"
	}, "slow");

	jQuery("#ja-popup-content").animate( {
		"left" :"+=50px"
	}, "slow");
	jQuery("#iContent").animate( {
		"left" :"+=50px"
	}, "slow");
	/*
	 * jQuery('#iContent', window.parent.document).animate( { height:
	 * jQuery(this).height()+30 });
	 */
}

function cGetZIndexMax() {
	var allElems = document.getElementsByTagName ? document
			.getElementsByTagName("*") : document.all; // or test for that too
	var maxZIndex = 0;

	for ( var i = 0; i < allElems.length; i++) {
		var elem = allElems[i];
		var cStyle = null;
		if (elem.currentStyle) {
			cStyle = elem.currentStyle;
		} else if (document.defaultView
				&& document.defaultView.getComputedStyle) {
			cStyle = document.defaultView.getComputedStyle(elem, "");
		}

		var sNum;
		if (cStyle) {
			sNum = Number(cStyle.zIndex);
		} else {
			sNum = Number(elem.style.zIndex);
		}
		if (!isNaN(sNum)) {
			maxZIndex = Math.max(maxZIndex, sNum);
		}
	}
	return maxZIndex;
}
/************************************************************************/

function JAsubmitbutton(context) {
    jQuery('#ja-popup-wait').css( {
        'display' :''
    });

    jQuery.post("index.php", jQuery("#iContent").contents().find("#adminForm").serialize(), function(res) {
    	jacFormHideIFrame();
        parseData_admin(res, context);
    }, 'json');
}

function JAsubmitattach(context) {
	jQuery('#ja-popup-wait').css( {
        'display' :''
    });	             	           
    jQuery.post("index.php", jQuery("#iContent").contents().find(
        "#form1").serialize(), function(res) {
        	parseData_admin(res, context);
    	}, 'json');
}

function parseData_admin(response, context) {
	//jQuery(document, window.parent.document).ready(function(){
		var reload = 0;
		var redirecturl = '';
		
		if(context == 'iframe') {
			var context = jQuery('#iContent').contents();
		} else {
			var context = window.parent.document;
		}
		jQuery.each(response.data, function(i, item) {
			var divId = item.id;
			var type = item.type;
			var value = item.value;
			if (jQuery(divId, context) != undefined) {
				if (type == 'html') {
					if (jQuery(divId, context))
						jQuery(divId, context).html(value);
					else
						alert('Element is not found');
				} else {
					if (type == 'reload') {
						if (value == 1)
							reload = 1;
					} else if(type == 'redirecturl') {
						redirecturl = value;
					} else {
						if(type=='val'){
							jQuery(divId, context).val(value);
						}else{
							jQuery(divId, context).attr(type, value);
						}
					}
				}
			}
		});
		if (reload == 1) {
			//parent.window.document.adminForm.submit();
			var win = window.parent ? window.parent : window;
			if(redirecturl != '') {
				win.location.href = redirecturl;
			} else {
				win.location.reload();
			}
		} else {
			setTimeout("hiddenMessage()", 5000);
		}
	//});
}