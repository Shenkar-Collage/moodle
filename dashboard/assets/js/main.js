/* Dashboard — main JavaScript */

$(function () {

  // ── Sidebar toggle ──────────────────────────────────────
  $('#sidebar-toggle').on('click', function () {
    var $s = $('#sidebar');
    if ($(window).width() <= 768) {
      $s.toggleClass('mobile-open');
    } else {
      $s.toggleClass('collapsed');
    }
  });

  // ── Initialize all DataTables ───────────────────────────
  if ($.fn.DataTable) {
    $('.data-table').DataTable({
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/he.json',
        search: 'חיפוש:',
        lengthMenu: 'הצג _MENU_ רשומות',
        info: 'מציג _START_ עד _END_ מתוך _TOTAL_',
        paginate: { first: 'ראשון', last: 'אחרון', next: 'הבא', previous: 'הקודם' }
      },
      order: [],
      pageLength: 25,
      responsive: true,
      dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
    });
  }

  // ── Components popup ────────────────────────────────────
  $(document).on('click', '.comp-badge', function () {
    var courseId   = $(this).data('course-id');
    var courseName = $(this).data('course-name');
    $('#componentsModalLabel').text('רכיבים: ' + courseName);
    $('#components-list').html('<li class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> טוען...</li>');
    $('#componentsModal').modal && $('#componentsModal').modal('show') || new bootstrap.Modal(document.getElementById('componentsModal')).show();

    $.getJSON(BASE_URL + '/api/components.php', { course_id: courseId }, function (data) {
      if (!data.length) {
        $('#components-list').html('<li class="text-muted text-center py-3">אין רכיבים</li>');
        return;
      }
      var icons = {
        'assign':   'fa-file-pen',
        'quiz':     'fa-circle-question',
        'forum':    'fa-comments',
        'resource': 'fa-file',
        'url':      'fa-link',
        'page':     'fa-file-lines',
        'folder':   'fa-folder',
        'label':    'fa-tag',
        'chat':     'fa-message',
        'wiki':     'fa-book',
        'glossary': 'fa-book-open',
        'survey':   'fa-poll',
        'workshop': 'fa-people-group',
        'scorm':    'fa-play-circle',
        'lti':      'fa-plug',
        'h5pactivity': 'fa-h',
        'attendance': 'fa-calendar-check',
      };
      var names = {
        'assign':   'מטלה',        'quiz':     'חידון',        'forum':    'פורום',
        'resource': 'קובץ',        'url':      'קישור',        'page':     'דף',
        'folder':   'תיקיה',       'label':    'תווית',        'chat':     'צ\'אט',
        'wiki':     'ויקי',         'glossary': 'מילון מונחים', 'survey':   'סקר',
        'workshop': 'סדנה',         'scorm':    'SCORM',        'lti':      'כלי חיצוני',
        'h5pactivity': 'H5P',       'attendance': 'נוכחות',
      };
      var html = '';
      $.each(data, function (_, item) {
        var icon = icons[item.component] || 'fa-puzzle-piece';
        var name = names[item.component] || item.component;
        html += '<li>' +
          '<span><i class="fa-solid ' + icon + ' component-icon text-primary"></i> ' + name + '</span>' +
          '<span class="badge bg-secondary">' + item.cnt + '</span>' +
        '</li>';
      });
      $('#components-list').html(html);
    });
  });

  // ── Auto-hide alerts ────────────────────────────────────
  setTimeout(function () { $('.alert-auto-hide').fadeOut(400); }, 4000);
});

// Expose BASE_URL for AJAX (set inline by PHP pages)
var BASE_URL = BASE_URL || '';
