/* Dashboard — main JS */

$(function () {
  // Sidebar toggle
  $('#sidebar-toggle').on('click', function () {
    var $s = $('#sidebar');
    if ($(window).width() <= 768) $s.toggleClass('mobile-open');
    else $s.toggleClass('collapsed');
  });

  // Init DataTables with sort arrows
  if ($.fn.DataTable) {
    $('.data-table').each(function () {
      var noSort = $(this).data('no-sort') ? false : true;
      $(this).DataTable({
        language: {
          search: 'חיפוש:', lengthMenu: 'הצג _MENU_ רשומות',
          info: 'מציג _START_ עד _END_ מתוך _TOTAL_',
          infoEmpty: 'אין רשומות', zeroRecords: 'לא נמצאו תוצאות',
          paginate: { first: 'ראשון', last: 'אחרון', next: 'הבא', previous: 'הקודם' }
        },
        order: [],
        pageLength: 25,
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
        columnDefs: [{ orderable: false, targets: 'no-sort' }],
      });
    });
  }

  // Components popup
  $(document).on('click', '.comp-badge', function () {
    var courseId   = $(this).data('course-id');
    var courseName = $(this).data('course-name');
    $('#componentsModalLabel').text('רכיבים: ' + courseName);
    $('#components-list').html('<li class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> טוען...</li>');
    new bootstrap.Modal(document.getElementById('componentsModal')).show();
    $.getJSON(BASE_URL + '/api/components.php', { course_id: courseId }, function (data) {
      if (!data.length) { $('#components-list').html('<li class="text-muted text-center py-3">אין רכיבים</li>'); return; }
      var icons  = { assign:'fa-file-pen', quiz:'fa-circle-question', forum:'fa-comments', resource:'fa-file', url:'fa-link', page:'fa-file-lines', folder:'fa-folder', label:'fa-tag', h5pactivity:'fa-h', attendance:'fa-calendar-check', workshop:'fa-people-group', scorm:'fa-play-circle' };
      var names  = { assign:'מטלה', quiz:'חידון', forum:'פורום', resource:'קובץ', url:'קישור', page:'דף', folder:'תיקיה', label:'תווית', h5pactivity:'H5P', attendance:'נוכחות', workshop:'סדנה', scorm:'SCORM' };
      var html = '';
      $.each(data, function (_, item) {
        html += '<li><span><i class="fa-solid ' + (icons[item.component]||'fa-puzzle-piece') + ' component-icon text-primary"></i> ' + (names[item.component]||item.component) + '</span><span class="badge bg-secondary">' + item.cnt + '</span></li>';
      });
      $('#components-list').html(html);
    });
  });

  // Auto-hide alerts
  setTimeout(function () { $('.alert-auto-hide').fadeOut(400); }, 4000);
});

var BASE_URL = BASE_URL || '';

// Excel export — pass table ID and desired filename (without extension)
function exportExcel(tableId, filename) {
  var wb = XLSX.utils.book_new();
  var tbl = document.getElementById(tableId);
  if (!tbl) { alert('טבלה לא נמצאה'); return; }

  // Collect headers (skip "no-export" action columns based on class)
  var headers = [], skipCols = [];
  $(tbl).find('thead th').each(function (i) {
    if ($(this).hasClass('no-export')) { skipCols.push(i); return; }
    headers.push($(this).text().trim().replace(/\n/g,' ').replace(/\s+/g,' '));
  });

  var rows = [headers];
  $(tbl).find('tbody tr').each(function () {
    var row = [];
    $(this).find('td').each(function (i) {
      if (skipCols.indexOf(i) !== -1) return;
      row.push($(this).text().trim().replace(/\n/g,' ').replace(/\s+/g,' '));
    });
    if (row.some(function(c){ return c !== ''; })) rows.push(row);
  });

  var ws = XLSX.utils.aoa_to_sheet(rows);
  // Right-to-left sheet
  if (!ws['!cols']) ws['!cols'] = [];
  XLSX.utils.book_append_sheet(wb, ws, 'נתונים');
  XLSX.writeFile(wb, filename + '.xlsx');
}
