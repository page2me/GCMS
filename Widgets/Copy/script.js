function addCopyLink(e) {
	var selection = window.getSelection();
	var url = getCurrentURL();
	if (e.clipboardData) {
		e.clipboardData.setData("text/plain", selection + "\n\n อ่านต่อได้ที่: " + url);
		e.clipboardData.setData("text/html", selection + '<br /><br /> อ่านต่อได้ที่: ' + url);
		GEvent.stop(e);
	} else {
		var div = document.createElement('div');
		div.style.position = 'absolute';
		div.style.left = '-99999px';
		document.body.appendChild(div);
		div.innerHTML = selection + '<br /><br /> อ่านต่อได้ที่: ' + url;
		selection.selectAllChildren(div);
		window.setTimeout(function () {
			document.body.removeChild(div);
		}, 100);
	}
}
$G(window).Ready(function () {
	document.addEvent('copy', addCopyLink);
});