/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Based on TextAreaSelectionHelper by Sardar <Sardar@vingrad.ru>
	http://forum.vingrad.ru/forum/topic-35775.html
	http://forum.vingrad.ru/forum/topic-84449.html
*/
CORE.BBEditor=function(id,textarea,buttons)
{
	var th=this;

	this.GetSelectedText=function()
	{
		if(document.selection)
			return document.selection.createRange().text;

		var start=textarea.prop("selectionStart"),
			end=textarea.prop("selectionEnd"),
			val=textarea.val();

		//Хак для оперы :(
		if(CORE.browser.opera && val.indexOf("\r")>-1)
		{
			var cnt=0,
				left=val.substring(0,start),
				overflow=start-left.length;

			for(var i=0;i<left.length;i++)
				if(left.charCodeAt(i)==10)
				{
					cnt++;
					if(overflow>0)
						overflow--;
					else
						left=left.substr(0,left.length-1);
				}
			start-=cnt;

			left=val.substring(0,end-cnt);
			for(;i<left.length;i++)
				if(left.charCodeAt(i)==10)
				{
					cnt++;
					left=left.substr(0,left.length-1);
				}
			end-=cnt;
		}

		return val.substring(start,end);
	};

	/** scorl и scorr - Корректировка выделения */
	this.SetSelectedText=function(tag,secondtag,F,scorl,scorr)
	{
		return SetSelectedText(textarea,tag,secondtag,F,scorl,scorr);
	};

	this.GetText=function()
	{
		return textarea.val();
	};

	this.SetText=function(text)
	{
		textarea.val(text);
	};

	this.Wrap=function(tag)
	{
		this.SetSelectedText("["+tag+"]","[/"+tag+"]");
	};

	this.Paste=function(tag)
	{
		this.SetSelectedText("["+tag+"]");
	};

	this.Ol=function()
	{
		this.SetSelectedText("[ol]","[/ol]",function(t){
			return t ? "\n[*]"+t.replace(/\n/g,"\n[*]")+"\n" : "\n[*]\n[*]\n[*]\n"
		});
	};

	this.Ul=function()
	{
		this.SetSelectedText("[ul]","[/ul]",function(t){
			return t ? "\n[*]"+t.replace(/\n/g,"\n[*]")+"\n" : "\n[*]\n[*]\n[*]\n"
		});
	};

	this.Url=function()
	{
		var text=this.GetSelectedText(),
			link="http://";

		if(text.match(/^([a-z]{3,10}:\/\/[a-zа-я0-9\/\._\-:]+\.[a-z]{2,5}\/)?(?:[^\s@{}]*)?$/))
			link=text;

		link=prompt(CORE.Lang('enter_adress'),link);

		if(link==null)
			return;

		text=prompt(CORE.Lang('link_text'),text);

		if(text==null)
			return;

		this.SetSelectedText("[url="+link+"]"+text+"[/url]",null,null,("[url="+link+"]").length,-6);
	};

	this.Img=function()
	{
		var link=this.GetSelectedText();

		if(!link)
			link=prompt(CORE.Lang('enter_image_addr'),link);

		if(link==null)
			return;

		this.SetSelectedText("[img]"+link+"[/img]",null,null,5,-6);
	};

	this.Mail=function()
	{
		var link=this.GetSelectedText();

		link=prompt(CORE.Lang('enter_adress'),link);

		if(link==null)
			return;

		if(!$("<input type='email'>").val(link).get(0).checkValidity() && !confirm(CORE.Lang('WRONG_EMAIL')))
			return this.Mail();

		text=prompt(CORE.Lang('link_text'),link);

		if(text==null)
			return;

		this.SetSelectedText("[email="+link+"]"+text+"[/email]",null,null,("[email="+link+"]").length,-8);
	};

	this.Color=function(cn)
	{
		if(cn)
			this.SetSelectedText("[color="+cn+"]","[/color]");
	};

	this.BackGround=function(cn)
	{
		if(cn)
			this.SetSelectedText("[background="+cn+"]","[/background]");
	};

	this.Size=function(s)
	{
		if(s)
			this.SetSelectedText("[size="+s+"]","[/size]");
	};

	this.Font=function(s)
	{
		if(s)
			this.SetSelectedText("[font="+s+"]","[/font]");
	};

	textarea.focus(function(){EDITOR.Active(id);EDITOR.activebb=th;});

	EDITOR.New(
		id,
		{
			Embed:function(type,data)
			{
				if(type=="image" && data.src)
					th.SetSelectedText("[img]"+data.src+"[/img]");
				else if(type=="nick" && data.name)
					th.SetSelectedText("[b]"+data.name+"[/b], ");
			},
			Insert:function(pre,after,F){ th.SetSelectedText(pre,after,F); },
			Get:function(){ return textarea.val(); },
			Set:function(text){ textarea.val(text); }
		}
	);

	buttons.on("bb",function(e,bb,val){
		switch(bb)
		{
			case"bold":
				th.Wrap("b");
			break;
			case"italic":
				th.Wrap("i");
			break;
			case"underline":
				th.Wrap("u");
			break;
			case"strike":
				th.Wrap("s");
			break;
			case"url":
			case"link":
				th.Url();
			break;
			case"mail":
				th.Mail();
			break;
			case"img":
				th.Img();
			break;
			case"ul":
				th.Ul();
			break;
			case"ol":
				th.Ol();
			break;
			case"li":
				th.Paste("*");
			break;
			case"tab":
				th.SetSelectedText("\t");
			break;
			case"c":
			case"r":
			case"tm":
			case"hr":
				th.Paste(bb);
			break;
			case"color":
				th.Color(val);
			break;
			case"font":
				th.Font(val);
			break;
			case"background":
				th.BackGround(val);
			break;
			case"size":
				th.Size(val);
			break;
			default:
				th.Wrap(bb);
		}
	});
};

