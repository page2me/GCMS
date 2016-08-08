function initEditInplace(id, module) {
  var editor, hs,
    patt = /config_status_(delete|name|color)_([0-9]+)/;
  function _doAction(c) {
    var q = '';
    if (this.id == id + '_add') {
      q = 'action=' + this.id;
    } else if (hs = patt.exec(this.id)) {
      if (hs[1] == 'delete' && confirm(trans('You want to delete ?'))) {
        q = 'action=' + this.id;
      } else if (hs[1] == 'color') {
        q = 'action=' + this.id + '&value=' + encodeURIComponent(c);
      }
    }
    if (q != '') {
      send('index.php/index/model/' + module + '/action', q, function (xhr) {
        var ds = xhr.responseText.toJSON();
        if (ds) {
          if (ds.data) {
            $G(id).appendChild(ds.data.toDOM());
            _doInitEditInplaceMethod(ds.newId);
            $E(ds.newId.replace('status', 'status_name')).focus();
          } else if (ds.del) {
            $G(ds.del).remove();
          } else if (ds.edit) {
            hs = patt.exec(ds.editId);
            if (hs[1] == 'color') {
              c = ds.edit;
              $E(ds.editId).title = trans('change color') + ' (' + c + ')';
              $E(ds.editId.replace('color', 'name')).style.color = c;
            }
          }
          if (ds.alert) {
            alert(ds.alert);
          }
        } else if (xhr.responseText != '') {
          alert(xhr.responseText);
        }
      }, this);
    }
  }
  var o = {
    onSave: function (v) {
      var req = new GAjax({
        asynchronous: false
      });
      req.initLoading(this, false);
      req.send('index.php/index/model/' + module + '/action', 'action=' + this.id + '&value=' + encodeURIComponent(v));
      req.hideLoading();
      var ds = req.responseText.toJSON();
      if (ds) {
        if (ds.alert) {
          alert(ds.alert);
        }
        if (ds.edit) {
          $E(ds.editId).innerHTML = ds.edit;
        }
        return true;
      } else if (req.responseText != '') {
        alert(req.responseText);
      }
      return false;
    }
  };
  function _doInitEditInplaceMethod(id) {
    var loading = true;
    forEach($G(id).elems('*'), function () {
      var hs = patt.exec(this.id);
      if (hs) {
        if (hs[1] == 'delete') {
          callClick(this, _doAction);
        } else if (hs[1] == 'color') {
          var t = this.title;
          this.title = trans('change color') + ' (' + t + ')';
          new GDDColor(this, function (c) {
            $E(this.input.id.replace('color', 'name')).style.color = c;
            if (!loading) {
              _doAction.call(this.input, c);
            }
          }).setColor(t);
        } else if (hs[1] == 'name') {
          editor = new EditInPlace(this, o);
        }
      }
    });
    loading = false;
  }
  callClick(id + '_add', _doAction);
  _doInitEditInplaceMethod(id);
}
function initListCategory(module) {
  if (!module) {
    module = 'index';
  }
  var patt = /^categoryid_([0-9]+)_([0-9]+)$/;
  forEach($G('datatable').elems('input'), function () {
    if (patt.test(this.id)) {
      $G(this).addEvent('keypress', numberOnly);
      this.addEvent('change', function () {
        var hs = patt.exec(this.id);
        send('index.php/' + module + '/model/category/action', 'action=categoryid&mid=' + hs[1] + '&id=' + hs[2] + '&value=' + this.value, doFormSubmit, this);
      });
    }
  });
}
function initLanguages(id) {
  var patt = /^(edit|delete|check)_([a-z]{2,2})$/;
  var doClick = function () {
    var hs = patt.exec(this.id);
    var q = '';
    if (hs[1] == 'check') {
      this.className = this.className == 'icon-uncheck' ? 'icon-check' : 'icon-uncheck';
      var chs = new Array();
      forEach($E(id).getElementsByTagName('span'), function () {
        var cs = patt.exec(this.id);
        if (cs && cs[1] == 'check' && this.className == 'icon-check') {
          chs.push(this.id);
        }
      });
      if (chs.length == 0) {
        alert(trans('Please select at least one item'));
      } else {
        q = 'action=changed&data=' + chs.join(',');
      }
    } else if (hs[1] == 'delete' && confirm(trans('You want to delete ?'))) {
      q = 'action=droplang&data=' + hs[2];
    }
    if (q != '') {
      send('index.php/index/model/languages/save', q, doFormSubmit, this);
    }
  };
  forEach($E(id).getElementsByTagName('span'), function () {
    if (patt.test(this.id)) {
      callClick(this, doClick);
    }
  });
  new GSortTable(id, {
    'tag': 'li',
    'endDrag': function () {
      var trs = new Array();
      forEach($E(id).getElementsByTagName('li'), function () {
        if (this.id) {
          trs.push(this.id);
        }
      });
      if (trs.length > 1) {
        send('index.php/index/model/languages/save', 'action=move&data=' + trs.join(','), doFormSubmit);
      }
    }
  });
}
function initMailserver() {
  var doChanged = function () {
    var a = this.value.toInt();
    $E('email_SMTPSecure').disabled = (a == 0);
    $E('email_Username').disabled = (a == 0);
    $E('email_Password').disabled = (a == 0);
  };
  var el = $G('email_SMTPAuth');
  el.addEvent('change', doChanged);
  doChanged.call(el);
}
function initSystem() {
  var clearCache = function () {
    send('index.php/index/model/system/clearCache', 'action=clearcache', doFormSubmit, this);
  };
  callClick('clear_cache', clearCache);
  new Clock('local_time');
  new Clock('server_time');
}
function initMenuwrite() {
  var getMenus = function () {
    var t = $E('type').value;
    var sel = $E('menu_order');
    for (var i = sel.options.length - 1; i >= 0; i--) {
      sel.removeChild(sel.options[i]);
    }
    var q = 'action=get&parent=' + $E('parent').value + '&id=' + $E('id').value.toInt();
    send('index.php/index/model/menuwrite/action', q, function (xhr) {
      var id = $E('id').value.toInt();
      var option = sel.options[0];
      var ds = xhr.responseText.toJSON();
      if (ds) {
        for (prop in ds) {
          q = prop.replace('O_', '');
          if (prop == 'parent') {
            el = $G('parent');
            if (ds[prop] == '') {
              el.addClass('valid');
              el.removeClass('invalid');
              el.hideTooltip();
            } else {
              el.addClass('invalid');
              el.removeClass('valid');
              el.showTooltip(eval(ds[prop]));
            }
          } else if (id > 0 && q == id) {
            if (option) {
              option.selected = 'selected';
            }
          } else if (t > 0) {
            option = document.createElement('option');
            option.value = q;
            option.innerHTML = ds[prop];
            sel.appendChild(option);
          }
        }
      } else if (xhr.responseText != '') {
        alert(xhr.responseText);
      }
    });
  };
  var menuAction = function () {
    var c = $E('action').value;
    forEach($E('menu_action').getElementsByTagName('div'), function () {
      if ($G(this).hasClass('action')) {
        if ($G(this).hasClass(c)) {
          this.removeClass('hidden');
        } else {
          this.addClass('hidden');
        }
      }
    });
  };
  var doCopy = function () {
    var lng = $E('language').value;
    var id = $E('id').value.toInt();
    if (id > 0 && lng !== '') {
      send('index.php/index/model/menuwrite/action', 'action=copy&id=' + id + '&lng=' + lng, doFormSubmit);
    }
  };
  $G("copy_menu").addEvent("click", doCopy);
  $G('action').addEvent('change', menuAction);
  $G('parent').addEvent('change', getMenus);
  $G('type').addEvent('change', getMenus);
  getMenus.call(this);
  menuAction();
}
var indexActionCallback = function (xhr) {
  var el,
    val,
    toplv = -1,
    ds = xhr.responseText.toJSON();
  if (ds) {
    for (prop in ds) {
      val = ds[prop];
      if (prop == 'location') {
        if (val == 'reload') {
          window.location.reload();
        } else {
          window.location.href = val;
        }
      } else if (prop == 'delete_id') {
        $G(val).remove();
      } else if (prop == 'alert') {
        alert(val);
      } else if (prop == 'elem') {
        el = $E(val);
        if (el) {
          el.className = ds.class;
          el.title = ds.title;
        }
      } else if ($E(prop)) {
        as = val.split('|');
        $E(prop).innerHTML = as[0];
        $E('move_left_' + as[2]).className = (as[1] == 0 ? 'hidden' : 'icon-move_left');
        $E('move_right_' + as[2]).className = (as[1] > toplv ? 'hidden' : 'icon-move_right');
        toplv = as[1];
      }
    }
  } else if (xhr.responseText != '') {
    alert(xhr.responseText);
  }
};
function doChangeLanguage(btn, url) {
  var doClick = function () {
    window.location = url + '&language=' + $E('language').value;
  };
  callClick(btn, doClick);
}
function checkIndexModule() {
  var value = this.input.value;
  var patt = /^[a-z0-9]{1,}$/;
  if (!patt.test(value)) {
    this.invalid(this.title);
  } else {
    return 'action=module&value=' + encodeURIComponent(value) + '&id=' + $E('id').value + '&lng=' + $E('language').value;
  }
}
function checkIndexTopic() {
  var value = this.input.value;
  if (value.length < 3) {
    this.invalid(this.title);
  } else {
    return 'action=topic&value=' + encodeURIComponent(value) + '&id=' + $E('id').value + '&lng=' + $E('language').value;
  }
}
var indexPreview = function () {
  var id = $E("id").value.toInt();
  if (id > 0) {
    window.open(WEB_URL + 'index.php?module=' + $E("owner").value + '&id=' + id, 'preview');
  }
};
var doIndexCopy = function () {
  var lng = $E('language').value;
  var id = $E('id').value.toInt();
  if (id > 0 && lng !== '') {
    send('index.php/index/model/pagewrite/copy', 'id=' + id + '&lng=' + lng + '&action=' + this.id, doFormSubmit);
  }
};
function initIndexWrite() {
  var module = new GValidator("module", "keyup,change", checkIndexModule, "index.php/index/model/checker/module", null, "setup_frm");
  var topic = new GValidator("topic", "keyup,change", checkIndexTopic, "index.php/index/model/checker/topic", null, "setup_frm");
  $G("language").addEvent("change", function () {
    if (topic.input.value != '') {
      topic.validate();
    }
    if (module.input.value != '') {
      module.validate();
    }
  });
  callClick('btn_copy', doIndexCopy);
  callClick('btn_preview', indexPreview);
}
function showDebug() {
  var t = 0;
  var _get = function () {
    return 'action=get&t=' + t;
  };
  new GAjax().autoupdate('index.php/index/model/debug/action', 5, _get, function (xhr) {
    var content = $E('debug_layer');
    forEach(xhr.responseText.split('\n'), function () {
      var line = this.split('\t');
      if (line.length == 3) {
        t = line[0];
        var div = document.createElement('div');
        var time = document.createElement('time');
        time.innerHTML = '<b>' + line[1] + '</b> : ' + t;
        div.appendChild(time);
        var p = document.createElement('p');
        p.innerHTML = line[2];
        div.appendChild(p);
        content.appendChild(div);
        content.scrollTop = content.scrollHeight;
      }
    });
  });
  $G('debug_clear').addEvent('click', function () {
    if (confirm(trans('You want to delete ?'))) {
      send('index.php/index/model/debug/action', 'action=clear', function (xhr) {
        $E('debug_layer').innerHTML = xhr.responseText;
      });
    }
  });
}
var confirmAction = function (msg, action, id, mid) {
  if (action == 'published' || action == 'can_reply' || action == 'move_left' || action == 'move_right') {
    return  'action=' + action + '&id=' + id + (mid ? '&mid=' + mid : '');
  } else if (confirm(trans('You want to XXX the selected items ?').replace(/XXX/, msg))) {
    return  'action=' + action + '&id=' + id + (mid ? '&mid=' + mid : '');
  }
  return '';
};
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
function checkSaved(button, url, write_id, target) {
  callClick(button, function () {
    var id = floatval($E(write_id).value);
    if (id == 0) {
      alert(trans('Please save before continuing'));
    } else if (target == '_self') {
      window.location = url.replace('&amp;', '&') + '&id=' + id;
    } else {
      window.open(url.replace('&amp;', '&') + '&id=' + id);
    }
  });
}
function getNews(div) {
  send('index.php/index/model/getnews/get', null, function (xhr) {
    $E(div).innerHTML = xhr.responseText;
  });
}
function getUpdate(v) {
  send('index.php/index/model/getupdate/get', 'v=' + v, function (xhr) {
    if (xhr.responseText != '') {
      var div = document.createElement('aside');
      div.innerHTML = xhr.responseText;
      div.className = 'message';
      $E('skip').insertBefore(div, $E('skip').getElementsByTagName('section')[0]);
    }
  });
}
function callInstall(c) {
  callClick('install_btn', function () {
    send('index.php/index/controller/installing', 'module=' + c, function (xhr) {
      ds = xhr.responseText.toJSON();
      if (ds) {
        $E('install').innerHTML = ds.content;
        if (ds.location) {
          window.setTimeout(function () {
            window.location = ds.location;
          }, 5000);
        }
      } else if (xhr.responseText != '') {
        $E('install').innerHTML = xhr.responseText;
      }
    });
  });
}