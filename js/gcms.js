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
  if (navigator.userAgent.indexOf("MSIE") > -1) {
    document.body.addClass("ie");
  }
  if (typeof use_ajax != 'undefined' && use_ajax == 1) {
    loader = new GLoader(WEB_URL + 'loader.php/index/controller/loader/index', getURL, function (xhr) {
      var content = $G('content');
      var datas = xhr.responseText.toJSON();
      if (datas) {
        editor = null;
        document.title = datas.topic.unentityify();
        if (datas.menu) {
          selectMenu(datas.menu);
        }
        content.setHTML(datas.detail);
        loader.init(content);
        datas.detail.evalScript();
        if (datas.to && $E(datas.to)) {
          window.scrollTo(0, $G(datas.to).getTop() - 10);
        } else if ($E('scroll-to')) {
          window.scrollTo(0, $G('scroll-to').getTop());
        }
        if ($E('db_elapsed') && datas.db_elapsed) {
          $E('db_elapsed').innerHTML = datas.db_elapsed;
        }
        if ($E('db_quries') && datas.db_quries) {
          $E('db_quries').innerHTML = datas.db_quries;
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
  var hs, q2, patt = /^lang_([a-z]{2,2})$/;
  forEach(document.body.getElementsByTagName("a"), function (item) {
    hs = patt.exec(item.id);
    if (hs) {
      item.onclick = function () {
        var lang = this.id.replace('lang_', '');
        var urls = document.location.toString().replace('#', '&').split('?');
        if (urls[1]) {
          var new_url = new Object();
          forEach(urls[1].split('&'), function (q) {
            q2 = q.split('=');
            if (q2.length == 2) {
              new_url[q2[0]] = q2[1];
            }
          });
          new_url['lang'] = lang;
          var qs = Array();
          for (var property in new_url) {
            qs.push(property + '=' + new_url[property]);
          }
          document.location = urls[0] + '?' + qs.join('&');
          return false;
        } else {
          return true;
        }
      };
    }
  });
  var _getCounter = function () {
    return 'counter=' + counter_time;
  };
  if (COUNTER_REFRESH_TIME > 0) {
    new GAjax().autoupdate(WEB_URL + 'xhr.php/index/model/useronline/index', COUNTER_REFRESH_TIME, _getCounter, function (xhr) {
      var datas = xhr.responseText.toJSON();
      if (datas) {
        for (var d in datas) {
          if (d == 'time') {
            counter_time = floatval(datas['time']);
          } else if ($E(d)) {
            $E(d).innerHTML = datas[d];
          }
        }
      }
    });
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
    forEach($G(id || 'content').elems('img'), function (item, index) {
      if (!$G(item).hasClass('nozoom')) {
        new preload(item, function () {
          if (floatval(this.width) > floatval(item.width)) {
            G_Lightbox.add(item);
          }
        });
      }
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
  var ds = xhr.responseText.toJSON();
  if (ds) {
    if (ds.alert && ds.alert != '') {
      alert(ds.alert);
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
      FB.api('/' + uid, {access_token: accessToken, fields: 'id,first_name,last_name,birthday,email,gender,link'}, function (response) {
        if (!response.error) {
          var q = new Array();
          for (var prop in response) {
            q.push(prop + '=' + response[prop]);
          }
          send(WEB_URL + 'xhr.php/index/model/fblogin/chklogin', 'u=' + encodeURIComponent(getCurrentURL()) + '&data=' + encodeURIComponent(q.join('&')) + '&token=' + encodeURIComponent($E('token').value), function (xhr) {
            var ds = xhr.responseText.toJSON();
            if (ds) {
              if (ds.alert) {
                alert(ds.alert);
              } else if (ds.isMember == 1) {
                if ($E('login_action')) {
                  var login_action = $E('login_action').value;
                  if (login_action == 1) {
                    ds.location = replaceURL('action', 'login');
                  } else if (login_action == 2) {
                    ds.location = 'back'
                  } else if (/^http.*/.test(login_action)) {
                    ds.location = login_action;
                  }
                }
                if (ds.location) {
                  if (ds.location == 'back') {
                    if (loader) {
                      loader.back();
                    } else {
                      window.history.go(-1);
                    }
                  } else {
                    window.location = ds.location;
                  }
                } else {
                  window.location = replaceURL('action', 'login');
                }
              }
            } else if (xhr.responseText != '') {
              alert(xhr.responseText);
            }
          });
        }
      });
    }
  }, {scope: 'email,user_birthday,public_profile'});
}
function initFacebook(appId, lng) {
  window.fbAsyncInit = function () {
    FB.init({
      appId: appId,
      cookie: true,
      status: true,
      xfbml: true,
      version: 'v2.6'
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