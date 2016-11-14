function initFB(token) {
  window.fbAsyncInit = function () {
    FB.init({
      appId: '385198338221385',
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
    js.src = "//connect.facebook.net/th_TH/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
  callClick('fb_login', function () {
    fbLogin(token);
  });
}
function fbLogin(token) {
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
          send('index.php/index/model/fblogin/chklogin', 'u=' + encodeURIComponent(window.location) + '&data=' + encodeURIComponent(q.join('&')) + '&token=' + token, function (xhr) {
            var ds = xhr.responseText.toJSON();
            if (ds) {
              if (ds.alert) {
                alert(ds.alert);
              } else if (ds.isMember == 1) {
                window.location = 'index.php';
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