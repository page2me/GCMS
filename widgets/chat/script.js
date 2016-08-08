/*
 Chat System
 27 มิย. 56
 */
var GChat = GClass.create();
GChat.prototype = {
	initialize: function (o) {
		var gchat_user = '';
		var gchat_time = 0;
		var gchat_interval = o.interval || 3;
		var gchat_lines = o.lines || 10;
		var gchat_smiles = new Array();
		var gchat_id = 0;
		var gchat_text = '';
		var gchat_div = $G('gchat_div');
		var content = $G('gchat_content');
		var text = $G('gchat_text');
		var gchat_sound = $G('gchat_sound');
		forEach($E('gchat_smile').getElementsByTagName('img'), function () {
			gchat_smiles.push(new RegExp(':(' + this.alt + '):', 'g'));
			callClick(this, function () {
				if (!text.disabled) {
					text.value = text.value + ':' + this.alt + ':';
					text.focus();
				}
			});
		});
		function limitContent() {
			for (var i = content.childNodes.length - gchat_lines - 1; i >= 0; i--) {
				content.removeChild(content.childNodes[i]);
			}
		}
		function playSound(id) {
			if (gchat_sound.className == 'icon-vol-up') {
				var audio = null;
				var src = WEB_URL + 'widgets/chat/sound/' + id + '.mp3';
				if (window.HTMLAudioElement) {
					audio = new Audio();
					if (!!(audio.canPlayType && audio.canPlayType('audio/mpeg;').replace(/no/, ''))) {
						audio.src = src;
						audio.play();
					} else {
						audio = null;
					}
				}
				if (audio == null) {
					var play_sound = $E('play_sound');
					if (!play_sound) {
						play_sound = document.createElement('div');
						play_sound.style.position = 'absolute';
						play_sound.style.left = '-10000px';
						play_sound.style.top = '-10000px';
						play_sound.id = 'play_sound';
						document.body.appendChild(play_sound);
					}
					if ($E('live_audio')) {
						play_sound.removeChild($E('live_audio'));
					}
					audio = document.createElement(GBrowser.IE ? 'bgsound' : 'embed');
					audio.setAttribute('src', src);
					audio.setAttribute('autostart', true);
					audio.setAttribute('hidden', true);
					audio.setAttribute('id', 'live_audio');
					play_sound.appendChild(audio);
				}
			}
		}
		function entityify(s) {
			return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}
		var row = '';
		function addMessage(time, name, text) {
			gchat_id = time;
			text = text.replace(/(^|\s)((http(s)?|ftp|file):\/\/([^\s<>\"\']+))/gim, '$1<a href="$2" target="_blank">$2</a>');
			text = text.replace(/(^|\s)(www([^\s<>\"\']+))/gim, '$1<a href="http://$2" target="_blank">$2</a>');
			for (var i = 0; i < gchat_smiles.length; i++) {
				text = text.replace(gchat_smiles[i], '<img src="' + WEB_URL + 'widgets/chat/smile/$1.gif" alt="$1" />');
			}
			row = row == 'bg1' ? 'bg2' : 'bg1';
			var result = '<dd class="' + row + '" id="gchat_' + time + '">';
			result += '<span class="time">' + mktimeToDate(time).dateFormat('H:I:S') + '</span>';
			result += '<span class="user">' + name + '</span>';
			result += text + '</dd>';
			content.appendChild(result.toDOM());
		}
		var _doSend = function () {
			var q = text.value;
			if (!text.disabled && q != '' && q != gchat_text) {
				gchat_text = q;
				addMessage(gchat_time, gchat_user, entityify(gchat_text));
				q = 'val=' + encodeURIComponent(q);
				q += '&time=' + gchat_time;
				new GAjax().send(WEB_URL + 'widgets/chat/send.php', q, emptyFunction);
				text.value = '';
				content.scrollTop = content.scrollHeight;
				playSound('type');
				text.focus();
			}
			return false;
		};
		$E('gchat_frm').onsubmit = _doSend;
		callClick(gchat_sound, function () {
			this.className = this.className == 'icon-vol-up' ? 'icon-vol-down' : 'icon-vol-up';
		});
		var _getChat = function () {
			return 'id=' + gchat_id;
		};
		var _chat = new GAjax();
		_chat.inint = true;
		_chat.autoupdate(WEB_URL + 'widgets/chat/chat.php', gchat_interval, _getChat, function (xhr) {
			if ($E(content.id)) {
				var ds = xhr.responseText.toJSON();
				if (ds) {
					if (ds[0]['user']) {
						gchat_user = ds[0]['user'];
						text.disabled = false;
					} else {
						text.disabled = true;
					}
					if (ds[0]['time']) {
						gchat_time = ds[0]['time'].toInt();
					}
					if (ds[0]['id']) {
						gchat_id = ds[0]['id'].toInt();
					}
					if (ds[0]['content']) {
						var hs = decodeURIComponent(ds[0]['content']).split(/\n/g);
						for (var i = hs.length - 1; i >= 0; i--) {
							var ds = hs[i].split(/\t/g);
							addMessage(ds[2], ds[1], ds[0]);
						}
						limitContent();
						content.scrollTop = content.scrollHeight;
						if (!_chat.inint) {
							playSound('type');
						}
						_chat.inint = false;
					}
				} else if (xhr.responseText != '') {
					alert(xhr.responseText);
				}
			} else {
				_chat.abort();
			}
		});
		window.setInterval(function () {
			gchat_time++;
		}, 1000);
	}
};
