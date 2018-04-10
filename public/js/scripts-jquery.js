$(document).ready(function(){

  //Changes the page after user logs out
  $('#logoutButtonContentSame').click(function(){
    //Adds and removes existing elements
    $('#userWelcome').hide();
    jQuery('#LoginAtTop').hide();
    var $this = $(this);
    //Changes button text
    //http://jsfiddle.net/V4u5X/2/
    if($this.hasClass('logoutButton')){
      window.location.href = "#";
    } else {
      $this.text('Login');
    }
    $this.toggleClass('logoutButton');
  });

  $('#public').click(function() {
    $('#privacy2').html("PUBLIC");
    $('#privacy3').val("PUBLIC");
  });

  $('#private').click(function() {
    $('#privacy2').html("PRIVATE");
    $('#privacy3').val("PRIVATE");
  });

});
