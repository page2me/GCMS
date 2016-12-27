// modules/doc/setup.js
function inintDocWrite(id, module_id) {
	inintCheck(id);
	inintTR(id, /M_[0-9]+/);
	var req = new GAjax();
	function _send(src, q) {
		var _class = src.className;
		src.className = 'icon-loading';
		req.send(WEB_URL + 'modules/doc/admin_action.php', q, function(xhr) {
			src.className = _class;
			if (xhr.responseText != '') {
				alert(xhr.responseText);
			} else {
				inintTR(id, /M_[0-9]+/);
			}
		});
	}
	new GSortTable(id, {
		endDrag: function() {
			var trs = new Array();
			forEach($G(id).elements('tr'), function() {
				if (this.id) {
					trs.push(this.id);
				}
			});
			if (trs.length > 1) {
				_send($E(this.id.replace('M_', 'move_')), 'module=' + module_id + '&action=move&data=' + trs.join(','));
			}
		}
	});
}