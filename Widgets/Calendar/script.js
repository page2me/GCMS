function widgetsCalendarSend(id, d) {
  var q = 'class=Widgets\\Calendar\\Controllers\\Get&method=render';
  if (d !== null) {
    q += '&id=' + d;
  }
  send(WEB_URL + 'xhr.php', q, function (xhr) {
    $E(id).innerHTML = xhr.responseText;
    widgetsCalendarInit(id, false);
  });
}
var widgetsCalendarInit = function (id, load) {
  var hs, patt = /^(prev|next|today|calendar)\-(([0-9]+){0,2}\-([0-9]+){0,2}\-([0-9]+){0,4})(\-([,0-9a-z_]+))?$/;
  forEach($E(id).getElementsByTagName('a'), function () {
    hs = patt.exec(this.id);
    if (hs) {
      if (hs[1] == 'calendar') {
        this.onclick = function () {
          loaddoc(this.href);
          return false;
        };
        this.onmousemove = function () {
          mTooltipShow(this.id, WEB_URL + 'xhr.php', 'class=Widgets\\Calendar\\Controllers\\Tooltip&method=get', this);
        };
      } else {
        this.onclick = function () {
          widgetsCalendarSend(id, this.id);
        };
      }
    }
  });
  if (load) {
    widgetsCalendarSend(id, null);
  }
};