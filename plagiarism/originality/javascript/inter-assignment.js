/**
 * Created by JetBrains PhpStorm.
 * Author: Eliad Carmi
 */

/*
Moodle 3.1.7 Changing the add assignment page to only allow one type of submission, either file or online text and changing the default max upload size to 100kb.

*/

require(['jquery', 'jqueryui'], function($) {
	$(document).ready(function() {

        $('#id_originality_use').change(function(){
            if ($(this).val() == 1 )  {
                //if the originality select box is 'yes' (use originality), then set the max submissions size to 100kb
                //actually comment this out in version 4.0.4, per Eli's instruction.
                //$('#id_assignsubmission_file_maxsizebytes').val(102400);
            }
        });

        $( "#mform1" ).submit(function() {
            if ($('#id_originality_use').val()==1){
                if (
                    ($("#id_assignsubmission_file_enabled").prop('checked') && $("#id_assignsubmission_onlinetext_enabled").prop('checked')) ||
                    ($('#id_assignsubmission_file_maxfiles').val() > 1 && $("#id_assignsubmission_file_enabled").prop('checked'))
                    ){
                    var msg = $("input[name='originality_one_type_submission_error']").val();

                    require(['core/notification'], function(notification) {
                        notification.alert('Notification', msg, 'OK');
                    });
                    return false;
                }else{
                    return true;
                }
            }
        });
});
});


