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

        $('body').css('cursor', 'progress'); 
		html.appendTo('body').ready(function() {
			console.log('Submit start');
			
			$('#submitDiv form').submit();
			
			console.log('Submit end');
		});
		
		//Chrome login hacks, login doesn't occur..
		/*
		setTimeout(function() {
			console.log('1st submit retry start');
			
			$('#submitDiv form').submit(); //1.) Try to resubmit form
			
			console.log('1st submit retry end');
			
			if ($('input[name="RelayState"]').length) {
				console.log('2nd redirect retry start');
				
				var url = $('input[name="RelayState"]').val();
				window.location.replace(url); //2.) Try to find redirect url in RelayState input's value
				console.log(url);
				
				console.log('2nd redirect retry end');
			}
			
			var samlResponse = atob($('input[name="SAMLResponse"]').val());
			var xmlDoc = $.parseXML(samlResponse);
			
			console.log('SAMLResponse:');
			console.log(xmlDoc);
			
			$.each($(xmlDoc)[0].all, function(key, node){
				if ($(node).prop("tagName") == 'saml:NameID') {
					//3.) Try to find redirect url in saml response's saml:NameID element's SPNameQualifier attribute
					//and remove /saml/metadata from the end of the SPNameQualifier if neccessary
					console.log('3rd redirect retry start');
					
					var url = $(node).attr("SPNameQualifier").toString().replace('/saml/metadata', '');
					window.location.replace(url);
					console.log(url);
					
					console.log('3rd redirect retry end');
				}
			});			
		}, 3000);
		*/
		setTimeout(function() {
			/*
			console.log('4nd redirect retry start');
			
			window.location.replace(document.referrer); //4.) Redirect to referer
			console.log(document.referrer);
			
			console.log('4nd redirect retry end');
			*/
		
			$('form #regularsubmit button').click(); //5.) trigger click on submit button again
		//}, 3500);
		}, 1000);		
		
		setTimeout(function() {
			window.location.reload(false); //6.) Reload page
		}, 3500);
    });

    return false;
  });
});
