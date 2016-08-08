function initEventCalendar() {
  var patt = /^(prev|next)_([0-9]+)_([0-9]+)$/;
  var _calendarClick = function () {
    send(WEB_URL + 'xhr.php', 'class=Event\\Xhr\\Controller&method=get&id=' + this.id, function (xhr) {
      var calendar = $G('event-calendar');
      calendar.setHTML(xhr.responseText);
      initEventCalendar();
      if (loader) {
        loader.inint(calendar);
      }
    });
  };
  var w = ($G('event-calendar').getWidth() / 8) + 'px';
  forEach($G('event-calendar').elems('*'), function () {
    var tag = this.tagName.toLowerCase();
    if (tag == 'td') {
      this.style.width = w;
    } else if (patt.test(this.id)) {
      callClick(this, _calendarClick);
    } else if (tag == 'a' && this.className == 'cuttext') {
      this.style.width = w;
    }
  });
}