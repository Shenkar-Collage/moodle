/**
 * Created by JetBrains PhpStorm.
 * Author: Eliad Carmi
 */

require(['jquery', 'jqueryui'], function($) {
    $(document).ready(function() {
        var val = $("#assNum").val();
        var zipurl = $(".zipurl").val();
        var lastColumnName = $(".flexible>thead>tr:first-child>th:last-child").attr("class").split(' ')[1];
        var lastColumnNum = parseInt(/\d+(?:\.\d+)?/.exec(lastColumnName));
        lastColumnNum++;
        var originalityHeader;

        if (current_language=='he') originalityHeader = 'בדיקת מקוריות';
        else  originalityHeader = 'Originality Test';

        $(".flexible>thead>tr:first-child").append("<th class='header c" + lastColumnNum + " originality' scope='col'>"+originalityHeader+" </th>")
        var i=0;
      //  $(".flexible>tbody>tr:not(:first)").each(function () {
        $(".flexible>tbody>tr").each(function () {
          var report =   $(this).find(".plagiarismreport");
            report.remove();
            if (report != undefined && $(report).html()!=null) {
            //    if (report.length > 0) {
                    $(report).attr("style", "display: block;text-align:center");
                    $(report).attr('id', "report_"+i);
                    $(this).append("<td class='cell c" + lastColumnNum +" scope='col'>"+$(report).html()+"</td>");
                    $("#report_"+i).show();

           //     }
            }
            i++;

        });

    });
});

