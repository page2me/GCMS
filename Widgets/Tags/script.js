function initTags(id) {
  var patt = /tags-([0-9]+)/;
  forEach($G(id).elems('a'), function (item) {
    if (patt.exec(item.id)) {
      $G(item).addEvent('mouseover', function () {
        mTooltipShow(this.id, WEB_URL + 'xhr.php', 'class=Widgets\\Tags\\Controllers\\Tooltip&method=get', this);
      });
      $G(item).addEvent('click', function () {
        send(WEB_URL + 'xhr.php', 'class=Widgets\\Tags\\Models\\Clicked&method=save&id=' + this.id);
      });
    }
  });
}