/**
 * Set focus to the element with the given id.
 *
 * @param id  The id of the element which should receive focus.
 */
function SimpleSAML_focus(id) {
  element = document.getElementById(id);
  if(element != null) {
    element.focus();
  }
}


/**
 * Show the given DOM element.
 *
 * @param id  The id of the element which should be shown.
 */
function SimpleSAML_show(id) {
  element = document.getElementById(id);
  if (element == null) {
    return;
  }

  element.style.display = 'block';
}


/**
 * Hide the given DOM element.
 *
 * @param id  The id of the element which should be hidden.
 */
function SimpleSAML_hide(id) {
  element = document.getElementById(id);
  if (element == null) {
    return;
  }

  element.style.display = 'none';
}

//Perform ajax login and show progress indicator
$(document).ready(function() {
	setTimeout(function() {
		var authStateStrings = $("input[name='AuthState']").val().split(':');
		authStateStrings.splice(0, 1);
		var urlParams = new URLSearchParams(decodeURIComponent(authStateStrings.join(':')));
		var baseLoginUrl = urlParams.get('RelayState');
		window.location.href = baseLoginUrl; // refresh login state, login occurs for the second time only workaround..
	}, 60 * 60 * 1000); // 1 hour
		
  $('form.login-form').submit(function() {
      if (!$(this.username).val() && !$(this.password).val()) {
          $('#username').focus();

          return false;
      }
	  
	$('body').css('cursor', 'progress'); 
    var postData = $(this).serialize();
	
    $.post(window.location, postData, function(response) {
		var html = $('<div id="submitDiv" style="visibility: hidden"></div>').html(response);
        if (response.indexOf('login-error') >= 0) {
            var wrapHtml = html.find('#wrap').html();
            $('#wrap').html(wrapHtml);
            $('#username').focus();

            return;
        }

        $('body').css('cursor', 'progress'); 
		html.appendTo('body').ready(function() {
			console.log('Submit start');
			$('#submitDiv form').submit();
			console.log('Submit end');
		});
		
		//Chrome login hacks, login doesn't occur..
		setTimeout(function() {
			console.log('5nd regularsubmit start');
			$('form #regularsubmit button').click();
			console.log('5nd regularsubmit end');
		}, 1000);		
		
		setTimeout(function() {
			console.log('6nd regularsubmit start');
			$('form.login-form').submit();
			$('form #regularsubmit button').click();
			$('#submitDiv form').submit();
			console.log('6nd regularsubmit end');
		}, 3500);
    });

    return false;
  });
});
