var ajax = new AJAX(); ajax.setcharset("utf-8");
var StartTime=0;
var Interval=0;

function bindButtonFile(buttonid, inputbox, bVideo, bAdd) {
	KindEditor(buttonid).click(function () {
		window.editor.loadPlugin('filemanager', function () {
			window.editor.plugin.filemanagerDialog({
				viewType: bVideo ? 'LIST' : 'VIEW',
				dirName: bVideo ? 'media' : "image",
				clickFn: function (url, title) {
					if (bAdd) {
						text = KindEditor(inputbox).val();
						if (text.length > 0) url = text + "\n" + url;
					}
					KindEditor(inputbox).val(url);
					window.editor.hideDialog();
				}
			});
		});
	});
}


function bindButtonUpImage(buttonid,formid, inputbox) {
	$(buttonid).click(function() {
		$(formid).find('[name="imgFile"]').trigger('click');
	});

	$(formid).find('[name="imgFile"]').change(function() {
		if ($(this).val()) {
			$(formid).ajaxSubmit(function(message) {
				var obj = JSON.parse(message);
				if(obj.error!=0)
					layer.msg(obj.message);
				else
					$(inputbox).val(obj.url);

			});
		}
	});
	
}



var getElementById = function (el) {
	var id = el;
	el = document.getElementById(el);
	if (el.id === id)
		return el;
	else {
		var nodes = document.all[id];
		for (var i = 0, len = nodes.length; i < len; i++)
			if (nodes[i].id == id)
				return nodes[i];
	}
}
//document.getElementByIdNew = getElementById;


function openUpdateWin(width, height, str) {
	openWindow2(101, width, height, 50)
	var msgDiv = document.getElementById("msg")
	var bgDiv = document.getElementById("bg")
	var iWidth = document.documentElement.scrollWidth
	var str = "<div style='width:400px;'><div class='divboxtitle'><span onclick=\"closeWin();set(document.getElementById('update'),'<font color=red >版本暂未升级...</font>');\" ><img src='../pic/btn_close.gif'/></span>seacms在线升级</div><div  class='divboxbody'>" + str + "<input type='button' value='进入升级页面' id='openwin' class='rb1'/>&nbsp;&nbsp;&nbsp;&nbsp;<input id='closewin' type='button' value='取   消' name='button' class='rb1'  /></div><div class='divboxbottom'>Power By seacms</div></div>"
	msgDiv.style.cssText += "FONT-SIZE: 12px;top:100px;left:" + (iWidth - width) / 2 + "px;text-align:center;";
	set(msgDiv, str)
	document.getElementById("closewin").onclick = function () {
		closeWin()
		set(document.getElementById("update"), "<font color='red'>版本暂未升级...</font>");
	}
	document.getElementById("openwin").onclick = function () {
		closeWin()
		location.href = 'admin_update.php';
	}
}

function openCollectWin(width, height, str, url) {
	openWindow2(101, width, height, 50)
	var msgDiv = document.getElementById("msg")
	var bgDiv = document.getElementById("bg")
	var iWidth = document.documentElement.scrollWidth
	var str = "<div style='width:400px;'><div class='divboxtitle'><span onclick=\"closeWin();\" ><img src='../pic/btn_close.gif'/></span>seacms温馨提示</div><div  class='divboxbody'>" + str + "<br><input type='button' value='继续采集' id='openwin' class='btn'/>&nbsp;&nbsp;<input id='closewin' type='button' value='取   消' name='button' class='btn'  />&nbsp;&nbsp;<input id='clearColHis' type='button' value='取消记录' name='button' class='btn'  /></div><div class='divboxbottom'>Power By seacms</div></div>"
	msgDiv.style.cssText += "FONT-SIZE: 12px;top:100px;left:" + (iWidth - width) / 2 + "px;text-align:center;";
	set(msgDiv, str)
	document.getElementById("closewin").onclick = function () {
		closeWin()
	}
	document.getElementById("clearColHis").onclick = function () {
		clearColHis()
		closeWin()
	}
	document.getElementById("openwin").onclick = function () {
		closeWin()
		location.href = url;
	}
}

function clearColHis() {
	ajax.get(
		"admin_ajax.php?action=clearColHis",
		function (obj) {
		}
	);
}

function checkNewVersionCallBack(obj) {

	if (obj.responseText == "False") {
		set(document.getElementById("update"), "<font color='red'>&nbsp;&nbsp;当前已是最新版本!</font>");
	} else {
		openUpdateWin(400, "auto", obj.responseText)
	}
}

function checkNewVersion() {
	set(document.getElementById("update"), "<font color='red'>&nbsp;&nbsp;请稍等，正在检测新版本...</font>");
	var getUrl = "admin_update.php?action=isNew";
	ajax.get(
		getUrl,
		checkNewVersionCallBack
	);
}

function ajaxFunction(url) {
	set(document.getElementById("wait"), "<font color='red'>目标网站正在采集中，请稍后.....</font>");
	location.href = url;
}




function checkRepeat() {
	ajax.get(
		"admin_ajax.php?action=checkrepeat&v_name=" + encodeURI(document.getElementById('v_name').value),
		function (obj) {
			if (obj.responseText == "ok") { set(document.getElementById("v_name_ok"), "<img src='img/yes.gif' border=0></img>"); } else { set(document.getElementById("v_name_ok"), "<img src='img/no.gif' border=0></img>"); }
		}
	);
}

function setVideoTopic(videoId, topicId) {
	openWindow2(101, 230, 20, 0)
	var msgDiv = document.getElementById("msg")
	var topicTDObj = document.getElementById("topic" + videoId);
	var topicTDTop = topicTDObj.offsetTop;
	var topicTDLeft = topicTDObj.offsetLeft;
	while (topicTDObj = topicTDObj.offsetParent) { topicTDTop += topicTDObj.offsetTop; topicTDLeft += topicTDObj.offsetLeft; }
	msgDiv.style.cssText += "border:1px solid #55BBFF;background: #C1E7FF;padding:3px 0px 3px 4px;"
	msgDiv.style.top = (topicTDTop - 1) + "px";
	msgDiv.style.left = (topicTDLeft - 1) + "px";
	msgDiv.innerHTML = "正在加载内容....";
	ajax.get(
		"admin_ajax.php?id=" + videoId + "&action=select&topicid=" + topicId,
		function (obj) {
			msgDiv.innerHTML = obj.responseText;
		}
	);
}

