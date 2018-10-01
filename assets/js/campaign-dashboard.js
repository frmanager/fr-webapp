// ...

import $ from 'jquery';
// JS is equivalent to the normal "bootstrap" package
// no need to set this to a variable, just require it

import 'datatables.net-bs4';


//LEGACY -->
import '../js/libs/jquery.countdown.min.js';

$(document).ready(function($){

  $('[data-countdown]').each(function() {
      var $this = $(this),
          finalDate = $(this).data('countdown');
      $this.countdown(finalDate, function(event) {
          $this.html(event.strftime('<span class="badge badge-danger">%D</span> day%!D left to donate!</span>'));
      });
  });

});