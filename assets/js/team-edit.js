// ...

const $ = require('jquery');

//import '../css/croppie.css';
//import '../js/libs/croppie.min.js';


$(document).ready(function() {
    $('.delete_record').click(function() {
      event.preventDefault();
      var r = confirm("Are you sure you want to delete?");
      if (r == true) {
        window.location = $(this).attr('href');
      }
    });

    $('#cancel-image-button').click(function() {
      $('.card-img-overlay').remove();
      $('#profile-image').attr('src', '{{ profileImageUrl }}');
    });

    $("#team_image").change(function(){
        readURL(this);
        $('.card-img-overlay').remove();
        $('<div class="card-img-overlay">' +
             '<h4 class="card-title text-center">This is a Preview</h4>' +
             '<p class="card-text text-center">Click "Update" to Save.</p>' +
          '</div>').insertAfter( "#profile-image" );
    });

});


function readURL(input) {
  if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
          $('#profile-image').attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
  }
}
