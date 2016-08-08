/*
 gBanner class
 design by http://www.goragod.com (goragod wiriya)
 08-05-56
 */
var gBanner = GClass.create();
gBanner.prototype = {
  initialize: function (div, options) {
    this.options = {
      className: 'gbanner',
      buttonContainerClass: 'button_container_gbanner',
      buttonClass: 'button_gbanner',
      slideTime: 10000,
      showNumber: true,
      loop: true
    };
    for (var property in options) {
      this.options[property] = options[property];
    }
    this.container = $G(div);
    this.container.addClass(this.options.className);
    this.container.style.overflow = 'hidden';
    var p = document.createElement('p');
    this.container.appendChild(p);
    p.className = this.options.buttonContainerClass;
    p.style.zIndex = 2;
    var button = document.createElement('p');
    p.appendChild(button);
    button.className = this.options.buttonClass;
    this.button = $G(button);
    this.datas = new Array();
    var tmp = this;
    forEach(this.container.getElementsByTagName('figure'), function () {
      tmp._initItem(this);
    });
    this.currentId = -1;
  },
  add: function (picture, detail, url) {
    var figure = document.createElement('figure');
    this.container.appendChild(figure);
    var img = document.createElement('img');
    img.src = picture;
    img.className = 'nozoom';
    figure.appendChild(img);
    var figcaption = document.createElement('figcaption');
    figure.appendChild(figcaption);
    var a = document.createElement('a');
    a.href = url;
    a.target = '_blank';
    figcaption.appendChild(a);
    if (detail && detail != '') {
      var span = document.createElement('span');
      span.innerHTML = detail;
      a.appendChild(span);
    }
    this._initItem(figure);
    return this;
  },
  JSONData: function (data) {
    try {
      var datas = eval('(' + data + ')');
      for (var i = 0; i < datas.length; i++) {
        this.add(datas[i].picture, datas[i].detail, datas[i].url);
      }
    } catch (e) {
    }
    return this;
  },
  _initItem: function (obj) {
    var i = this.datas.length;
    this.datas.push($G(obj));
    obj.style.display = i == 0 ? 'block' : 'none';
    var a = $G(document.createElement('a'));
    this.button.appendChild(a);
    a.rel = i;
    if (this.options.showNumber) {
      a.appendChild(document.createTextNode(i + 1));
    }
    var tmp = this;
    a.addEvent('click', function () {
      tmp.fade.stop();
      window.clearTimeout(tmp.SlideTime);
      tmp._show(this.rel);
    });
  },
  _nextslide: function () {
    var next = this.currentId + 1;
    if (next >= this.datas.length && this.options.loop) {
      next = 0;
    }
    if (next < this.datas.length && $E(this.container.id)) {
      this._show(next);
      if (this.datas.length > 1) {
        var temp = this;
        this.SlideTime = window.setTimeout(function () {
          temp._nextslide.call(temp)
        }, this.options.slideTime);
      }
    }
  },
  playSlideShow: function () {
    this._nextslide();
    return this;
  },
  _show: function (id) {
    if (this.datas[id]) {
      var temp = this;
      forEach(this.datas, function (item, index) {
        if (id == index) {
          item.style.display = 'block';
          item.style.zIndex = 1;
          item.getElementsByTagName('figcaption').className = 'show';
        } else if (temp.currentId == index) {
          item.style.display = 'list-item';
          item.style.zIndex = 0;
          item.getElementsByTagName('figcaption').className = '';
        } else {
          item.style.display = 'none';
          item.style.zIndex = 0;
          item.getElementsByTagName('figcaption').className = '';
        }
      });
      this.fade = new GFade(this.datas[id]).play({
        'onComplete': function () {
          temp.currentId = id;
          temp._setButton(id);
        }
      });
    }
  },
  _setButton: function (id) {
    forEach(this.button.getElementsByTagName('a'), function () {
      this.className = this.rel == id ? 'current' : '';
    });
  }
};