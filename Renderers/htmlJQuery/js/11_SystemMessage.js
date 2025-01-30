
$(document).ready(function() {
  var $overlay = $(".page-overlay");
  var $message = $(".message");
  var $messageText = $(".messageText");
  var $showMessageButton = $(".show-message-button");
  var position = $showMessageButton.position();
  function cleanMessage() { $messageText.text(''); }

  function showMessage(message='') {
    if(message.length > 0 ){ $messageText.text(message); }
    if($messageText.text().trim().length > 0 ){
      $overlay.fadeIn();
      //$message.fadeIn();
      $message.animate({
        fontSize: "2rem",
        width: "90vw",
        padding: "0.25rem",
        left: "50vw",
        top: "30vh"
      }, 700 );
    }
  }
  
  function hideMessage() {
    $overlay.fadeOut();
    $message.animate({
      fontSize: "0rem",
      width: "0vw",
      padding: "0rem",
      left: position.left,
      top: position.top
    }, 700 );

    // Code to modify the URL
    var currentUrl = window.location.href;
    var newUrl = currentUrl.split('!!')[0]; // Split the URL at '!!' and take the first part
    history.pushState({}, '', newUrl); // Update the URL without reloading the page
  }

  $(document).click(function(event) {
    if ($overlay.is(event.target)) {
      hideMessage();
    }
  });

  $message.find(".close").click(function() {
    hideMessage();
  });

  $showMessageButton.click(function() {
    showMessage();
  });
  
  // show message
  $message.css('top', position.top);
  $message.css('left',  position.left);
  showMessage('');

  setTimeout(function(){
    hideMessage();
  }, 2000);


});


