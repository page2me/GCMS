/*
 * Javascript Libraly for GCMS (front-end + back-end)
 *
 * @filesource js/common.js
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
var mtooltip,
  modal = null,
  loader = null,
  editor = null,
  G_Lightbox = null;
function mTooltipShow(id, action, method, elem) {
  if (Object.isNull(mtooltip)) {
    mtooltip = new GTooltip({
      className: 'member-tooltip',
      fade: true,
      cache: true
    });
  }
  mtooltip.showAjax(elem, action, method + '&id=' + id, function (xhr) {
    if (loader) {
      loader.init(this.tooltip);
    }
  });
}
function send(target, query, callback, wait, c) {
  var req = new GAjax();
  req.initLoading(wait || 'wait', false, c);
  req.send(target, query, function (xhr) {
    if (callback) {
      callback.call(this, xhr);
    }
  });
}
var hideModal = function () {
  if (modal != null) {
    modal.hide();
  }
};
function showModal(src, qstr, doClose) {
  send(src, qstr, function (xhr) {
    var ds = xhr.responseText.toJSON();
    var detail = '';
    if (ds) {
      if (ds.alert) {
        alert(ds.alert);
      } else if (ds.detail) {
        detail = decodeURIComponent(ds.detail);
      }
    } else {
      detail = xhr.responseText;
    }
    if (detail != '') {
      modal = new GModal({
        onclose: doClose
      }).show(detail);
      detail.evalScript();
    }
  });
}
function defaultSubmit(ds) {
  var _alert = '',
    _input = false,
    _url = false,
    _location = false,
    t, el,
    remove = /remove([0-9]{0,})/;
  for (var prop in ds) {
    var val = ds[prop];
    if (prop == 'error') {
      _alert = eval(val);
    } else if (prop == 'alert') {
      _alert = val;
    } else if (prop == 'location') {
      if (val == 'close') {
        if (modal) {
          modal.hide();
        }
      } else {
        _location = val;
      }
    } else if (prop == 'url') {
      _url = val;
      _location = val;
    } else if (prop == 'tab') {
      initWriteTab("accordient_menu", val);
    } else if (remove.test(prop)) {
      if ($E(val)) {
        $G(val).remove();
      }
    } else if (prop == 'input') {
      el = $G(val);
      t = el.title ? el.title.strip_tags() : '';
      if (t == '' && el.placeholder) {
        t = el.placeholder.strip_tags();
      }
      if (_input != el) {
        el.invalid(t);
      }
      if (t != '' && _alert == '') {
        _alert = t;
        _input = el;
      }
    } else if ($E(prop)) {
      $G(prop).setValue(decodeURIComponent(val).replace('%', '&#37;'));
    } else if ($E(prop.replace('ret_', ''))) {
      el = $G(prop.replace('ret_', ''));
      if (val == '') {
        el.valid();
      } else {
        if (val == 'this' || val == 'Please fill in') {
          if (el.placeholder) {
            t = el.placeholder.strip_tags();
          } else {
            t = el.title.strip_tags();
          }
          val = val == 'this' ? t : trans(val) + ' ' + t;
        }
        if (_input != el) {
          el.invalid(val);
        }
        if (_alert == '') {
          _alert = val;
          _input = el;
        }
      }
    }
  }
  if (_alert != '') {
    alert(_alert);
  }
  if (_input) {
    _input.focus();
    var tag = _input.tagName.toLowerCase();
    if (tag != 'select') {
      _input.highlight();
    }
    if (tag == 'input') {
      var type = _input.get('type').toLowerCase();
      if (type == 'text' || type == 'password') {
        _input.select();
      }
    }
  }
  if (_location) {
    if (_location == 'reload') {
      if (loader) {
        loader.reload();
      } else {
        reload();
      }
    } else if (_location == _url) {
      window.location = decodeURIComponent(_location);
    } else if (_location == 'back') {
      if (loader) {
        loader.back();
      } else {
        window.history.go(-1);
      }
    } else {
      window.location = _location.replace(/&amp;/g, '&');
    }
  }
}
function doFormSubmit(xhr) {
  var datas = xhr.responseText.toJSON();
  if (datas) {
    defaultSubmit(datas);
  } else if (xhr.responseText != '') {
    alert(xhr.responseText);
  }
}
function initWriteTab(id, sel) {
  var a;
  function _doclick(sel) {
    forEach($E(id).getElementsByTagName('a'), function () {
      a = this.id.replace('tab_', '');
      if ($E(a)) {
        this.className = a == sel ? 'select' : '';
        $E(a).style.display = a == sel ? 'block' : 'none';
      }
    });
    $E('tab').value = sel;
  }
  forEach($E(id).getElementsByTagName('a'), function () {
    if ($E(this.id.replace('tab_', ''))) {
      callClick(this, function () {
        _doclick(this.id.replace('tab_', ''));
        return false;
      });
    }
  });
  _doclick(sel);
}
function checkUsername() {
  var patt = /[a-zA-Z0-9]+/;
  var value = this.value;
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value == '') {
    this.invalid(this.title);
  } else if (patt.test(value)) {
    return 'value=' + encodeURIComponent(value) + id;
  } else {
    this.invalid(this.title);
  }
}
function checkEmail() {
  var value = this.value;
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value == '') {
    this.invalid(this.title);
  } else if (/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/.test(value)) {
    return 'value=' + encodeURIComponent(value) + id;
  } else {
    this.invalid(this.title);
  }
}
function checkPhone() {
  var value = this.value;
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value != '') {
    return 'value=' + encodeURIComponent(value) + id;
  }
}
function checkDisplayname() {
  var value = this.value;
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value.length < 2) {
    this.invalid(this.title);
  } else {
    return 'value=' + encodeURIComponent(value) + '&id=' + id;
  }
}
function checkPassword() {
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  var Password = $E(ids[0] + '_password');
  var Repassword = $E(ids[0] + '_repassword');
  if (Password.value == '' && Repassword.value == '') {
    if (id == 0) {
      this.Validator.invalid(this.Validator.title);
    } else {
      this.Validator.reset();
    }
    this.Validator.reset();
  } else if (Password.value == Repassword.value) {
    Password.Validator.valid();
    Repassword.Validator.valid();
  } else {
    this.Validator.invalid(this.Validator.title);
  }
}
function checkAntispam() {
  var value = this.value;
  if (value.length > 3) {
    return 'value=' + value + '&id=' + $E('antispam_id').value;
  } else {
    this.invalid(this.placeholder);
  }
}
function checkIdcard() {
  var value = this.value;
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  var i, sum;
  if (value.length != 13) {
    this.invalid(this.title);
  } else {
    for (i = 0, sum = 0; i < 12; i++) {
      sum += parseFloat(value.charAt(i)) * (13 - i);
    }
    if ((11 - sum % 11) % 10 != parseFloat(value.charAt(12))) {
      this.invalid(this.title);
    } else {
      return 'value=' + encodeURIComponent(value) + '&id=' + id;
    }
  }
}
function checkAlias() {
  var value = this.value;
  if (value.length < 3) {
    this.invalid(this.title);
  } else {
    return 'val=' + encodeURIComponent(value) + '&id=' + $E('id').value;
  }
}
function replaceURL(key, value) {
  var q,
    prop,
    urls = window.location.toString().replace('#', '&').replace('?', '&').split('&'),
    new_url = new Object(),
    qs = Array(),
    l = urls.length;
  if (l > 1) {
    for (var n = 1; n < l; n++) {
      if (urls[n] != 'action=login' && urls[n] != 'action=logout') {
        q = urls[n].split('=');
        if (q.length == 2) {
          new_url[q[0]] = q[1];
        }
      }
    }
  }
  new_url[key] = value;
  for (prop in new_url) {
    if (new_url[prop]) {
      qs.push(prop + '=' + new_url[prop]);
    } else {
      qs.push(prop);
    }
  }
  return urls[0] + '?' + qs.join('&');
}
function reload() {
  window.location = replaceURL(new Date().getTime());
}
function getWebUri() {
  var port = floatval(window.location.port);
  var protocol = window.location.protocol;
  if ((protocol == 'http:' && port == 80) || (protocol == 'https:' && port == 443)) {
    port = '';
  } else {
    port = port > 0 ? ':' + port : '';
  }
  return protocol + '//' + window.location.hostname + port + '/';
}
function _doCheckKey(input, e, patt) {
  var val = input.value;
  var key = GEvent.keyCode(e);
  if (!((key > 36 && key < 41) || key == 8 || key == 9 || key == 13 || GEvent.isCtrlKey(e))) {
    val = String.fromCharCode(key);
    if (val !== '' && !patt.test(val)) {
      GEvent.stop(e);
      return false;
    }
  }
  return true;
}
var numberOnly = function (e) {
  return _doCheckKey(this, e, /[0-9]/);
};
var integerOnly = function (e) {
  return _doCheckKey(this, e, /[0-9\-]/);
};
var currencyOnly = function (e) {
  return _doCheckKey(this, e, /[0-9\.]/);
};
function setSelect(id, value) {
  forEach($E(id).getElementsByTagName('input'), function () {
    if (this.type.toLowerCase() == 'checkbox') {
      this.checked = value;
    }
  });
}
function selectChanged(src, action, callback) {
  $G(src).addEvent('change', function () {
    var temp = this;
    send(action, 'id=' + this.id + '&value=' + this.value, function (xhr) {
      if (xhr.responseText !== '') {
        callback.call(temp, xhr);
      }
    });
  });
}
var doCustomConfirm = function (value) {
  return confirm(value);
};
function contryChanged(prefix) {
  var _contryChanged = function () {
    if (this.value != 'TH') {
      $G($E(prefix + '_provinceID').parentNode.parentNode).addClass('hidden');
      $G($E(prefix + '_province').parentNode.parentNode).removeClass('hidden');
    } else {
      $G($E(prefix + '_provinceID').parentNode.parentNode).removeClass('hidden');
      $G($E(prefix + '_province').parentNode.parentNode).addClass('hidden');
    }
  };
  if ($E(prefix + '_country')) {
    $G(prefix + '_country').addEvent('change', _contryChanged);
    _contryChanged.call($E(prefix + '_country'));
  }
}
$G(window).Ready(function () {
  var fontSize = floatval(Cookie.get('fontSize')),
    patt = /font_size(.*?)\s(small|normal|large)/;
  var _doChangeFontSize = function () {
    fontSize = floatval(document.body.getStyle('fontSize'));
    var hs = patt.exec(this.className);
    if (hs[2] == 'small') {
      fontSize = Math.max(6, fontSize - 2);
    } else if (hs[2] == 'large') {
      fontSize = Math.min(24, fontSize + 2);
    } else {
      fontSize = document.body.get('data-fontSize');
    }
    document.body.setStyle('fontSize', fontSize + 'px');
    Cookie.set('fontSize', fontSize);
    return false;
  };
  document.body.set('data-fontSize', floatval(document.body.getStyle('fontSize')));
  forEach(document.body.getElementsByTagName('a'), function () {
    if (patt.test(this.className)) {
      callClick(this, _doChangeFontSize);
    }
  });
  if (fontSize > 5) {
    document.body.setStyle('fontSize', fontSize + 'px');
  }
  if (navigator.userAgent.indexOf("MSIE") > -1) {
    document.body.addClass("ie");
  }
  var _doMenuClick = function () {
    if ($E('wait')) {
      $E('wait').className = 'show';
    }
  };
  forEach($E(document.body).getElementsByTagName('nav'), function () {
    if ($G(this).hasClass('topmenu sidemenu slidemenu gddmenu')) {
      new GDDMenu(this, _doMenuClick);
    }
  });
  var _scrolltop = 0;
  var toTop = 100;
  if ($E('toTop')) {
    if ($G('toTop').hasClass('fixed_top')) {
      document.addEvent('toTopChange', function () {
        if (document.body.hasClass('toTop')) {
          var _toTop = $G('toTop').copy();
          _toTop.zIndex = -1;
          _toTop.id = 'toTop_temp';
          _toTop.setStyle('opacity', 0);
          _toTop.removeClass('fixed_top');
          $G('toTop').after(_toTop);
        } else if ($E('toTop_temp')) {
          $G('toTop_temp').remove();
        }
      });
    }
    toTop = $E('toTop').getTop();
  }
  document.addEvent('scroll', function () {
    var c = this.viewport.getscrollTop() > toTop;
    if (_scrolltop != c) {
      _scrolltop = c;
      if (c) {
        document.body.addClass('toTop');
        document.callEvent('toTopChange');
      } else {
        document.body.removeClass('toTop');
        document.callEvent('toTopChange');
      }
    }
  });
});