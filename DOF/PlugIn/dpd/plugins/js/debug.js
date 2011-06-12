var DubroxPhpDebugger = {
	version: '1.2',
	errorBox: document.createElement("div"),
	init: function () {
		DubroxPhpDebugger.errorBox.setAttribute("class","errorBox");
		DubroxPhpDebugger.errorBox.setAttribute("onclick","DubroxPhpDebugger.removeError()");
		DubroxPhpDebugger.errorBox.setAttribute("style","position:fixed;top:10px;right:10px;font-size:small;");
		window.onerror=DubroxPhpDebugger.errorHandler;
	},
	errorHandler: function (msg,url,line) {
		var error="<div style='background-color:#FFE4E1;border:5px solid #BB3030;padding:10px;' title='JS debugger v"+DubroxPhpDebugger.version+" - Click to close.'>";
		error+="<div style='font-weight: bold;'>" + msg + "</div>";
		error+="URL: " + url + "<br>";
		error+="Line: " + line + "</div>";
		DubroxPhpDebugger.errorBox.innerHTML += error;
		self.scroll(0,0);
		document.body.appendChild(DubroxPhpDebugger.errorBox);
	},
	removeError: function () {
		DubroxPhpDebugger.errorBox.innerHTML="";
		document.body.removeChild(DubroxPhpDebugger.errorBox);
	}
};

DubroxPhpDebugger.init();