function setVideoState(videoId) {
	openWindow2(101, 250, 20, 0)
	var msgDiv = document.getElementById("msg")
	var topicTDObj = document.getElementById("state" + videoId);
	var topicTDTop = topicTDObj.offsetTop;
	var topicTDLeft = topicTDObj.offsetLeft;
	while (topicTDObj = topicTDObj.offsetParent) { topicTDTop += topicTDObj.offsetTop; topicTDLeft += topicTDObj.offsetLeft; }
	msgDiv.style.cssText += "border:1px solid #55BBFF;background: #C1E7FF;padding:3px 0px 3px 4px;"
	msgDiv.style.top = (topicTDTop - 1) + "px";
	msgDiv.style.left = (topicTDLeft - 1) + "px";
	msgDiv.innerHTML = "状态：连载到第<input type='text' size='5' id='series' name='series'>集<input type='button' value='确定' onclick='submitVideoState(" + videoId + ")' class='rb1' /><input type='button' value='取消' onclick='closeWin()' class='rb1' />";
}

function submitVideoTopic(videoId) {
	var topic = document.getElementById("topicselect").value
	if (topic.length == 0) {
		alert('请选择专题')
		return false
	}
	ajax.get(
		"admin_ajax.php?id=" + videoId + "&topic=" + topic + "&action=submittopic",
		function (obj) {
			if (obj.responseText == "submitok") {
				set(document.getElementById("topic" + videoId), "<font color='red'>" + document.getElementById("topicselect").options[document.getElementById("topicselect").selectedIndex].text + "</font>");
				closeWin();
			} else {
				set(document.getElementById("topic" + videoId), "<font color='red'>发生错误</font>");
			}
		}
	);
}

function submitVideoState(videoId) {
	var state = document.getElementById("series").value
	if (isNaN(state)) {
		alert('集数为数字')
		return false
	} else if (state == 'undefined' || state == '') {
		alert('集数不能为空')
		return false
	}
	ajax.get(
		"admin_ajax.php?id=" + videoId + "&state=" + state + "&action=submitstate",
		function (obj) {
			if (obj.responseText == "submitok") {
				set(document.getElementById("state" + videoId), "<font color='red'>(连载到" + state + "集)</font>");
				closeWin();
			} else {
				set(document.getElementById("state" + videoId), "<font color='red'>发生错误</font>");
			}
		}
	);
}

function selectPicLink(selectObj, str) {
	var selectValue = selectObj.options[selectObj.selectedIndex].value
	if (selectValue == str)
		document.getElementById("tr_v_pic").style.display = ""
	else
		document.getElementById("tr_v_pic").style.display = "none"
}

function openAdWin(divid, path, url) {
	document.getElementById(divid).style.display = "block";
	selfLabelWindefault(divid);
	document.getElementById("adpath").value = '<script type=\"text/javascript\" language=\"javascript" src=\"' + url + path.replace("../", "") + '\"></script>';
}

function openHtmlToJsWin(divid) {
	document.getElementById(divid).style.display = "block";
	selfLabelWindefault(divid);
}

function insertHtmlToJsWin(divid, divid2, divid3) {
	hide(divid);
	document.getElementById(divid2).value = document.getElementById(divid3).value
}

function openSelfLabelWin(divid, id) {
	document.getElementById(divid).style.display = "block";
	selfLabelWindefault(divid);
	set(document.getElementById("labelcontent"), "代码加载中...");
	ajax.get(
		"admin_ajax.php?id=" + id + "&action=getselflabel",
		function (obj) {
			if (obj.responseText == "err") {
				set(document.getElementById("labelcontent"), "发生错误");
			} else {
				set(document.getElementById("labelcontent"), obj.responseText);
			}
		}
	);
}

function selfLabelWindefault(divid) {
	document.getElementById(divid).style.left = (document.documentElement.clientWidth - 568) / 2 + "px"
	document.getElementById(divid).style.top = (getScroll() + 60) + "px"
}

function viewCurrentAdTr(id) {
	var adtrObj = getElementsByName("tr", "adtr")
	var n = adtrObj.length
	for (var i = 0; i <= n - 1; i++) {
		adtrObj[i].className = "";
	}
	document.getElementById("adtr" + id).className = "editlast";
}

function isExistUsername(id) {
	var username = document.getElementById("username").value
	if (username.length == 0) {
		set(document.getElementById("checkmanagername"), "管理员名称不能为空");
		return false;
	}
	ajax.get(
		"admin_ajax.php?username=" + username + "&action=checkuser&id=" + id,
		function (obj) {
			var value = obj.responseText
			if (value == "no") {
				set(document.getElementById("checkmanagername"), "发生错误");
			} else {
				if (value == "1")
					set(document.getElementById("checkmanagername"), "已经存在此管理员，请更换名称");
				else if (value == "0")
					set(document.getElementById("checkmanagername"), "恭喜，该用户名可用");
			}
		}
	);
}

function starView(level, vid, type) {
	var i, j, htmlStr;
	var htmlStr = ""
	if (level == 0) { level = 0 }
	//if (level > 0) { htmlStr += "<img src='img/starno.gif' border='0' style='cursor:pointer;margin-left:2px;' title='取消推荐'  onclick='commendVideo(" + vid + ",0," + type + ")'/>" }
	if (level > 0) { htmlStr += "<i class='iconfont' border='0' style='color:Orange;cursor:pointer;margin-left:2px;' title='取消推荐'  onclick='commendVideo(" + vid + ",0," + type + ")'>&#xe69d;</i>" }
	for (i = 1; i <= level; i++) {
		htmlStr += "<i class='iconfont' border='0' style='color:red;cursor:pointer;margin-left:2px;' onclick='commendVideo(" + vid + "," + i + "," + type + ")' title='推荐为" + i + "星级' id='star" + vid + "_" + i + "'>&#xe717;</i>"
	}
	for (j = level + 1; j <= 5; j++) {
		htmlStr += "<i class='iconfont' border='0' style='color:gray;cursor:pointer;margin-left:2px;' onclick='commendVideo(" + vid + "," + j + "," + type + ")' title='推荐为" + j + "星级' id='star" + vid + "_" + j + "'>&#xe717;</i>"
	}
	set(document.getElementById('star' + vid), htmlStr)
}

function commendVideo(vid, commendid, type) {

	ajax.get(
		"admin_ajax.php?id=" + vid + "&commendid=" + commendid + "&type=" + type + "&action=commend",
		function (obj) {
			if (obj.responseText == "submitok") {
				starView(commendid, vid, type);
			} else {
				set(document.getElementById("star" + vid), "<font color='red'>发生错误</font>");
			}
		}
	);
}

function viewCurrentTopicTr(id) {
	var topictrObj = getElementsByName("tr", "topictr")
	var n = topictrObj.length
	for (var i = 0; i <= n - 1; i++) {
		topictrObj[i].style.background = "#ffffff";
	}
	document.getElementById("topictr" + id).style.background = "#E7E7E7";
}

function openTopicDesWin(divid, id) {
	selfLabelWindefault(divid);
	view(divid)
	document.getElementById("f_id").value = id
	set(document.getElementById("f_des"), "加载中...");
	ajax.get(
		"admin_ajax.php?id=" + id + "&action=gettopicdes",
		function (obj) {
			if (obj.responseText == "err") {
				set(document.getElementById("f_des"), "发生错误");
			} else {
				set(document.getElementById("f_des"), obj.responseText);
			}
		}
	);
}

