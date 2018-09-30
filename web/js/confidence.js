/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
!function(a){function f(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var e,b=a.ui.mouse.prototype,c=b._mouseInit,d=b._mouseDestroy;b._touchStart=function(a){var b=this;!e&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(e=!0,b._touchMoved=!1,f(a,"mouseover"),f(a,"mousemove"),f(a,"mousedown"))},b._touchMove=function(a){e&&(this._touchMoved=!0,f(a,"mousemove"))},b._touchEnd=function(a){e&&(f(a,"mouseup"),f(a,"mouseout"),this._touchMoved||f(a,"click"),e=!1)},b._mouseInit=function(){var b=this;b.element.bind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),c.call(b)},b._mouseDestroy=function(){var b=this;b.element.unbind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),d.call(b)}}}(jQuery);

if (typeof franchise_id != 'undefined' &&
  typeof completedWeek != 'undefined' &&
  typeof liveScoringWeek != 'undefined' &&
  typeof thisProgram != 'undefined') {

  (function ($, window, document, franchise_id, completedWeek, liveScoringWeek, thisProgram) {
    $(document).ready(function () {

      function getCount(){
        var $form = $('form input[name=TYPE]').parent();
        var $table = $form.find('table');
        var $selects = $table.find('select');
        var count = 0;
        $selects.each(function(){
          if ($(this).prop('disabled')) {
            if ($(this).val() != '-') {
              count++;
            } else {
           //   $('body').removeClass('use-new');
            }
          }
          else {
            count++;
          }
        });
        return count;
      }

      if (thisProgram == 'options_121') {
        $('body').addClass('use-new');
        getCount();
        if (!$('body').hasClass('use-new')) {
          return;
        }
       // $('select[name="RANKMIN,LAR"]').prop('disabled', true);
       // $('input[name="PICKMIN,LAR"]').prop('disabled', true);
        var $form = $('form input[name=TYPE]').parent();
        if (typeof $form == 'undefined') return;
        var $table = $form.find('table');
        $table.addClass('confidence-table');
        if (typeof $table == 'undefined') return;
        var $rows = $table.find('tr');
        var $hometeams = $('<div class="hometeams"></div>');
        var $awayteams = $('<div class="awayteams"></div>');
        var $picks = $('<div class="picks"></div>');
        var $matches = [];
        var count = 0;
        $rows.each(function () {
          if (!$(this).hasClass('oddtablerow') && !$(this).hasClass('eventablerow')) return;
          count++;
          var $hometd = $(this).children('td:first-child');
          var $homeradio = $hometd.children('input');
          var $conf = $(this).find('select');
          var $awaytd = $(this).children('td:nth-child(2)');
          var $awayradio = $awaytd.children('input');

          var $hometeam = $("<div class='hometeam home pick'></div>");
          $hometeam.html("<div class='confidence'></div><div class='match'><span class='teamname'>" + $hometd.children('label').text()+ "</span> <span class='oteamname'>vs " + $awaytd.children('label').text() + "</span></div><div class='kickoff'></div>");
          $hometeam.data('name', $homeradio.attr('name'));
          $hometeam.data('value', $homeradio.val());
          $hometeam.data('opponent', $awayradio.val());
          $hometeam.data('type', 'home');
          $hometeam.data('otype', 'away');
          $hometeam.data('selectname', $conf.attr('name'));
          $hometeam.addClass($homeradio.val());

          var $awayteam = $("<div class='awayteam away pick'></div>");
          $awayteam.html("<div class='confidence'></div><div class='match'><span class='teamname'>" + $awaytd.children('label').text()+ "</span> <span class='oteamname'>@ " + $hometd.children('label').text() + "</span></div><div class='kickoff'></div>");
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
          if ($homeradio.prop('disabled')) {
            $hometeam.addClass('locked static');
            $awayteam.addClass('locked static');
          }

          if ($awayradio.is(':checked')) {
            $awayteam.data('selected', true);
            $awayteam.addClass('selected game-picked');
            $hometeam.addClass('oselected game-picked');
            $matches.push($awayteam.clone(true));
          }
          if ($homeradio.is(':checked')) {
            $hometeam.data('selected', true);
            $hometeam.addClass('selected game-picked');
            $awayteam.addClass('oselected game-picked');
            $matches.push($hometeam.clone(true));
          }

          $hometeams.append($hometeam);
          $awayteams.append($awayteam);

        });

        $matches.sort(function (a, b) {
          return b.data('conf') - a.data('conf')
        });
        for (var i = 0; i < $matches.length; i++ ) {
          var rank = count - i;

          $matches[i].children('.confidence').html(rank.toString());

          $picks.append($matches[i].removeClass('selected game-picked'));
        }
        var $board = $("<div class='board'></div>");
        $picks.height(count * 45);
        $board.append($hometeams);
        $board.append($awayteams);
        $board.append($picks);
        $table.after($board);
       // if ($picks.children().length != $hometeams.children().length) {
      //    $form.find('input[type=submit]').prop('disabled', true);
      //  }
        function logevent(event) {
          return;
          console.log (event.type + ' event');
        //  console.log($('.picks > div').length + ' divs in picklist');
        //  console.log($('.picks > .pick').length + ' picks in picklist');
         // console.log($('.picks .locked').length + ' locked');
          console.log($('.picks .incoming').length + ' incoming detected');
          console.log("Locked index: " + $('.picks .pick.PHI').index());
          console.log("Locked index (saved): " + $('.picks .locked').data('position'));
        }


        $picks.sortable({
          activate: function(event, ui) {
            logevent(event);
          },
          deactivate: function(event, ui) {
            logevent(event);
          },
          receive: function (event, ui) {
            logevent(event);
            var $item = ui.item;
            var value = $item.data('value');
            var name = $item.data('name');
            var opponent = $item.data('opponent');
            var otype = $item.data('otype');
            var type = $item.data('type');
            $('.picks .' + opponent).remove();
            ui.helper.removeClass('incoming')

            console.log('receive stop');
            var index = $('.ui-sortable-helper', this).index();
            var $lockhelper = $('<div></div>').prependTo(this);
            var $locks = $('.locked', this).detach();
            var $sortable = $(this);
            $locks.each(function(){
              var pos = $(this).data('position');
              $(this).insertAfter($('> div', $sortable).eq(pos));
            });
            $lockhelper.remove();

            $('.' + type + 'teams .' + value).removeClass('oselected').addClass('selected game-picked');
            $('.' + otype + 'teams .' + opponent).removeClass('selected').addClass('oselected game-picked');
            $('input[value=' + value + ']').prop('checked', true);
         //   if ($(".picks .pick").length = getCount()) {
         //     $('form input[name=TYPE]').parent().find('input[type=submit]').prop('disabled', false);
         //   }


          },
          update: function (event, ui) {
            logevent(event);
            var $form = $('form input[name=TYPE]').parent();
            var $table = $form.find('table');
            $helper = ui.helper;
            var $selects = $table.find('select').val('-');

            var conf = getCount();
            var $picklist = $(this).children('.pick');
            $picklist.each(function () {
              $(this).data('conf', conf)
              $("select[name='" + $(this).data('selectname') + "']").val(conf.toString());
              $(this).children('.confidence').html(conf.toString());
              conf--;
            });


          },
          change: function(event, ui) {
            logevent(event);
        //    console.log('change stop');
            var index = $('.ui-sortable-helper', this).index();
            var $lockhelper = $('<div></div>').prependTo(this);
            var $locks = $('.locked', this).detach();
            var $sortable = $(this);
            $locks.each(function(){
              var pos = $(this).data('position');
              $(this).insertAfter($('> div', $sortable).eq(pos));
            });
            $lockhelper.remove();


            var $form = $('form input[name=TYPE]').parent();
            var $table = $('table', $form);
            $helper = ui.helper;
            var conf = getCount();
            var $picklist = $('.pick:not(.ui-sortable-helper)', this);
            $picklist.each(function () {
              $(this).data('conf', conf)
            //  $("select[name='" + $(this).data('selectname') + "']").val(conf.toString());
              $(this).children('.confidence').html(conf.toString());
              conf--;
            });
            $(this).find('.pick.ui-sortable-helper .confidence').html($(this).children('.ui-sortable-placeholder').data('conf').toString());

            /*$sortable = $(this);
            $statics = $('.static', this).detach();
            $helper = $('<div></div>').prependTo(this);
            $statics.each(function(){
              var $this = $(this);
              var target = $this.data('pos');

              $this.insertAfter($('div', $sortable).eq(target));
            });
            $helper.remove(); */
          },
          items: '> :not(.static)',
          start: function(event, ui){
            logevent(event);
        //    console.log('starting');
            $('.locked', this).each(function(){
              $(this).data('position', $(this).index());
              console.log('starting: index ' + $(this).index());
            });
          },
          over: logevent,
          out: function(event, ui) {
            logevent(event);
          //  console.log('out stop');
            if (ui.helper == null) { return;}
            var index = $('.ui-sortable-helper', this).index();
            var $lockhelper = $('<div></div>').prependTo(this);
            var $locks = $('.locked', this).detach();
            var $sortable = $(this);
            $locks.each(function(){
              var pos = $(this).data('position');
              $(this).insertAfter($('> div:not(.incoming.ui-sortable-placeholder)', $sortable).eq(pos));
            });
            $lockhelper.remove();

            var $form = $('form input[name=TYPE]').parent();
            var $table = $('table', $form);
            $helper = ui.helper;
            var conf = getCount();
            var $picklist = $('.pick:not(.ui-sortable-helper,.incoming.ui-sortable-placeholder)', this);
            $picklist.each(function () {
              $(this).data('conf', conf)
              //  $("select[name='" + $(this).data('selectname') + "']").val(conf.toString());
              $(this).children('.confidence').html(conf.toString());
              conf--;
            });

            $('.incoming .confidence', $(this)).html('');
          },
          stop: function(event) {
            logevent(event)
          //  console.log('stop stop');
          }
        });

        $board.find('.hometeams .pick, .awayteams .pick').draggable({
          helper: function (a, b) {
            var $helper = $(this).clone();
            $helper.data($(this).data());
            $helper.addClass('incoming');
            return $helper;
          },
          cancel: '.selected, .locked',
          revert: 'invalid',
          connectToSortable: '.picks',
          start: function (event, ui) {
            ui.helper.width($('.hometeam').width());
            if (ui.helper.hasClass('oselected')) {
              $('.picks .'+ui.helper.data('opponent')).addClass('removing');
            }
            ui.helper.removeClass('selected oselected game-picked').addClass('unlocked');
          },
          stop: function(event, ui){
            $('.picks .'+ui.helper.data('opponent')).removeClass('removing');
          }

        });

        var nflSchedule;
        var week = $form.children('input[name=WEEK]').val();
        $.getJSON("http://www71.myfantasyleague.com/2018/export", {TYPE: 'nflSchedule', W: week, JSON:1} , function(data){
          for(var i = 0; i< data.nflSchedule.matchup.length; i++) {
            var match = data.nflSchedule.matchup[i];
            var kickoff = new Date(match.kickoff * 1000)
            for (var j = 0; j < match.team.length; j++) {
              var team = match.team[j];
              var teamid = team.id
              var spread = team.spread
              $(".pick." + team.id+ " .kickoff").html("Kickoff: " + kickoff.toDateString() + " " +kickoff.toLocaleTimeString())
            }

          }
        })
      }
    });
  })(jQuery, window, document, franchise_id, league_id, liveScoringWeek, thisProgram);
}