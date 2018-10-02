// ...

const $ = require('jquery');
import 'bootstrap/js/dist/collapse'

import {Spinner} from '../js/libs/spin.js';
import '../css/spin.css';

$(document).ready(function($){

  //called when key is pressed in textbox
  $("#donation_amount, #donation_cc_expirationMonth, #donation_cc_expirationYear, #donation_cc_cvv").keypress(function(e) {
    //if the letter is not digit then display error and don't type anything
    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
      return false;
    }
  });

  $('#cc_form').collapse("hide");

  $('input:radio[name="donation[paymentMethod]"]').change(function() {
    if ($(this).val() == "cc") {
      $('#cc_form').collapse("show");
    } else {
      $('#cc_form').collapse("hide");
    }
  });

  $(function() {
    $('[data-toggle="tooltip"]').tooltip()
  })

  var submitted = false;
  $( "#submitButton" ).click(function() {
    if(!submitted){
      console.log('Submitting form');
      var failed = false;

      if($("#donation_firstName").val() == ""){
        failed = true;
      };

      if($("#donation_lastName").val() == ""){
        failed = true;
      };

      if($("#donation_email").val() == ""){
        failed = true;
      };

      if(!failed){
        submitted = true;
        var opts = {
          lines: 13 // The number of lines to draw
        , length: 28 // The length of each line
        , width: 14 // The line thickness
        , radius: 42 // The radius of the inner circle
        , scale: 1 // Scales overall size of the spinner
        , corners: 1 // Corner roundness (0..1)
        , color: '#000' // #rgb or #rrggbb or array of colors
        , opacity: 0.25 // Opacity of the lines
        , rotate: 0 // The rotation offset
        , direction: 1 // 1: clockwise, -1: counterclockwise
        , speed: 1 // Rounds per second
        , trail: 60 // Afterglow percentage
        , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
        , zIndex: 2e9 // The z-index (defaults to 2000000000)
        , className: 'spinner' // The CSS class to assign to the spinner
        , top: '50%' // Top position relative to parent
        , left: '50%' // Left position relative to parent
        , shadow: false // Whether to render a shadow
        , hwaccel: false // Whether to use hardware acceleration
        , position: 'absolute' // Element positioning
        }

        var target = document.getElementById('overlay_center');
        var spinner = new Spinner(opts).spin(target);
        $("#submitButton").prop("disabled",true);
        document.getElementById("form").submit();

      }else{
        console.log('Form was already submitted');
      }
    }
  });

});