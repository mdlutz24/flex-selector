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
          var $conf = $(this).find('select');
          var $awaytd = $(this).children('td:nth-child(2)');
          var $awayradio = $awaytd.children('input');

          var $hometeam = $("<div class='hometeam home pick'></div>");
          $hometeam.html($hometd.children('label').text());
          $hometeam.data('name', $homeradio.attr('name'));
          $hometeam.data('value', $homeradio.val());
          $hometeam.data('opponent', $awayradio.val());
          $hometeam.data('type', 'home');
          $hometeam.data('otype', 'away');
          $hometeam.data('selectname', $conf.attr('name'));
          $hometeam.addClass($homeradio.val());

          var $awayteam = $("<div class='awayteam away pick'></div>");
          $awayteam.html($awaytd.children('label').text());
          $awayteam.data('name', $awayradio.attr('name'));
          $awayteam.data('value', $awayradio.val());
          $awayteam.data('opponent', $homeradio.val());
          $awayteam.data('type', 'away');
          $awayteam.data('otype', 'home');
          $awayteam.data('selectname', $conf.attr('name'));
          $awayteam.addClass($awayradio.val());

          if ($conf.val() != '-') {
            $awayteam.data('conf', $conf.val());
            $hometeam.data('conf', $conf.val());
          }

          if ($awayradio.is(':checked')) {
            $awayteam.data('selected', true);
            $awayteam.addClass('selected');
            $matches.push($awayteam.clone(true));
          }
          if ($homeradio.is(':checked')) {
            $hometeam.data('selected', true);
            $hometeam.addClass('selected');
            $matches.push($hometeam.clone(true));
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
      $picks.sortable({
        receive: function(event, ui) {
          var $item = ui.item;
          var value = $item.data('value');
          var name = $item.data('name');
          var opponent = $item.data('opponent');
          var otype = $item.data('otype');
          var type = $item.data('type');
          $('.picks .' + opponent).remove();
          $('.' + type + 'teams .' + value).addClass('selected');
          $('.' + otype + 'teams .' + opponent).removeClass('selected');
          $('input[value=' + value +']').prop('checked', true);


        },
        update: function (event,ui) {
          var $form = $('form input[name=TYPE]').parent();
          var $table = $form.find('table');
          $helper = ui.helper;
          var conf = $table.find('select').val('-').length;
          var $picklist = $(this).children('.pick');
          $picklist.each(function(){
            $(this).data('conf', conf)
            $("select[name='" + $(this).data('selectname') + "']").val(conf.toString());
            conf--;
          });



        }
      });

      $board.find('.hometeam.pick, .awayteam.pick').draggable({
        helper: function(a,b){
          var $helper = $(this).clone();
          $helper.data('name', $(this).data('name'));
          $helper.data('otype', $(this).data('otype'));
          $helper.data('type', $(this).data('type'));
          $helper.data('value', $(this).data('value'));
          $helper.data('opponent', $(this).data('opponent'));
          $helper.data('selectname', $(this).data('selectname'));
          return $helper;
          },
        cancel: '.selected',
        revert: 'invalid',
        connectToSortable: '.picks',
        start: function(event, ui) {
          ui.helper.width($('.hometeam').width());
        }

      });

    });
  })(jQuery, window, document, franchise_id, league_id, liveScoringWeek, thisProgram);
}