if (typeof franchise_id != 'undefined' &&
  typeof completedWeek != 'undefined' &&
  typeof liveScoringWeek != 'undefined' &&
  typeof thisProgram != 'undefined') {

  (function ($, window, document, franchise_id, completedWeek, liveScoringWeek, thisProgram) {
    $(document).ready(function () {
      if (thisProgram == 'options_179') {
        $form = $('form input[name=TYPE]').parent();
        if (typeof $form == 'undefined') return;
        $table = $form.find('table');
        if (typeof $table == 'undefined') return;
        $rows = $table.find('tr');
        $rows.shift()

      }

    });
  })(jQuery, window, document, franchise_id, league_id, liveScoringWeek, thisProgram);
}