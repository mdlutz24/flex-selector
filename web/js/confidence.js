if (typeof franchise_id != 'undefined' &&
  typeof completedWeek != 'undefined' &&
  typeof liveScoringWeek != 'undefined' &&
  typeof thisProgram != 'undefined') {

  (function ($, window, document, franchise_id, completedWeek, liveScoringWeek, thisProgram) {
    $(document).ready(function () {
      if (thisProgram == 'options_179') {
        var $form = $('form input[name=TYPE]').parent();
        if (typeof $form == 'undefined') return;
        var $table = $form.find('table');
        if (typeof $table == 'undefined') return;
        var $rows = $table.find('tr');
        var $hometeams = $('<div class="hometeams"></div>');
        var $awayteams = $('<div class="awayteams"></div>');
        var $picks = $('<div class="picks"></div>');
        var $matches = [];

        $rows.each(function(){
          if (!$(this).hasClass('oddtablerow') && !$(this).hasClass('eventablerow')) return;
          var $hometd = $(this).children('td:first-child');
          var $homeradio = $hometd.children('input');
          var $conf = $(this).children('td.inputfield select');
          var $awaytd = $(this).children('td:nth-child(2)');
          var $awayradio = $awaytd.children('input');

          var $hometeam = $("<div class='hometeam pick'></div>");
          $hometeam.html($hometd.children('label').text());
          $hometeam.data('name', $homeradio.attr('name'));
          $hometeam.data('value', $homeradio.attr('value'));

          var $awayteam = $("<div class='awayteam pick'></div>");
          $awayteam.html($awaytd.children('label').text());
          $awayteam.data('name', $awayradio.attr('name'));
          $awayteam.data('value', $awayradio.attr('value'));

          if ($conf.val() != '-') {
            $awayteam.data('conf', $conf.val());
            $hometeam.data('conf', $conf.val());
          }

          if ($awayradio.is(':checked')) {
            $awayteam.data('selected', true);
            $awayteam.addClass('selected');
            $matches.push($awayteam.clone());
          }
          if ($homeradio.is(':checked')) {
            $hometeam.data('selected', true);
            $hometeam.addClass('selected');
            $matches.push($hometeam.clone());
          }
          $hometeams.append($hometeam);
          $awayteams.append($awayteam);

        })
        $matches.sort(function(a,b){return a.data('conf')-b.data('conf')})
        for ($match in $matches) {
          $picks.append($match);
        }
        var $board = $("<div class='board'></div>");
        $board.append($hometeams);
        $board.append($awayteams);
        $board.append($picks);
        $form.after($board);

      }

    });
  })(jQuery, window, document, franchise_id, league_id, liveScoringWeek, thisProgram);
}