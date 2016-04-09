function show_hide_block(block_id)
{	if (document.getElementById(block_id).style.display=="none")
	{	document.getElementById(block_id).style.display="block";
		setCookie(block_id,"1");
	} else
	{	document.getElementById(block_id).style.display="none";
		setCookie(block_id,"0");
	}
}

function setCookie(name,value)
{	var eD=new Date();
	eD.setTime(eD.getTime()+1000*60*60*24*30*12);
	document.cookie=name+'='+escape(value)+'; path=; expires='+eD.toGMTString();
}

function getCookie(name)
{	var oC=document.cookie;
	if(!oC||oC=='')return '';
	oC=oC.split(';');
	var Ck;
	for (var i=0;i<oC.length;i++)
	{	Ck=oC[i].split('=')[0];
		if(Ck.charAt(0)==' ') Ck=Ck.substring(1);
		if(Ck==name) 
		{	var r=oC[i].split("=");
			if (r.length>1) return unescape(r[1]);
			else return '';
		}
	}
	return '';
}

function chat_validate()
{	if (document.chatform.your_name.value=='') return false;
	if (document.chatform.your_message.value=='') return false;
	setTimeout('chat_update();',1200);
	return true;
}

function chat_update()
{	document.chatform.your_message.value='';
	document.chatform.your_message.focus();
}

function check(frm)
{	var s='';
	if (frm.num.value.length<6) s+='Неверно введены контрольные цифры.\n';
	if (frm.disp.value.length<5||frm.disp.value.indexOf('@')<1||frm.disp.value.indexOf('.')<3) s+='Неверно записан адрес e-mail.\n';
	if (s!='')
	{	alert(s);
		return false;
	}
	return true;
}

function onpreview(text)
{	document.preview.your_message.value=text;
	win=window.open('','new_win','width=640,height=480,scrollbars=yes');
	document.preview.submit();
	win.focus();
}

function storeCaret(text) 
{if (text.createTextRange) text.caretPos=document.selection.createRange().duplicate();}

function addtag(s)
{	if (document.add_rec.your_message.createTextRange&&document.add_rec.your_message.caretPos) 
	{	var cp=document.add_rec.your_message.caretPos;      
		cp.text=s;
	}else document.add_rec.your_message.value+=s;
	if (document.getElementById("gbform").style.display=="block") document.add_rec.your_message.focus(cp);
}

function validate()
{	var s='';
	if (document.add_rec.your_name.value=='') s='Вы забыли ввести имя.\n';
	if (document.add_rec.your_message.value.length<4) s+='Вы забыли ввести сообщение.\n';
	if (document.add_rec.num.value.length<6) s+='Неверно введены контрольные цифры.\n';
	if (document.add_rec.your_email.value.length>0&&(document.add_rec.your_email.value.length<5||document.add_rec.your_email.value.indexOf('@')<1||document.add_rec.your_email.value.indexOf('.')<3)) s+='Неверно записан адрес e-mail.\n';
	if (document.add_rec.your_icq.value.length>0&&(isNaN(document.add_rec.your_icq.value)||document.add_rec.your_icq.value.length<5)) s+='Неверно заполнено поле ICQ.';
	if (s!='')
	{	alert(s);
		return false;
	}
	return true;
}

function photo_validate()
{	var s='';
	if (document.add_photo.pictur.value=='') s='Вы забыли указать адрес фотографии.\n';
	if (s!='')
	{	alert(s);
		return false;
	}
	return true;
}

function new_storeCaret(text) 
{if (text.createTextRange) text.caretPos=document.selection.createRange().duplicate();}

function new_addtag(s)
{	if (document.add_new.message.createTextRange&&document.add_new.message.caretPos) 
	{	var cp=document.add_new.message.caretPos;      
		cp.text=s;
	}else document.add_new.message.value+=s;
	document.add_new.message.focus(cp);
}

function new_validate()
{	var s='';
	if (document.add_new.message.value.length<4) s+='Вы забыли ввести сообщение.';
	if (s!='')
	{	alert(s);
		return false;
	}
	return true;
}

function checkPool(f)
{	var i;
	for (i=0;i<f.answer.length;i++)
		if (f.answer[i].checked) return true;
	return false;
}

function send_validate()
{	var s='';
	if (document.sendmail.email.value.length<5||document.sendmail.email.value.indexOf('@')<1||document.sendmail.email.value.indexOf('.')<3) s+='Неверно записан адрес e-mail.\n';
	if (document.sendmail.subj.value=='') s+='Вы забыли ввести тему письма.\n';
	if (document.sendmail.num.value.length<6) s+='Неверно введены контрольные цифры.\n';
	if (document.sendmail.body.value.length<4) s+='Вы забыли ввести сообщение.\n';
	if (s!='')
	{	alert(s);
		return false;
	}
	return true;
}