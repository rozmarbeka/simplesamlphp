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

//Perform ajax login and show progress indicator as it's the future
$(document).ready(function(){
  $('form.login-form').submit(function() {
      if (!$(this.username).val() && !$(this.password).val()) {
          $('#username').focus();

          return false;
      }

      var postData = $(this).serialize();
    $.post(window.location, postData, function(response) {
        if (response.indexOf('login-error') >= 0) {
            var wrapHtml = $('<div/>').html(response).find('#wrap').html();
            $('#wrap').html(wrapHtml);
            $('#username').focus();

            return;
        }

        $('.progress-indicator').show();
        $('body').append('<div id="submitDiv" style="visibility: hidden">' + response + '</div>');
        $('#submitDiv form').submit();
        
        //Chrome hack, sometimes submit doesn't happen immediately
        setTimeout(function () {
            $('#submitDiv form').submit();
        }, 10);
    });

    return false;
  });
});
