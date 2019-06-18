/**
 * Created by JetBrains PhpStorm.
 * Author: Eliad Carmi
 */


// Moodle 3.1.7 Changing the add assignment page to only allow one type of submission, either file or online text and changing the default max upload size to 100kb.

require(['jquery', 'jqueryui'], function($) {
      $(document).ready(function() {

          var orig_use_originality_setting = $('#id_originality_use').val();

          $('#id_originality_use').change(function(){
              if (orig_use_originality_setting == 0  && $(this).val() == 1 && $('#assignment_has_submissions_notifications').length)  { // Length checks if element exists on page
                  var msg = $("#assignment_has_submissions_notifications").html();

                  require(['core/notification'], function(notification) {
                      notification.alert('Notification', msg, 'OK');
                  });
              }
          });

      });

});