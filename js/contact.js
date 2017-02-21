

window.addEventListener('load', function() {
  var el = document.getElementById('contact-form');
  el.addEventListener('submit', function(e){
    sendContactForm(e);
  });
  document.getElementById("form-url").style.display = "none"; //hides simple spam fooler
});

function sendContactForm(event) {
  event.preventDefault();
  document.getElementById("contact-submit").disabled = true;
  $("#form-messages").text("Sending...");


  var formData = $("#contact-form").serialize();
  var formMessages = $("#form-messages");

  $.ajax({
    type: 'POST',
    cache: false,
    url: $("#contact-form").attr('action'),
    data: formData,
    statusCode: {
      404:function(){
        grecaptcha.reset();
        $(formMessages).text('Oops! An error occured and your message could not be sent.');
      }
    }

  })
    .done(function(response) {
      // Make sure that the formMessages div has the 'success' class.
      $(formMessages).removeClass('error');
      $(formMessages).addClass('success');

      // Set the message text.
      $(formMessages).text(response);

      //exit prevent double click
      setTimeout(function(){
        document.getElementById("contact-submit").disabled = false;
      }, 9000);

      var el1 = document.getElementById("form-name");
      var el2 = document.getElementById("form-email");
      var el3 = document.getElementById("form-message");

      el1.disabled = true;
      el2.disabled = true;
      el3.disabled = true;

      // Clear the form.
      setTimeout(function(){
        el1.disabled = false;
        el2.disabled = false;
        el3.disabled = false;
        el1.value = "";
        el2.value = "";
        el3.value = "";
        grecaptcha.reset();
      }, 9000);
    })
    .fail(function(data) {
      // Make sure that the formMessages div has the 'error' class.
      $(formMessages).removeClass('success');
      $(formMessages).addClass('error');

      //exit prevent double click
      setTimeout(function(){
        grecaptcha.reset();
        document.getElementById("contact-submit").disabled = false;
      }, 2000);

      // Set the message text.
      if (data.responseText !== '') {
          $(formMessages).text(data.responseText);
        } else {
          if (document.getElementsByTagName('html')[0].getAttribute('lang') === "sv") {
            $(formMessages).text('Oops! Det är något fel på servern och dit meddelande kunde inte skickas.');
          } else {
            $(formMessages).text('Oops! An error occured and your message could not be sent.');
          };
        };
        document.getElementById("contact-submit").disabled = false;
      });
};
