function initGalleryUpload(id, album_id) {
  var patt = /^(preview|edit|delete)_([0-9]+)(_([0-9]+))?$/;
  if (G_Lightbox === null) {
    G_Lightbox = new GLightbox();
  } else {
    G_Lightbox.clear();
  }
  var _doDelete = function () {
    var cs = new Array();
    forEach($E(id).getElementsByTagName('a'), function () {
      if (this.className == 'icon-check') {
        var hs = patt.exec(this.id);
        cs.push(hs[2]);
      }
    });
    if (cs.length == 0) {
      alert(trans('Please select at least one item'));
    } else if (confirm(trans('You want to XXX the selected items ?').replace(/XXX/, this.value))) {
      send('index.php/gallery/model/admin/setup/action', 'action=deletep&mid=' + $E('module_id').value + '&album=' + album_id + '&id=' + cs.join(','), doFormSubmit, this);
    }
  };
  var _doAction = function () {
    var hs = patt.exec(this.id);
    if (hs[1] == 'delete') {
      this.className = this.className == 'icon-check' ? 'icon-uncheck' : 'icon-check';
    }
    return false;
  };
  forEach($E(id).getElementsByTagName('a'), function () {
    var hs = patt.exec(this.id);
    if (hs) {
      if (hs[1] == 'preview') {
        G_Lightbox.add(this);
      } else {
        callClick(this, _doAction);
      }
    }
  });
  var _setSel = function () {
    var chk = this.id == 'selectAll' ? 'icon-check' : 'icon-uncheck';
    forEach($E(id).getElementsByTagName('a'), function () {
      var hs = patt.exec(this.id);
      if (hs && hs[1] == 'delete') {
        this.className = chk;
      }
    });
  };
  var galleryUploadResult = function (error, count) {
    if (error != "") {
      alert(error);
    }
    if (count > 0) {
      alert(trans('Successfully uploaded XXX files').replace('XXX', count));
    }
    if (this.location) {
      window.location = this.location;
    } else {
      window.location.reload();
    }
  };
  var upload = new GUploads({
    form: id,
    input: "fileupload",
    fileprogress: "fsUploadProgress",
    oncomplete: galleryUploadResult,
    onupload: function () {
      $E("btnCancle").disabled = false;
    },
    customSettings: {albumId: album_id}
  });
  callClick('btnDelete', _doDelete);
  callClick("btnCancle", function () {
    upload.cancle()
  });
  callClick("selectAll", _setSel);
  callClick("clearSelected", _setSel);
}