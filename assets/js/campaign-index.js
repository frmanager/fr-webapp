// ...

const $ = require('jquery');
require('popper.js')
require('tether')
require('pace');
require('bootstrap');
require('fastclick');
import 'datatables.net-bs4';

import '../js/libs/jquery.countdown.min.js';

$(document).ready(function($){

//Slow scrolling for anchor nav script
$('a[href*="#"]')
  // Remove links that don't actually link to anything
  .not('[href="#"]')
  .not('[href="#0"]')
  .click(function(event) {
    // On-page links
    if (
      location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '')
      &&
      location.hostname == this.hostname
    ) {
      // Figure out element to scroll to
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      // Does a scroll target exist?
      if (target.length) {
        // Only prevent default if animation is actually gonna happen
        event.preventDefault();
        $('html, body').animate({
          scrollTop: target.offset().top
        }, 800, function() {
          // Callback after animation
          // Must change focus!
          var $target = $(target);
          $target.focus();
          if ($target.is(":focus")) { // Checking if the target was focused
            return false;
          } else {
            $target.attr('tabindex','-1'); // Adding tabindex for elements not focusable
            $target.focus(); // Set focus again
          };
        });
      }
    }
  });

  $('[data-countdown]').each(function() {
      var $this = $(this),
          finalDate = $(this).data('countdown');
      $this.countdown(finalDate, function(event) {
          $this.html(event.strftime('<span class="badge badge-danger">%D</span> day%!D left to donate!</span>'));
      });
  });



});