function submitTopicDes(divid) {
	ajax.postf(
		document.getElementById("formdes"),
		function (obj) { if (obj.responseText == "ok") { hide(divid); alert('修改成功'); } else { alert('发生错误'); } }
	);
}

function gather() {
	var url = document.getElementById("gatherurl").value
	if (url.length == 0)
		return false
	else {
		view("loading")
		ajax.get(
			"admin_webgather.php?url=" + url + "&action=gather",
			function (obj) {
				if (obj.responseText == "err") {
					document.getElementById("gathercontent").value = "发生错误";
				} else {
					document.getElementById("gathercontent").value = obj.responseText;
				}
				hide("loading")
			}
		);
	}
}

function insertResult(i) {
	var id = document.getElementById("areaid").value
	document.getElementById("v_playurl" + id).value += (document.getElementById("v_playurl" + id).value != '' ? "\n" : '') + document.getElementById("gathercontent").value
	document.getElementById("gatherurl").value = ''
	document.getElementById("gathercontent").value = ''
	document.getElementById("areaid").value = i;
	hide('gathervideo')
}

function loadXML(xmlFile) {
	var xmlDoc;
	try //Internet Explorer
	{
		xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async = false;
		xmlDoc.load(xmlFile);
	}
	catch (e) {
		try //Firefox, Mozilla, Opera
		{
			xmlDoc = document.implementation.createDocument("", "", null);
			xmlDoc.async = false;
			xmlDoc.load(xmlFile);
		}
		catch (e) {
			try //Google Chrome
			{
				var xmlhttp = new window.XMLHttpRequest();
				xmlhttp.open("GET", xmlFile, false);
				xmlhttp.send(null);
				xmlDoc = xmlhttp.responseXML;
			}
			catch (e) {
				error = e.message;
			}
		}
	}
	return xmlDoc;
	/*var xmlDoc;
	if(window.ActiveXObject)
	{
		xmlDoc = new ActiveXObject('Microsoft.XMLDOM');
		xmlDoc.async = false;
		xmlDoc.load(xmlFile);
	}
	else if (navigator.userAgent.indexOf("Firefox") > 0)
	{
		xmlDoc = document.implementation.createDocument('', '', null);
		xmlDoc.async = false;
		xmlDoc.load(xmlFile);
	}
	else
	{
		return null;
	}
	return xmlDoc;*/
}




function getReferedId(str) {
	var xml = loadXML("../data/admin/playerKinds.xml");
	var dogNodes = xml.getElementsByTagName("player");
	for (var i = 0; i < dogNodes.length; i++) {
		var _postfix = dogNodes[i].attributes[2].value;
		var _flag = dogNodes[i].attributes[3].value;
		if (str == _flag) return _postfix;
	}

/*	if(str.indexOf('新浪高清')>-1) return "hd_iask"
	if(str.indexOf('搜狐高清')>-1) return "hd_sohu"
	if(str.indexOf('天线高清')>-1) return "hd_openv"
	if(str.indexOf('56高清')>-1) return "hd_56"
	if(str.indexOf('56')>-1) return "56"
	if(str.indexOf('优酷')>-1) return "youku"
	if(str.indexOf('土豆')>-1) return "tudou"
	if(str.indexOf('搜狐')>-1) return "sohu"
	if(str.indexOf('新浪')>-1) return "iask"
	if(str.indexOf('六间房')>-1) return "6rooms"
	if(str.indexOf('qq')>-1) return "qq"
	if(str.indexOf('youtube')>-1) return "youtube"
	if(str.indexOf('17173')>-1) return "17173"
	if(str.indexOf('ku6视频')>-1) return "ku6"
	if(str.indexOf('FLV')>-1) return "flv"
	if(str.indexOf('SWF')>-1) return "swf"
	if(str.indexOf('real')>-1) return "real"
	if(str.indexOf('media')>-1) return "media"
	if(str.indexOf('qvod')>-1) return "qvod"
	if(str.indexOf('ppstream')>-1) return "pps"
	if(str.indexOf('迅播高清')>-1) return "gvod"
	if(str.indexOf('远古高清')>-1) return "wp2008"
	if(str.indexOf('播客CC')>-1) return "cc"
	if(str.indexOf('ppvod高清')>-1) return "ppvod"
	if(str.indexOf('PVOD')>-1) return "pvod"
	if(str.indexOf('海洋影音')>-1) return "ssea"
*/	return ""
}

function repairUrl(i) {
	var urlStr, urlArray, newStr, j, flagCount, fromText
	fromText = $("#v_playfrom" + i).find("option:selected").text();
	if (fromText.length == 0) { alert('请选择播放器类型'); return false; }
	urlStr = $("#v_playurl" + i).val();
	if (urlStr.length == 0) { alert('请填写地址'); return false; }
	if (navigator.userAgent.indexOf("Chrome") > 0) { urlArray = urlStr.split("\n"); }
	else if (navigator.userAgent.indexOf("Firefox") > 0) { urlArray = urlStr.split("\n"); }
	else if (navigator.userAgent.indexOf("Safari") > 0) { urlArray = urlStr.split("\n"); }
	else if (navigator.userAgent.indexOf("Opera") > 0) { urlArray = urlStr.split("\n"); }
	else if (navigator.userAgent.indexOf("rv:11") > 0) { urlArray = urlStr.split("\n"); }
	else { urlArray = urlStr.split("\r\n"); }
	newStr = "";
	for (j = 0; j < urlArray.length; j++) {
		if (urlArray[j].length > 0) {
			flagCount = urlArray[j].split('$').length - 1
			switch (flagCount) {
				case 0:
					urlArray[j] = '第' + (j + 1) + '集$' + urlArray[j] + '$' + getReferedId(fromText)
					break;
				case 1:
					urlArray[j] = urlArray[j] + '$' + getReferedId(fromText)
					break;
				case 2:
					break;
			}
			newStr += urlArray[j] + "\r\n";
		}
	}
	$('v_playurl' + i).value = trimOuterStr(newStr, "\r\n");
}


