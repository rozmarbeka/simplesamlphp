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

$(document).ready(function(){
  $('form.login-form').submit(function() {
    $('.progress-indicator').show();
    var postData = $(this).serialize();
    $.post(window.location, postData, function(response) {
      $('body').append('<div id="submitDiv" style="visibility: hidden">' + response + '</div>');
      $('#submitDiv form').submit();
    });

    return false;
  });
});