function SetSelectedText(textarea,tag,secondtag,F,scorl,scorr)
{
	textarea.focus();
	scorl=scorl||0;
	scorr=scorr||0;
	tag=tag||"";

	if(document.selection)
	{
		var iesel=document.selection.createRange();

		if(typeof(secondtag)=="string")
		{
			var text=$.isFunction(F) ? F(iesel.text) : iesel.text,
				l=text.replace(/\n/g,"").length;

			iesel.text=tag+text+secondtag;
			iesel.moveEnd("character",-secondtag.length);
			iesel.moveStart("character",-l);
		}
		else
			iesel.text=$.isFunction(F) ? F(text) : tag;

		iesel.select();
	}
	else if(textarea.prop("selectionStart")<=textarea.prop("selectionEnd"))
	{
		var start=textarea.prop("selectionStart"),
			end=textarea.prop("selectionEnd"),
			val=textarea.val(),
			left=val.substring(0,start),
			content,
			right,
			sctop=textarea.scrollTop(),
			scleft=textarea.scrollLeft();

		//Хак для оперы :(
		if(CORE.browser.opera && val.indexOf("\r")>-1)
		{
			var cnt=0,
				overflow=start-left.length;

			for(var i=0;i<left.length;i++)
				if(left.charCodeAt(i)==10)
				{
					cnt++;
					if(overflow>0)
						overflow--;
					else
						left=left.substr(0,left.length-1);
				}

			content=val.substring(0,end-cnt);
			for(;i<content.length;i++)
				if(content.charCodeAt(i)==10)
				{
					scorr++;
					cnt++;
					content=content.substr(0,content.length-1);
				}

			content=val.substring(left.length,end-cnt);
			right=val.substring(left.length+content.length);
		}
		else
		{
			content=val.substring(start,end);
			right=val.substring(end);
		}

		if($.isFunction(F))
			content=F(content);
		if(typeof secondtag=="string")
		{
			textarea.val(left+tag+content+secondtag+right);
			textarea.get(0).setSelectionRange(start+tag.length+scorl,start+tag.length+content.length+scorr);
		}
		else
		{
			if(typeof tag!="string")
				tag=content;
			textarea.val(left+tag+right);
			if(start!=end)
				textarea.get(0).setSelectionRange(start+scorl,start+tag.length+scorr);
			else
				textarea.get(0).setSelectionRange(start+tag.length+scorl,start+tag.length+scorr);
		}
		textarea.scrollTop(sctop).scrollLeft(scleft);
	}
	else
		textarea.get(0).value+=tag + ((typeof(secondtag)=="string") ? secondtag : "");

	textarea.change();
}

(function()
{
	var iekeys={"1":65,"2":66,"4":68,"12":76,"16":80,"19":83,"20":84,"21":85,"26":90},
		keys=
		{
			"b":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Wrap("b");
			},
			"i":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Wrap("i");
			},
			"u":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Wrap("u");
			},
			"t":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.SetSelectedText("\t");
			},
			"l":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Url();
			},
			"e":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Mail();
			},
			"I":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Img();
			},
			"S":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Wrap("s");
			},
			"L":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Wrap("lest");
			},
			"M":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Wrap("center");
			},
			"R":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Wrap("right");
			},
			"J":function()
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Wrap("justify");
			}
		};

	$(document).keypress(function(e){
		var key=e.keyCode ? e.keyCode : e.charCode;

		key=iekeys[String(key)] ? iekeys[String(key)] : key;

		if(e && e.ctrlKey)
		{
			key=e.shiftKey ? String.fromCharCode(key).toUpperCase() : String.fromCharCode(key).toLowerCase();
			if(key.match(/^[123456]$/))
			{
				if(EDITOR.activebb)
					EDITOR.activebb.Paste("h"+key);
				e.preventDefault();
				e.stopPropagation();
			}
			else if(typeof keys[key]=="function")
			{
				keys[key](e);
				e.preventDefault();
				e.stopPropagation();
			}
		}
	});

})();

EDITOR.activebb=false;