function repairUrl2(i) {
	var urlStr, urlArray, newStr, j, flagCount, fromText
	fromText = $("#m_downfrom" + i).find("option:selected").text();
	if (fromText.length == 0) { alert('请选择下载类型'); return false; }
	urlStr = $('m_downurl' + i).val();
	if (urlStr.length == 0) { alert('请填写地址'); return false; }
	if (navigator.userAgent.indexOf("Chrome") > 0) { urlArray = urlStr.split("\n"); }
	else if (navigator.userAgent.indexOf("Firefox") > 0) { urlArray = urlStr.split("\n"); }
	else if (navigator.userAgent.indexOf("Safari") > 0) { urlArray = urlStr.split("\n"); }
	else if (navigator.userAgent.indexOf("Opera") > 0) { urlArray = urlStr.split("\n"); }
	else if (navigator.userAgent.indexOf("rv:11") > 0) { urlArray = urlStr.split("\n"); }
	else { urlArray = urlStr.split("\r\n"); }
	newStr = "";
	for (j = 0; j < urlArray.length; j++) {
		if (urlArray[j].length > 0) {
			flagCount = urlArray[j].split('$').length - 1
			switch (flagCount) {
				case 0:
					urlArray[j] = '第' + (j + 1) + '集$' + urlArray[j] + '$down'
					break;
				case 1:
					urlArray[j] = urlArray[j] + '$down'
					break;
				case 2:
					break;
			}
			newStr += urlArray[j] + "\r\n";
		}
	}
	$('m_downurl' + i).value = trimOuterStr(newStr, "\r\n");
}
function expendPlayArea(i, optionStr, type) {
	if (expendPlayArea.i === false) {
		expendPlayArea.i = i
	} else {
		i = ++expendPlayArea.i;
	}
	optionStr = unescape(optionStr)
	var n = i - 1, m = i + 1
	var sparkStr = (type == 1) ? "" : ""
	var sparkStr2 = (type == 1) ? "" : ""
	var area = "<table width='100%' border='0' cellpadding='0' cellspacing='0' bgcolor='#FFFFFF' id='playfb" + i + "'><tr><td height='30' width='70' class='td_border'>播放来源" + i + "：</td><td class='td_border'><select id='v_playfrom" + i + "' name='v_playfrom[" + i + "]'><option value=''>暂无数据" + i + "</option>" + optionStr + "</select>&nbsp;&nbsp;<img onclick=\"var tb=document.getElementById('playfb" + i + "');tb.parentNode.removeChild(tb);\"  src='img/btn_dec.gif' class='pointer' alt='删除播放来源" + i + "' align='absmiddle' />&nbsp;&nbsp;<a href=\"javascript:moveTableUp(document.getElementById('playfb" + i + "'))\">上移</a>&nbsp;&nbsp;<a href=\"javascript:moveTableDown(document.getElementById('playfb" + i + "'))\">下移</a>" + sparkStr + sparkStr2 + "&nbsp;&nbsp;<div class=\"div-inline webuploader-pick\" id = \"selVideo" + i + "\">选择视频</div>  <div class=\"div-inline\" id=\"picker" + i + "\">上传视频</div> <div class=\"div-inline\" id=\"info" + i + "\"><div class=\"progress\" style=\"display: none;\"><span class=\"text\">0%</span><span class=\"percentage\" style=\"width: 0%;\"></span></div> </td></tr><tr><td  class='td_border'>数据地址" + i + "：<br/><input type='button' value='手动校正' title='一般情况下不需要手动校正，系统会自动进行校正' class='rb1'  onclick='repairUrl(" + i + ")'/></td><td align='left' class='td_border'><textarea id='v_playurl" + i + "' name='v_playurl[" + i + "]' rows='8'  style='width:695px;;height: 80px;'></textarea>" + sparkStr + "</div></td></tr></table>"
	var _nextdiv = document.createElement("div");
	_nextdiv.innerHTML = area
	document.getElementById('v_playarea').appendChild(_nextdiv.getElementsByTagName('table')[0])
	bindButtonFile('#selVideo' + i, '#v_playurl' + i, true, true);
	var up1 = new $WebUpload('#picker' + i, '#info' + i, '#v_playurl' + i);
	up1.init();
	return i;
}
expendPlayArea.i = false;

function expendDownArea(i, optionStr, type) {
	if (expendDownArea.i === false) {
		expendDownArea.i = i
	} else {
		i = ++expendDownArea.i;
	}
	optionStr = unescape(optionStr)
	var n = i - 1, m = i + 1
	var sparkStr = (type == 1) ? "" : ""
	var area = "<table width='100%' border='0' cellpadding='0' cellspacing='0' bgcolor='#FFFFFF' id='downfb" + i + "'><tr><td  height='30' width='70' >下载来源" + i + "：</td><td class='td_border'>" + sparkStr + "<select id='m_downfrom" + i + "' name='m_downfrom[" + i + "]'><option value=''>暂无数据" + i + "</option>" + optionStr + "</select>&nbsp;&nbsp;<img onclick=\"var tb=document.getElementById('downfb" + i + "');tb.parentNode.removeChild(tb);\"  src='img/btn_dec.gif' class='pointer' alt='删除下载来源" + i + "' align='absmiddle' />&nbsp;&nbsp;<a href=\"javascript:moveTableUp(document.getElementById('downfb" + i + "'))\">上移</a>&nbsp;&nbsp;<a href=\"javascript:moveTableDown(document.getElementById('downfb" + i + "'))\">下移</a>"+ "&nbsp;&nbsp;<div class=\"div-inline webuploader-pick\" id = \"selDown" + i + "\">选择视频</div>  <div class=\"div-inline\" id=\"d_picker" + i + "\">上传视频</div> <div class=\"div-inline\" id=\"d_info" + i + "\"><div class=\"progress\" style=\"display: none;\"><span class=\"text\">0%</span><span class=\"percentage\" style=\"width: 0%;\"></span></div</td></tr><tr><td  class='td_border'>下载地址" + i + "：<br/><input type='button' value='手动校正' title='一般情况下不需要手动校正，系统会自动进行校正' class='rb1'  onclick='repairUrl2(" + i + ")'/></td><td align='left' class='td_border'><textarea id='m_downurl" + i + "' name='m_downurl[" + i + "]' rows='8' style='width:695px;height: 80px;'></textarea></td></tr></table>";
	var _nextdiv = document.createElement("div");
	_nextdiv.innerHTML = area
	document.getElementById('m_downarea').appendChild(_nextdiv.getElementsByTagName('table')[0]);
	bindButtonFile('#selDown' + i, '#m_downurl' + i, true, true);
	var up1 = new $WebUpload('#d_picker' + i, '#d_info' + i, '#m_downurl' + i);
	up1.init();
	_nextdiv = null;
}
expendDownArea.i = false;


function expendPlayerSkin(i) {
	var str = "皮肤" + (i + 1) + " :背景颜色 #<input type='text' name='playerbgcolor'  alt='clrDlg0" + i + "' /> 文字颜色 #<input type='text' name='playerfontcolor'  alt='clrDlg1" + i + "' /><br><div id='addplayerskin" + i + "'><img  src='img/btn_add.gif' onclick='expendPlayerSkin(" + (i + 1) + ")'  style='cursor:pointer'/></div>";
	var _nextdiv = document.createElement("div")
	set(_nextdiv, str)
	document.getElementById('playerskindiv').appendChild(_nextdiv)
	if (i > 0) { hide("addplayerskin" + (i - 1)) }
}

