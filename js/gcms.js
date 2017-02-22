/*
 * Javascript Libraly for GCMS (front-end)
 *
 * @filesource js/gcms.js
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
function initSearch(form, input, module) {
  var doSubmit = function (e) {
    input = $G(input);
    var v = input.value.trim();
    if (v.length < 2) {
      input.invalid();
      alert(input.title);
      input.focus();
    } else {
      loaddoc(WEB_URL + 'index.php?module=' + $E(module).value + '&q=' + encodeURIComponent(v));
    }
    GEvent.stop(e);
    return false;
  };
  $G(form).addEvent('submit', doSubmit);
}
function getCurrentURL() {
  var patt = /^(.*)=(.*)$/;
  var patt2 = /^[0-9]+$/;
  var urls = new Object();
  var u = window.location.href;
  var us2 = u.split('#');
  u = us2.length == 2 ? us2[0] : u;
  var us1 = u.split('?');
  u = us1.length == 2 ? us1[0] : u;
  if (us1.length == 2) {
    forEach(us1[1].split('&'), function () {
      hs = patt.exec(this);
      if (hs) {
        urls[hs[1].toLowerCase()] = this;
      } else {
        urls[this] = this;
      }
    });
  }
  if (us2.length == 2) {
    forEach(us2[1].split('&'), function () {
      hs = patt.exec(this);
      if (hs) {
        if (MODULE_URL == '1' && hs[1] == 'module') {
          if (hs[2] == FIRST_MODULE) {
            u = WEB_URL + 'index.php';
          } else {
            u = WEB_URL + hs[2].replace('-', '/') + '.html';
          }
        } else if (hs[1] != 'visited') {
          urls[hs[1].toLowerCase()] = this;
        }
      } else if (!patt2.test(this)) {
        urls[this] = this;
      }
    });
  }
  var us = new Array();
  for (var p in urls) {
    us.push(urls[p]);
  }
  if (us.length > 0) {
    u += '?' + us.join('&');
  }
  return u;
}
var createLikeButton = $G.emptyFunction;
var counter_time = 0;
$G(window).Ready(function () {
  forEach(document.body.getElementsByTagName("a"), function () {
    if (/^lang_([a-z]{2,2})$/.test(this.id)) {
      callClick(this, function () {
        var hs = /^lang_([a-z]{2,2})$/.exec(this.id);
        window.location = replaceURL('lang', hs[1]);
        return false;
      });
    }
  });
  if (typeof use_ajax != 'undefined' && use_ajax == 1) {
    loader = new GLoader(WEB_URL + 'loader.php/index/controller/loader/index', getURL, function (xhr) {
      var scroll_to = 'scroll-to';
      var content = $G('content');
      var datas = xhr.responseText.toJSON();
      if (datas) {
        for (var prop in datas) {
          var value = datas[prop];
          if (prop == 'detail') {
            content.setHTML(value);
            loader.init(content);
            value.evalScript();
          } else if (prop == 'topic') {
            document.title = value.unentityify();
          } else if (prop == 'menu') {
            selectMenu(value);
          } else if (prop == 'to') {
            scroll_to = value;
          } else if ($E(prop)) {
            $E(prop).innerHTML = value;
          }
        }
        if ($E(scroll_to)) {
          window.scrollTo(0, $G(scroll_to).getTop() - 10);
        }
        if (Object.isFunction(createLikeButton)) {
          createLikeButton();
        }
      } else {
        content.setHTML(xhr.responseText);
      }
    });
    loader.initLoading('wait', false);
    loader.init(document);
  }
});
var getURL = function (url) {
  var loader_patt0 = /.*?module=.*?/;
  var loader_patt1 = new RegExp('^' + WEB_URL + '([a-z0-9]+)/([0-9]+)/([0-9]+)/(.*).html$');
  var loader_patt2 = new RegExp('^' + WEB_URL + '([a-z0-9]+)/([0-9]+)/(.*).html$');
  var loader_patt3 = new RegExp('^' + WEB_URL + '([a-z0-9]+)/([0-9]+).html$');
  var loader_patt4 = new RegExp('^' + WEB_URL + '([a-z0-9]+)/(.*).html$');
  var loader_patt5 = new RegExp('^' + WEB_URL + '(.*).html$');
  var p1 = /module=(.*)?/;
  var urls = url.split('?');
  var new_q = new Array();
  if (urls[1] && loader_patt0.exec(urls[1])) {
    new_q.push(urls[1]);
    return new_q;
  } else if (hs = loader_patt1.exec(urls[0])) {
    new_q.push('module=' + hs[1] + '&cat=' + hs[2] + '&id=' + hs[3]);
  } else if (hs = loader_patt2.exec(urls[0])) {
    new_q.push('module=' + hs[1] + '&cat=' + hs[2] + '&alias=' + hs[3]);
  } else if (hs = loader_patt3.exec(urls[0])) {
    new_q.push('module=' + hs[1] + '&cat=' + hs[2]);
  } else if (hs = loader_patt4.exec(urls[0])) {
    new_q.push('module=' + hs[1] + '&alias=' + hs[2]);
  } else if (hs = loader_patt5.exec(urls[0])) {
    new_q.push('module=' + hs[1]);
  } else {
    return null;
  }
  if (urls[1]) {
    forEach(urls[1].split('&'), function (q) {
      if (q != 'action=logout' && q != 'action=login' && !p1.test(q)) {
        new_q.push(q);
      }
    });
  }
  return new_q;
};
function selectMenu(module) {
  if ($E('topmenu')) {
    var tmp = false;
    forEach($E('topmenu').getElementsByTagName('li'), function (item, index) {
      var cs = new Array();
      if (index == 0) {
        tmp = item;
      }
      forEach(this.className.split(' '), function (c) {
        if (c == module) {
          tmp = false;
          cs.push(c + ' select');
        } else if (c !== '' && c != 'select' && c != 'default') {
          cs.push(c);
        }
      });
      this.className = cs.join(' ');
    });
    if (tmp) {
      $G(tmp).addClass('default');
    }
  }
}
function initIndex(id) {
  $G(window).Ready(function () {
    if (G_Lightbox === null) {
      G_Lightbox = new GLightbox();
    } else {
      G_Lightbox.clear();
    }
    var content = $G(id || 'content');
    forEach(content.elems('img'), function (item) {
      if (!$G(item).hasClass('nozoom')) {
        new preload(item, function () {
          if (floatval(this.width) > floatval(item.width)) {
            G_Lightbox.add(item);
          }
        });
      }
    });
    forEach(content.getElementsByClassName('copytoclipboard'), function () {
      callClick(this, function () {
        var element = this.parentNode;
        if (document.selection) {
          var range = document.body.createTextRange();
          range.moveToElementText(element);
          range.select();
        } else if (window.getSelection) {
          var range = document.createRange();
          range.selectNode(element);
          window.getSelection().removeAllRanges();
          window.getSelection().addRange(range);
        }
        document.execCommand('copy');
        document.body.msgBox(trans('successfully copied to clipboard'));
      });
    });
  });
}
function changeLanguage(lang) {
  $G(window).Ready(function () {
    forEach(lang.split(','), function () {
      $G('lang_' + this).addEvent('click', function (e) {
        GEvent.stop(e);
        window.location = replaceURL('lang', this.title);
      });
    });
  });
}
var doLoginSubmit = function (xhr) {
  var el, t, ds = xhr.responseText.toJSON();
  if (ds) {
    if (ds.alert && ds.alert != '') {
      if (ds.alert == 'Please fill in' && ds.input && $E(ds.input)) {
        el = $E(ds.input);
        if (el.placeholder) {
          t = el.placeholder.strip_tags();
        } else {
          t = el.title.strip_tags();
        }
        alert(trans(ds.alert) + ' ' + t);
      } else {
        alert(ds.alert);
      }
    }
    if (ds.action) {
      if (ds.action == 2) {
        if (loader) {
          loader.back();
        } else {
          window.history.back();
        }
      } else if (ds.action == 1) {
        window.location = replaceURL('action', 'login');
      }
    }
    if (ds.content) {
      hideModal();
      var content = decodeURIComponent(ds.content);
      var login = $G('login-box');
      login.setHTML(content);
      content.evalScript();
      if (loader) {
        loader.init(login);
      }
    }
    if (ds.input) {
      $G(ds.input).invalid().focus();
    }
  } else if (xhr.responseText != '') {
    alert(xhr.responseText);
  }
};
var doLogout = function (e) {
  setQueryURL('action', 'logout');
};
var doMember = function (e) {
  GEvent.stop(e);
  var action = $G(this).id;
  if (this.hasClass('register')) {
    action = 'register';
  } else if (this.hasClass('forgot')) {
    action = 'forgot';
  }
  showModal(WEB_URL + 'xhr.php', 'class=Index\\Member\\Controller&method=modal&action=' + action);
  return false;
};
function setQueryURL(key, value) {
  var a = new Array();
  var patt = new RegExp(key + '=.*');
  var ls = window.location.toString().split(/[\?\#]/);
  if (ls.length == 1) {
    window.location = ls[0] + '?' + key + '=' + value;
  } else {
    forEach(ls[1].split('&'), function (item) {
      if (!patt.test(item)) {
        a.push(item);
      }
    });
    var url = ls[0] + '?' + key + '=' + value + (a.length == 0 ? '' : '&' + a.join('&'));
    if (key == 'action' && value == 'logout') {
      window.location = url;
    } else {
      loaddoc(url);
    }
  }
}
function fbLogin() {
  FB.login(function (response) {
    if (response.authResponse) {
      var accessToken = response.authResponse.accessToken;
      var uid = response.authResponse.userID;
      FB.api('/' + uid, {access_token: accessToken, fields: 'id,first_name,last_name,email,link'}, function (response) {
        if (!response.error) {
          var q = new Array();
          if ($E('token')) {
            q.push('token=' + encodeURIComponent($E('token').value));
          }
          for (var prop in response) {
            q.push(prop + '=' + encodeURIComponent(response[prop]));
          }
          send(WEB_URL + 'xhr.php/index/model/fblogin/chklogin', q.join('&'), function (xhr) {
            var ds = xhr.responseText.toJSON();
            if (ds) {
              if (ds.alert) {
                alert(ds.alert);
              }
              if (ds.isMember == 1) {
                var login_action;
                if ($E('login_action')) {
                  login_action = $E('login_action').value;
                } else {
                  login_action = replaceURL('action', 'login');
                }
                if (login_action == 2) {
                  if (loader) {
                    loader.back();
                  } else {
                    window.history.back();
                  }
                } else {
                  window.location = login_action;
                }
              }
            } else if (xhr.responseText != '') {
              alert(xhr.responseText);
            }
          });
        }
      });
    }
  }, {scope: 'email,public_profile'});
}
function initFacebook(appId, lng) {
  window.fbAsyncInit = function () {
    FB.init({
      appId: appId,
      cookie: true,
      status: true,
      xfbml: true,
      version: 'v2.8'
    });
  };
  (function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
      return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/" + (lng == 'th' ? 'th_TH' : 'en_US') + "/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
}
function getWidgetNews(id, module, interval, callback) {
  var req = new GAjax();
  var _callback = function (xhr) {
    if (xhr.responseText !== '') {
      if ($E(id)) {
        var div = $G(id);
        div.setHTML(xhr.responseText);
        if (Object.isFunction(callback)) {
          callback.call(div);
        }
        if (loader) {
          loader.init(div);
        }
      } else {
        req.abort();
      }
    }
  };
  var _getRequest = function () {
    return 'class=Widgets\\' + module + '\\Controllers\\Index&method=getWidgetNews&id=' + id;
  };
  if (interval == 0) {
    req.send(WEB_URL + 'xhr.php', _getRequest(), _callback);
  } else {
    req.autoupdate(WEB_URL + 'xhr.php', floatval(interval), _getRequest, _callback);
  }
}
function initWidgetTab(tab, id, module, category) {
  var patt = /tab_([0-9,]+)/;
  function getNews(wait, category) {
    var q = 'class=Widgets\\' + module + '\\Controllers\\Index&method=getWidgetNews&id=' + id + '&cat=' + category;
    send(WEB_URL + 'xhr.php', q, function (xhr) {
      $E(id).innerHTML = xhr.responseText;
    }, wait);
  }
  var _tabClick = function () {
    var temp = this;
    getNews(this, this.id.replace('tab_', ''));
    forEach($G(tab).elems('a'), function () {
      if (temp == this) {
        this.addClass('select');
      } else {
        $G(this).removeClass('select');
      }
    });
  };
  if (tab != '' && $E(tab)) {
    var firstitem = null;
    forEach($G(tab).elems('a'), function (item, index) {
      if (patt.test(item.id)) {
        callClick(item, _tabClick);
        if (index == 0) {
          firstitem = item;
        }
      }
    });
    if (firstitem) {
      _tabClick.call(firstitem);
    }
  } else {
    getNews('wait', category);
  }
}
var G_editor = null;
function initEditor(frm, editor, action) {
  $G(window).Ready(function () {
    if ($E(editor)) {
      G_editor = editor;
      new GForm(frm, action).onsubmit(doFormSubmit);
    }
  });
}
function initDocumentView(id, module) {
  $G(id).Ready(function () {
    var patt = /(quote|edit|delete|pin|lock|print|pdf)-([0-9]+)-([0-9]+)-([0-9]+)-(.*)$/;
    var viewAction = function (action) {
      var temp = this;
      send(WEB_URL + 'xhr.php/' + module + '/model/action/view', action, function (xhr) {
        var ds = xhr.responseText.toJSON();
        if (ds) {
          if (ds.action == 'quote') {
            var editor = $E(G_editor);
            if (editor && ds.detail !== '') {
              editor.value = editor.value + ds.detail;
              editor.focus();
            }
          } else if ((ds.action == 'pin' || ds.action == 'lock') && $E(ds.action + '_' + ds.qid)) {
            var a = $E(ds.action + '_' + ds.qid);
            a.className = a.className.replace(/(un)?(pin|lock)\s/, (ds.value == 0 ? 'un' : '') + '$2 ');
            a.title = ds.title;
          }
          if (ds.confirm) {
            if (confirm(ds.confirm)) {
              if (ds.action == 'deleting') {
                viewAction.call(temp, 'id=' + temp.className.replace('delete-', 'deleting-'));
              }
            }
          }
          if (ds.alert) {
            alert(ds.alert);
          }
          if (ds.location) {
            loaddoc(ds.location.replace(/&amp;/g, '&'));
          }
          if (ds.remove && $E(ds.remove)) {
            $G(ds.remove).remove();
          }
        } else if (xhr.responseText != '') {
          alert(xhr.responseText);
        }
      }, this);
    };
    var viewExport = function (action) {
      var hs = patt.exec(action);
      window.open(WEB_URL + 'print.php?action=' + hs[1] + '&id=' + hs[2] + '&module=' + hs[5], 'print');
    };
    forEach($G(id).elems('a'), function (item, index) {
      if (patt.exec(item.className)) {
        callClick(item, function () {
          var hs = patt.exec(this.className);
          if (hs[1] == 'print' || hs[1] == 'pdf') {
            viewExport(this.className);
          } else {
            viewAction.call(this, 'id=' + this.className);
          }
        });
      }
    });
    initIndex(id);
  });
}
function loaddoc(url) {
  if (loader && url != WEB_URL) {
    loader.location(url);
  } else {
    window.location = url;
  }
}