function downloadInitRow(tr, row) {
  forEach($G(tr).elems('input'), function (item, index) {
    if (index == 0) {
      $G(item).addEvent('keypress', numberOnly);
    }
  });
}