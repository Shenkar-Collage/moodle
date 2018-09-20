/**
 * Created by JetBrains PhpStorm.
 * Author: Eliad Carmi
 */

require(['jquery', 'jqueryui'], function($) {
	$(document).ready(function() {
        var exts = $('#file_exts').html();
        var exts_array = exts.split(',');

        var allowed_file_types = $.map(exts_array, function(value) { return value.trim();});

	    //Added by openapp, move the div from the top of the page to the area of the assignment submission.
	    var originalityInter = $('#intro-originality.generalbox');
	    $('#intro-originality.generalbox').remove();
	    $('.editsubmissionform').before(originalityInter);

        if (!$("#iagree").prop('checked'))  {
            $('input[id="id_submitbutton"]').attr('disabled','disabled');
        }

	    $("#iagree").change(function() {
	        if ($(this).is(":checked")) {
        	    $('input[id="id_submitbutton"]').removeAttr('disabled')
	        } else {
        	    $('input[id="id_submitbutton"]').attr('disabled','disabled');
	        }
	    });
        $('#region-main input[id="id_submitbutton"]').click(function(){

            var filename = $('.fp-thumbnail>img').attr('alt');
            var file_ext = filename.split('.').pop().toLowerCase();
            //var allowed_file_types = ["txt", "rtf", "doc", "docx", "pdf"];

            var allowed = $.inArray(file_ext, allowed_file_types) > -1;

            if (!allowed){
                $('#fileext_err').show();
                return false;
            }else {
                //$(this).attr('disabled','disabled');
                //$('#mform1').submit();
                $(this).hide();
                return true;
            }
        });

	});
});
