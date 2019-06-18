/**
 * Created by JetBrains PhpStorm.
 * Author: Eliad Carmi
 */

require(['jquery', 'jqueryui'], function($) {
    $(document).ready(function() {
        // Added by openapp, move the div from the top of the page to the area of the assignment submission.
        var originalityInter = $('#intro-originality.generalbox');
        $('#intro-originality.generalbox').remove();
        $('.editsubmissionform').before(originalityInter);

        $('#region-main input[id="id_submitbutton"]').click(function(e){

            e.preventDefault();

            window.onbeforeunload = null;

            var isChecked = $('#iagree').prop('checked');

            if (!isChecked) {
                var msg = $("#click_checkbox_msg").text();
                var button_text = $("#click_checkbox_button_text").text();

                require(['core/notification'], function(notification) {
                    notification.alert('', msg, button_text);
                });
                return;
            }
            $('#mform1').submit();
            return true;
        });

    });
});