function viewGatherWin(i) {
	document.getElementById('gathervideo').style.display = 'block'; document.getElementById("areaid").value = i; selfLabelWindefault('gathervideo');
}

function alertUpdatePic() {
	ajax.get(
		"admin_ajax.php?action=updatepic",
		function (obj) {
			if (obj.responseText > 0) {
				view('updatepic'); set(document.getElementById("updatepicnum"), obj.responseText)
			}
		}
	);
	floatBtttom("updatepic", 500);
}


function floatBtttom(id, retime) {
	document.getElementById(id).style.top = (document.documentElement.scrollTop - 0 + document.documentElement.clientHeight - document.getElementById(id).clientHeight) + "px";
	timer = setTimeout("floatBtttom('" + id + "'," + retime + ");", retime);
}

function isViewState() {
	if (document.getElementById("v_statebox").checked) { document.getElementById("v_statespan").style.display = "inline"; } else { hide("v_statespan"); document.getElementById("v_state").value = ''; }
}

function isViewClass() {
	if (document.getElementById("v_classbox").checked) { document.getElementById("v_classspan").style.display = "inline"; } else { hide("v_classspan"); }
}

function htmlToJs(htmlinput, jsinput) {
	if (document.all) {
		document.getElementById(jsinput).value = "document.writeln(\"" + document.getElementById(htmlinput).value.replace(/\\/g, "\\\\").replace(/\//g, "\\/").replace(/\'/g, "\\\'").replace(/\"/g, "\\\"").split('\r\n').join("\");\ndocument.writeln(\"") + "\")";
	}
	else {
		document.getElementById(jsinput).value = "document.writeln(\"" + document.getElementById(htmlinput).value.replace(/\\/g, "\\\\").replace(/\//g, "\\/").replace(/\'/g, "\\\'").replace(/\"/g, "\\\"").split('\n').join("\");\ndocument.writeln(\"") + "\")";
	}
}

function jstohtml(jsinput, htmlinput) {
	document.getElementById(htmlinput).value = document.getElementById(jsinput).value.replace(/document.writeln\("/g, "").replace(/document.write\("/g, "").replace(/"\);/g, "").replace(/\\\"/g, "\"").replace(/\\\'/g, "\'").replace(/\\\//g, "\/").replace(/\\\\/g, "\\").replace(/document.writeln\('/g, "").replace(/"\)/g, "").replace(/'\);/g, "").replace(/'\)/g, "");
}

function trimOuterStr(str, outerstr) {
	var len1
	len1 = outerstr.length;
	if (str.substr(0, len1) == outerstr) { str = str.substr(len1) }
	if (str.substr(str.length - len1) == outerstr) { str = str.substr(0, str.length - len1) }
	return str
}

function reverseOrder() {
	if (document.getElementById('gathercontent').value == "") { alert("没有地址内容"); return; }
	if (navigator.userAgent.indexOf("Firefox") > 0) { var listArray = document.getElementById('gathercontent').value.split("\n"); } else { var listArray = document.getElementById('gathercontent').value.split("\r\n"); }
	var newStr = "";
	for (var i = listArray.length - 1; i >= 0; i--) {
		newStr += listArray[i] + "\r\n";
	}
	document.getElementById('gathercontent').value = trimOuterStr(newStr, "\r\n");
}

function replaceStr() {
	var contentObj = document.getElementById('gathercontent'), str = "gi";
	if (contentObj.value == "") { alert("没有地址内容"); return; }
	var replace1 = document.getElementById("replace1").value, replace2 = document.getElementById("replace2").value;
	var content = contentObj.value;
	var reg = new RegExp(replace1, str);
	contentObj.value = content.replace(reg, replace2);
}

function viewComMakeOps() { hide('tr_makenews_all'); hide('tr_makenews_type'); hide('tr_makenews_content'); view('tr_make_all'); view('tr_make_type'); view('tr_make_content'); hide('tr_make_zt'); hide('tr_make_self'); document.getElementById("makeHtmlTab").getElementsByTagName("li")[0].className = "hover"; document.getElementById("makeHtmlTab").getElementsByTagName("li")[1].className = ""; document.getElementById("makeHtmlTab").getElementsByTagName("li")[2].className = ""; }

function viewNewsMakeOps() { view('tr_makenews_all'); view('tr_makenews_type'); view('tr_makenews_content'); hide('tr_make_all'); hide('tr_make_type'); hide('tr_make_content'); hide('tr_make_zt'); hide('tr_make_self'); document.getElementById("makeHtmlTab").getElementsByTagName("li")[1].className = "hover"; document.getElementById("makeHtmlTab").getElementsByTagName("li")[0].className = ""; document.getElementById("makeHtmlTab").getElementsByTagName("li")[2].className = ""; }

function viewSpeMakeOps() { hide('tr_make_all'); hide('tr_make_type'); hide('tr_make_content'); hide('tr_makenews_all'); hide('tr_makenews_type'); hide('tr_makenews_content'); view('tr_make_zt'); view('tr_make_self'); document.getElementById("makeHtmlTab").getElementsByTagName("li")[2].className = "hover"; document.getElementById("makeHtmlTab").getElementsByTagName("li")[0].className = ""; document.getElementById("makeHtmlTab").getElementsByTagName("li")[1].className = ""; }

function Nav() {
	if (window.navigator.userAgent.indexOf("MSIE") >= 1) return 'IE';
	else if (window.navigator.userAgent.indexOf("Firefox") >= 1) return 'FF';
	else return "OT";
}

function SelTrim(selfield) {
	var tagobj = document.getElementById(selfield);
	if (Nav() == 'IE') { var posLeft = window.event.clientX - 200; var posTop = window.event.clientY; }
	else { var posLeft = 100; var posTop = 100; }
	window.open("templets/admin_co_trimrule.html?" + selfield, "coRule", "scrollbars=no,resizable=yes,statebar=no,width=320,height=180,left=" + posLeft + ", top=" + posTop);
}

function moveTableUp(o) {
	if (!!o.previousSibling) {
		o.parentNode.insertBefore(o, o.previousSibling);
	}
}
function moveTableDown(o) {
	if (!!o.nextSibling) {
		o.parentNode.insertBefore(o.nextSibling, o);
	}
}
function view(id) {
	document.getElementById(id).style.display = '';
}
function hide(id) {
	document.getElementById(id).style.display = 'none';
}


function selecMakeMode(value) {
	if (value == 'dir1' || value == 'dir3' || value == 'dir5' || value == 'dir7' || value == 'dir9') {
		view("dir1");
		hide("dir2");
	}
	if (value == 'dir2' || value == 'dir4' || value == 'dir6' || value == 'dir8') {
		hide("dir1");
		view("dir2");
	}
}

function selecRunMode(value) {
	if (value == '1') {
		hide("static"); view("dynamic"); hide("forgedStatic"); hide('ismakeplaytr'); hide("dir1"); hide("dir2"); selectMakeplay();
	}
	if (value == '0') {
		view("static"); hide("dynamic"); hide("forgedStatic"); view('ismakeplaytr'); selecMakeMode(document.getElementById('makemode')[document.getElementById('makemode').selectedIndex].value); selectMakeplay();
	}
	if (value == '2') {
		hide("static"); view("forgedStatic"); hide("dynamic"); hide('ismakeplaytr'); hide("dir1"); hide("dir2"); selectMakeplay();
	}
}

function selecNewsMakeMode(value) {
	if (value == 'dir1' || value == 'dir3' || value == 'dir5' || value == 'dir7') {
		hide("newsdir2"); view("newsdir1");
	}
	if (value == 'dir2' || value == 'dir4' || value == 'dir6' || value == 'dir8') {
		view("newsdir2"); hide("newsdir1");
	}
}

function selecNewsRunMode(value) {
	if (value == '1') {
		hide("newsstatic"); view("newsdynamic"); hide("newsforgedStatic"); hide("newsdir2"); hide("newsdir1");
	}
	if (value == '0') {
		view("newsstatic"); hide("newsdynamic"); hide("newsforgedStatic"); selecNewsMakeMode(document.getElementById('newsmakemode')[document.getElementById('newsmakemode').selectedIndex].value);
	}
	if (value == '2') {
		hide("newsstatic"); view("newsforgedStatic"); hide("newsdynamic"); hide("newsdir2"); hide("newsdir1");
	}
}

function selectCacheSearch(v) {
	document.getElementById('CacheSearch').style.display = v == '1' ? '' : 'none';
}

function selectMakeplay() {
	return;
	var y = document.getElementById('ismakeplay'), v = y[y.selectedIndex].value, b = document.getElementById('paramset2'), r = document.getElementById('runmode'), x = r[r.selectedIndex].value;
	if (v == '3' && x == 'static') {
		var a = document.getElementById('paramset1');
		selectMakeplay.i = a.checked ? 1 : 2;
		a.checked = true; a.onclick();
		b.disabled = true;
	} else if (b.disabled) {
		if (selectMakeplay.i == 2) {
			b.checked = true; b.onclick();
		}
		b.disabled = false;
	}
}

function onTxtChange(input) {
	input.value = input.value.replace(/[^\w_]/ig, '');
}


function selectAlertWin(value) {
	if (value == 1) { view("alertwinset"); }
	if (value == 0) { hide("alertwinset"); }
}

function selectCache(value) {
	if (value == 1) { view("cacheset"); }
	if (value == 0) { hide("cacheset"); }
}

function clearCache() {
	ajax.get(
		"admin_ajax.php?action=updatecache",
		function (obj) {
			if (obj.responseText == 'ok') {
				layer.msg('缓存更新成功');
			} else {
				layer.msg('缓存更新失败');
			}
		}
	);
}

function JsonMenu(resid) {
}

function selecPlan(selecVal) {
	if (selecVal == '0') {
		view('collectItem');
		hide('autocollectItem');
		hide('autocollectItemnews');
	} else if (selecVal == '2') {
		view('autocollectItem');
		hide('collectItem');
		hide('autocollectItemnews');
	}
	else if (selecVal == '4') {
		view('autocollectItemnews');
		hide('collectItem');
		hide('autocollectItem');
	}
	else {
		hide('autocollectItem');
		hide('collectItem');
		hide('autocollectItemnews');
	}
}

function setBindType(curid, classid) {
	openWindow2(101, 280, 28, 0)
	var msgDiv = document.getElementById("msg")
	var topicTDObj = document.getElementById("bind_" + curid);
	var topicTDTop = topicTDObj.offsetTop;
	var topicTDLeft = topicTDObj.offsetLeft;
	while (topicTDObj = topicTDObj.offsetParent) { topicTDTop += topicTDObj.offsetTop; topicTDLeft += topicTDObj.offsetLeft; }
	msgDiv.style.cssText += "border:1px solid #55BBFF;background: #C1E7FF;padding:3px 0px 3px 4px;"
	msgDiv.style.top = (topicTDTop - 2) + "px";
	msgDiv.style.left = (topicTDLeft + 1) + "px";
	//msgDiv.innerHTML="正在加载内容....";	
	ajax.get(
		"admin_reslib.php?curid=" + curid + "&ac=bind&classid=" + classid,
		function (obj) {
			msgDiv.innerHTML = obj.responseText;
		}
	);
}

function submitBindType(tid, curid, oldtype) {
	/*var tid = document.getElementById("tid").value
	var v_oldtype = document.getElementById("v_oldtype").value
	var curid = document.getElementById("curid").value*/
	ajax.get(
		"admin_reslib.php?ac=bindsubmit&curid=" + curid + "&tid=" + tid + "&v_oldtype=" + oldtype,
		function (obj) {
			if (obj.responseText == "bindok") {
				set(document.getElementById("bind_" + curid), " <b><a href='javascript:void(0)' id='bind_" + curid + "' onClick=\"setBindType('" + curid + "',0);\">已绑定</a></b>");
				hideBind();
			} else if (obj.responseText == "nobind") {
				set(document.getElementById("bind_" + curid), " <b><a href='javascript:void(0)' id='bind_" + curid + "' onClick=\"setBindType('" + curid + "',0);\"><font color='red'>未绑定</font></a></b>");
				hideBind();
			} else {
				set(document.getElementById("bind_" + curid), "<font color='red'>发生错误</font>");
			}
		}
	);
}

function hideBind() {
	document.body.removeChild(document.getElementById("msg"));
	document.body.removeChild(document.getElementById("bg"));
}

function getSelect_Value(obj_id) {
	var obj = document.getElementById(obj_id); //selectid
	var index = obj.selectedIndex; // 选中索引
	var value = obj.options[index].value; // 选中值
	return value;
}

function FormatMyTime(t){
	var s = t % 60;
	var i = parseInt((t / 60) % 60);
	var h = parseInt(t / 3600);
	str = "" + s;
	if(s<10) str = "0" + str;
	str = i + ":" + str;
	if(i<10) str = "0" + str;
	str = h + ":" + str;
	if(h<10) str = "0" + str;
	return " [" + str + "]";
}

function ShowRecTime(bShow) {
	if(bShow){
		var t = parseInt(Date.parse(new Date())/1000) - StartTime;
		var str = FormatMyTime(t)
		document.getElementById("rec_time").innerHTML = str;

	} else {
		document.getElementById("rec_time").innerHTML = "";
		
	}
}

function showRecodeDlg(vid){


	layer.open({
		type: 1 //Page层类型
		,btn:["确定","取消"]
		,title: '选择录像时长'
		,skin: 'layui-layer-prompt'
		,content: '<label><input type="radio" name="hour" value="1" checked>&nbsp;1小时</label>　<label><input type="radio" name="hour" value="2">&nbsp;2小时</label>　<label><input type="radio" name="hour" value="3">&nbsp;3小时</label>　<label><input type="radio" name="hour" value="5">&nbsp;5小时</label>　<br><br><br><label><input type="radio" name="hour" value="12">&nbsp;12小时</label>　<label><input type="radio" name="hour" value="24">&nbsp;1整天</label>　<label><input type="radio" name="hour" value="168">&nbsp;1星期</label>　<label><input type="radio" name="hour" value="8760">&nbsp;1整年</label><br><br>'
		,yes: function(index, layero){
			val = $(layero).find('input:radio:checked').val();
			layer.close(index);
			ctrlRecord(vid,1,val);
		}
	});



}



function ctrlRecord(vid,commandid,hour=1) {
	ajax.get(
		"admin_ajax.php?id=" + vid  + "&commendid=" + commandid +"&hour=" + hour + "&action=vod",
		function (obj) {
			if (obj.responseText == "record" || obj.responseText == "continue")  {
				set(document.getElementById("ctrlm"+vid), "<span class='layui-btn layui-btn-xs layui-btn-radius layui-btn-danger' title='点击停止录制后可以到预约列表中发布' onclick='ctrlRecord("+vid+",0);'>停止录制</span>");
				set(document.getElementById("stat"+vid), "<font color='red'>正在录制</font>");
				document.getElementById("title"+vid).setAttribute("class","text_red"); 
				StartTime = parseInt(Date.parse(new Date())/1000);
				ShowRecTime(true);
				Interval=window.setInterval('ShowRecTime(true)',1000);
				if (obj.responseText == "record" ){
					if(hour < 24)
						msg1 = hour + "小时";
					else if(hour < 720)
						msg1 = parseInt(hour/24) + "天"
					else if(hour < 8760)
						msg1 = parseInt(hour/720) + "个月"
					else
						msg1 = parseInt(hour/8760) + "年"
					$msg = msg1 + '后自动结束录制，您还可以到“管理预约”编辑自动结束时间。';
				}else {
					$msg = '在预约中找到一个正在录制中的项目，继续该项的录制。';
				}
			}else if (obj.responseText == "stop") {
				set(document.getElementById("ctrlm"+vid), "<span  class='layui-btn layui-btn-xs layui-btn-radius' title='点击开始录制并自动加入预约列表' onclick='showRecodeDlg("+vid+");'>开始录制</span>");
				set(document.getElementById("stat"+vid), "<font color='green'>正在直播</font>");
				document.getElementById("title"+vid).setAttribute("class","text_green"); 
				ShowRecTime(false);
				window.clearInterval(Interval)
				$msg = '录制结束，您可以到“管理预约”中查看录制的项目，并进行下一步处理。';
			}
			else {
				$msg = obj.responseText;
			}
			layer.msg($msg, {
				icon: 1,
				time: 6000
			}, function(){
			}); 
		}
	);
}

function publish_vod(vid) {
	layer.confirm('首页发布后，用户就可以在网站前台直接观看直播视频，<br>确定要将该直播视频流发布到网站前台首页吗？', {
		btn: ['确定','取消'] //按钮
		,title:'首页发布'
		,icon: 3
	  }, function(){
		ajax.get(
			"admin_ajax.php?id=" + vid  + "&action=publish",
			function (obj) {
				layer.msg(obj.responseText, {
					icon: 1,
					time: 6000
				  }, function(){
				  }); 
			}
		);
	  }, function(){
	  });

}

function runVodTask(){
	ajax.get(
		"../vod.php?admin=1",
		function (obj) {
			if (obj.responseText != "") {
				t1 = Number(obj.responseText)*1000;
				 window.setTimeout(runVodTask,t1);
			}
		}
	);
}

function runStatTask(){
	ajax.get(
		"admin_ajax.php?&action=get_stat",
		function (obj) {
			strs=obj.responseText.split(";");
			if(strs.length==2){
				yy = parseInt(strs[0]);
				vod = parseInt(strs[1]);
				yy_tip = document.getElementById("yy_tip");
				vod_tip = document.getElementById("vod_tip");
				yy_tip.style.display=yy>0?"inline-block":"none";
				vod_tip.style.display=vod>0?"inline-block":"none";
				yy_tip.innerHTML = yy;
				vod_tip.innerHTML = vod;
			}
				window.setTimeout(runStatTask,5000);
		}
	);
}

function getLiveStat(id_group,stat_group){
	idgroup = document.getElementById(id_group).innerHTML;
	stats =  document.getElementById(stat_group).innerHTML;
	if(idgroup==="") return;
	ajax.get(
		"admin_ajax.php?idgroup=" + idgroup + "&stats=" + stats +  "&action=live_main",
		function (obj) {
			if(obj.responseText!=""){
				strs=obj.responseText.split("{;}");
				var new_id_group = "";
				var new_stat_group = "";
				var len = strs.length;
				var hasChanged = false;
				for(var i=0;i<len;i++){
					subs = strs[i].split("{|}");
					if(subs.length==5){
						if(subs[1]!=subs[2]){
							set(document.getElementById("stat"+subs[0]),  subs[3]);
							node = document.getElementById("action"+subs[0]);
							node.innerHTML = subs[4];
							hasChanged = true;
						}
						if(subs[2]!=5){
							if(new_id_group!=="")new_id_group += ",";
							if(new_stat_group!=="")new_stat_group += ",";
							new_id_group += subs[0];
							new_stat_group += subs[2];
						} 
					}
				}
				if(hasChanged){
					set(document.getElementById("id_group"),new_id_group);
					set(document.getElementById("stat_group"),new_stat_group);				
				}
			}
		}
	);	
}

function getVodStat(id_group,stat_group,page){
	if(page<2){
		var topstyle = window.parent.document.getElementById('vod_tip').style.display;
		if((topstyle!="none" && page==0) || (topstyle=="none" && page==1)){
			window.location.reload();
			return;
		}	
	}
	idgroup = document.getElementById(id_group).innerHTML;
	stats =  document.getElementById(stat_group).innerHTML;
	if(id_group==="") return;

	ajax.get(
		"admin_ajax.php?idgroup=" + idgroup + "&stats=" + stats +  "&action=vod_list",
		function (obj) {
			if(obj.responseText!=""){
				strs=obj.responseText.split(";");
				var new_id_group = "";
				var new_stat_group = "";
				var len = strs.length;
				var hasChanged = false;
				for(var i=0;i<len;i++){
					subs = strs[i].split(",");
					if(subs.length==3){
						if(subs[1]!=subs[2]){
							hasChanged = true;

							if(page==0 && subs[2]!=0){//有直接出现时，从刷新界面
								document.location.reload();
								return;
							}else if(page==1 && subs[1]==0 && subs[2]!=0){
								document.location.reload();
							}
							
							var stat=new Array("<font color='gray'>无信号</font>","<font color='green'>正在直播</font>","<font color='red'>正在录制</font>");
							var title=new Array("text_gray","text_green","text_red");
							var action = new Array("<a href='?action=del&id="+subs[0]+"' onClick='return confirm(\"确定要删除该直播视频流吗？\")'>删除</a>","<span id='ctrlm"+subs[0]+"'><span  class='layui-btn layui-btn-xs layui-btn-radius' title='点击开始录制并自动加入预约列表' onclick='showRecodeDlg("+subs[0]+");'>开始录制</span></span>","<span id='ctrlm"+subs[0]+"'><span class='layui-btn layui-btn-xs layui-btn-radius layui-btn-danger' title='点击停止录制后可以到预约列表中发布' onclick='ctrlRecord("+subs[0]+",0);'>停止录制</span></span>")

							set(document.getElementById("stat"+subs[0]),  stat[subs[2]]);
							set(document.getElementById("action"+subs[0]),  action[subs[2]]);
							document.getElementById("title"+subs[0]).setAttribute("class", title[subs[2]]);
							if(page==2){
								if(subs[2]>0){
									var el = document.getElementById("title"+subs[0]);
									el.href = "admin_vod.php?playid=" + subs[0];
									el.title = "点击查看直播";
								}
								else{
									var el = document.getElementById("title"+subs[0]);
									el.removeAttribute("href");
									el.removeAttribute("title");
								}
							}							

							if(page==1){//查看直播界面
								if(subs[2]==2){
									StartTime = parseInt(Date.parse(new Date())/1000);
									ShowRecTime(true);
									Interval=window.setInterval('ShowRecTime(true)',1000);
								}else{
									ShowRecTime(false);
									if(Interval!=0) window.clearInterval(Interval);							
								}
							}


						}
						if(new_id_group!=="")new_id_group += ",";
						if(new_stat_group!=="")new_stat_group += ",";
						new_id_group += subs[0];
						new_stat_group += subs[2];
					}
				}
				if(hasChanged){
					set(document.getElementById("id_group"),new_id_group);
					set(document.getElementById("stat_group"),new_stat_group);
				}
			}
		}
	);	
}

function getstarttime(t1,idname){

	hour = Math.floor(player.currentTime / 3600);
	t1 = t1 - hour * 3600;
	minute = Math.floor(t1 / 60);
	t1 = t1 - minute*60;
	t1 = t1.toFixed(2);
	text = ""+pad(hour)+":"+pad(minute)+":"+pad(t1);
	document.getElementById(idname).value=text;
}


function runCutTask(filename,stime,etime,mode,id){
	var ll  = layer.open({
		type: 1,
		title: false,
		closeBtn: 0,
		shadeClose: false,
		content: '<div class="wait"><img src="img/wait.gif"><p><br>裁剪中，请等待...</p></div>'
	});	
	ajax.get(
		"admin_ajax.php?filename=" + filename + "&stime=" + stime + "&etime=" + etime +"&mode=" + mode+ "&id="+ id + "&action=cut",
		function (obj) {
			layer.close(ll);
			if (obj.responseText != "") {
				if(mode==1){	
					layer.msg(obj.responseText);
					player.once('play',function(){
						getstarttime(0,'v_stime');
						getstarttime(player.duration,'v_etime');
					});
					player.src = player.src+'8';
					document.getElementById('waiting').style.display = "none";					
				}else{
					if(obj.responseText.indexOf('/')>=0){
						layer.open({
							type: 1,
							title: '截图成功，已经设置为封面，您还可以对封面图片进行裁剪',
							btn: ['裁剪并退出', '退出'],
							area: ['650px', '480px'],
							content: '<div><img id="cropperImg" src="' + obj.responseText +'?'+ Math.random() + '" width="100%" height="100%"/></div>',
							yes: function(index){
								layer.close(index);
								src_data = cropper.getData();
								src_data.x = Math.round(src_data.x);
								src_data.y = Math.round(src_data.y);
								src_data.width = Math.round(src_data.width);
								src_data.height = Math.round(src_data.height);
								runCutPicTask(obj.responseText,src_data.x,src_data.y,src_data.width,src_data.height);
							  }
						});
						var image = document.querySelector('#cropperImg');
						var cropper = new Cropper(image, {
							  dragMode: 'crop',
							  scalable:false,
							  zoomable:false,
							  zoomOnTouch:false,
							  zoomOnWheel:false,
						  });
					}else{
						layer.msg(obj.responseText);
					}
				}
			}
		}
	);
}

function runCutPicTask(filename,x,y,width,height){
	ajax.get(
		"admin_ajax.php?filename=" + filename + "&x=" + x + "&y=" + y +"&width=" + width+ "&height="+ height + "&action=cut_pic",
		function (obj) {
			if (obj.responseText != "") {
				layer.msg(obj.responseText);
			}
		}
	);
}

function switchQRcode(vid,isopen){
	ajax.get(
		"admin_ajax.php?vid=" + vid + "&isopen="+ isopen + "&action=qrcode",
		function (obj) {
			if (obj.responseText != "") {
				subs = obj.responseText.split("{|}");
				if(subs[0]=='0'){
					layer.msg(subs[1]);
				}
				else{
					layer.open({
						title: '预约二维码',
						offset: '50px',
						content: "<div id='qrcode' style='width:256px;' ></div><div style='text-align:center'><br>"+subs[1]+"</div>"
					});

					var qrcode = new QRCode('qrcode', {
						text: subs[2],
						width: 256,
						height: 256,
						colorDark : "#000000",
						colorLight : "#ffffff",
						correctLevel : QRCode.CorrectLevel.H
					});						
				}
			}
		}
	);
}

function runNickName(nickname){
	ajax.get(
		"admin_ajax.php?nickname=" + nickname  + "&action=nickname",
		function (obj) {
			if (obj.responseText != "err") {
				layer.msg(obj.responseText);
				set(document.getElementById("nickname"),nickname);
				
			}
		}
	);
}

function runRePassword(old_password,new_password){
	ajax.get(
		"admin_ajax.php?old_pass=" + old_password  + "&new_pass=" + new_password +  "&action=repassword",
		function (obj) {
			layer.msg(obj.responseText);
		}
	);
}

function flv2mp4(id,pid){
	var ll  = layer.open({
		type: 1,
		title: false,
		closeBtn: 0,
		shadeClose: false,
		content: '<div class="wait"><img src="img/wait.gif"><p><br>正在转转码中，请等待...</p></div>'
	});
	ajax.get(
		"admin_ajax.php?sid=" + id + "&pid=" + pid + "&action=flv2mp4",
		function (obj) {
			layer.close(ll);
			layer.msg(obj.responseText);
			if(obj.responseText=="转码成功。"){
				var htmls = "<a href='admin_vod.php?action=cut&id="+id+"' title='对视频进行播放与裁剪操作'><font color='green'>播放</font></a>";
				set(document.getElementById("herf"+id),htmls);
			}
		}
	);
}
