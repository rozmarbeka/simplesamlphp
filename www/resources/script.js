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
$(document).ready(function(){
  $('form.login-form').submit(function() {
      if (!$(this.username).val() && !$(this.password).val()) {
          $('#username').focus();

          return false;
      }

      var postData = $(this).serialize();
    $.post(window.location, postData, function(response) {
		var html = $('<div id="submitDiv" style="visibility: hidden"></div>').html(response);
        if (response.indexOf('login-error') >= 0) {
            var wrapHtml = html.find('#wrap').html();
            $('#wrap').html(wrapHtml);
            $('#username').focus();

            return;
        }

        $('.progress-indicator').show();
		html.appendTo('body').ready(function() {
			$('#submitDiv form').submit();
		});
		
		//Chrome login hacks, login doesn't occur..
		setTimeout(function() {
			$('#submitDiv form').submit(); //1.) Try to resubmit form
			if ($('input[name="RelayState"]').length) {
				window.location.href = $('input[name="RelayState"]').val(); //2.) Try to find redirect url in RelayState input's value
			}
			
			var samlResponse = atob($('input[name="SAMLResponse"]').val());
			var xmlDoc = $.parseXML(samlResponse);
			$.each($(xmlDoc)[0].all, function(key, node){
				if ($(node).prop("tagName") == 'saml:NameID') {
					//3.) Try to find redirect url in saml response's saml:NameID element's SPNameQualifier attribute
					//and remove /saml/metadata from the end of the SPNameQualifier if neccessary
					window.location.href = $(node).attr("SPNameQualifier").toString().replace('/saml/metadata', '');
				}
			});			
		}, 3000);
    });

    return false;
  });
});
