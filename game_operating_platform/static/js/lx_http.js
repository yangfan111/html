/*
 * http lib.
 */
var lx = lx || window['lx'];

if (lx == null) {

lx = {};

lx.create_xml_http_obj = function() {
	var httpobj
	if (window.XMLHttpRequest)
		//for IE7+, Firefox, Chrome, Opera, Safari
		httpobj = new XMLHttpRequest();
	else
		//for IE6, IE5
		httpobj = new ActiveXObject("Microsoft.XMLHTTP");

	return httpobj
}

/*
 * 执行post请求
 * @param {string} url 请求地址
 * @param {string} data 数据
 * @param {function} finish_func 执行成功时的回调函数，此函数接受1个参数【返回数据】
 * @param {function} error_func 执行出错时的回调函数，此函数接受2个参数【错误码、错误信息】
 */
lx.http_post = function(url, data, finish_func, error_func) {
	var xmlhttp = lx.create_xml_http_obj();
	if (xmlhttp == null) {
		if (error_func)
			error_func(-1, "create xml http obj failed!");
		return;
	}

	xmlhttp.open("POST", url, true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send(data);
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == this.DONE) {
			//this.responseText为web页面的输出流产生的内容。目前只需要此值。
			if (this.status != 200 || this.responseText == null) {
				if (error_func)
					error_func(this.status, "ok");
			} else {
				if (finish_func)
					finish_func(this.responseText);
			}
		}
	}
}

}
