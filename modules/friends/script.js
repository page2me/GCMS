function initFriends(id, form, province) {
  $G(window).Ready(function () {
    $G(province).addEvent('change', function () {
      setQueryURL('province', this.value);
    });
    new GForm(form, WEB_URL + 'xhr.php/friends/model/write/save').onsubmit(doFormSubmit);
  });
  initDocumentView(id, 'friends');
}