function initEvent() {
  var doChanged = function () {
    $E('to_h').disabled = this.checked;
    $E('to_m').disabled = this.checked;
  };
  $G('forever').addEvent('change', doChanged);
  doChanged.call($E('forever'));
}