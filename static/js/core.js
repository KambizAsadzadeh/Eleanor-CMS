/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
var uagent=navigator.userAgent.toLowerCase(),
	CORE={

	dir:"/",//Путь к сайту относительно домена

	//Индикатор загрузки
	loading:"#loading",
	ShowLoading:function()
	{
		if(CORE.loading)
			$(CORE.loading).show().trigger("show");
	},
	HideLoading:function()
	{
		if(CORE.loading)
			$(CORE.loading).hide().trigger("hide");
	},

	language:"",//Идентификатор языка сайта
	lang:[],
	Lang:function(s,a)
	{
		if(typeof s=="string")
		{
			var r=s in CORE.lang ? CORE.lang[s] : null;

			if($.isFunction(r))
				r=$.isArray(a) ? r.apply(document,a) : r();
			else if(typeof r=="string")
			{
				a=a||[];
				for(var i in a)
					r=r.split("{"+i+"}").join(a[i]);
			}

			return r;
		}
		else
		{
			a=a||"";
			for(var i in s)
				CORE.lang[a+i]=s[i];
		}
	},

	//Браузер 
	browser:
	{
		opera:uagent.indexOf("opera")!=-1,
		firefox:uagent.indexOf("firefox")!=-1
	},

	//Ajax обертка
	in_ajax:[],
	after_ajax:[],
	Ajax:function(url,data,Success,Fail,xhr)
	{
		if(typeof url!="string")
		{
			xhr=Fail;
			Fail=Success;
			Success=data;
			data=url;
			url=false;
		}

		if(typeof data=="function")
		{
			xhr=Fail;
			Fail=Success;
			Success=data;
			data={};
		}

		var ajax={
				type:"POST",
				url:url===false ? location.href : url,
				data:data,
				dataType:"json"
			},
			EventsPack={};

		if(data instanceof FormData)
		{
			ajax.processData=false;
			ajax.contentType=false;
		}

		switch(typeof Success)
		{
			case"function":
				EventsPack.OnSuccess=Success;
			break;
			case"object":
				EventsPack=Success;
		}

		if($.isFunction(Fail))
			EventsPack.OnFail=Fail;

		if(typeof xhr!="undefined")
			ajax.xhr=xhr;

		EventsPack=$.extend(
			{
				OnBegin:function(){ CORE.ShowLoading() },
				OnEnd:function(){ CORE.HideLoading() },
				OnSuccess:function(){},
				OnFail:function(s)
				{
					if($.isPlainObject(s) || $.isArray(s))
					{
						var r="";
						$.each(s,function(k,v){
							r+=$.isNumeric(k) && CORE.Lang(v) ? CORE.Lang(v) : v;
							r+="\n";
						});
						s=r;
					}

					if(s!="")
						alert(s);
				}
			},
			EventsPack
		);

		CORE.in_ajax.push(true);
		return $.ajax( $.extend(ajax,{
			beforeSend:EventsPack.OnBegin,
			success:function(r){
				CORE.AjaxHandler(r,EventsPack.OnSuccess,EventsPack.OnFail);
			},
			complete:function(jqXHR,status){
				EventsPack.OnEnd(jqXHR,status);
				CORE.in_ajax.pop();
				if(CORE.in_ajax.length==0)
				{
					var len=CORE.after_ajax.length;
					$.each(CORE.after_ajax,function(i,F){ try{F()}catch(e){} });
					CORE.after_ajax.splice(0,len);
				}
			},
			error:function(jqXHR,status,error){
				if("responseJSON" in jqXHR &&
					"error" in jqXHR.responseJSON &&
					"file" in jqXHR.responseJSON &&
					"line" in jqXHR.responseJSON)
					error=jqXHR.responseJSON.error+"\n\n"+jqXHR.responseJSON.file+"["+jqXHR.responseJSON.line+"]";
				try{EventsPack.OnFail(error,status,jqXHR)}catch(e){}
			}
		}));
	},
	QAjax:function()
	{
		var a=arguments;
		if(CORE.in_ajax.length)
			CORE.after_ajax.push(function(){
				CORE.Ajax.apply(CORE,a);
			});
		else
			CORE.Ajax.apply(CORE,a);
	},
	AjaxHandler:function(data,Success,Fail)
	{
		var MySoccess=function()
		{
			try{Success(data.data)}catch(e){}
			$.each(data.head,function(i,H){ CORE.AddHead(i,H) });
		};

		if(!data || ("error" in data) || !("data" in data))
			try{Fail(data.error ? data.error : data||"No data")}catch(e){}
		else if($.isArray(data.scripts) && data.scripts.length>0)
			CORE.AddScript(data.scripts,MySoccess);
		else
			MySoccess();
	},
	Inputs2object:function(O,ef)//Empty filler
	{
		var R={};

		if(O instanceof jQuery)
		{
			if(O.size()==0)
				return {};
			var params={};
			$.each(O.serializeArray(),function(i,n){
				params[n.name+"+"+i]=n.value;
			});
			O=params;
		}
		else if($.isEmptyObject(O))
			return {};

		$.each(O,function(k,v){
			var emp="",
				LR=R;
			k=k.replace(/\+\d+$/,"");
			$.each(k ? k.replace(/^\[|\]/g,"").split("[") : [],function(kk,vv){
				if(vv=="")
				{
					emp+="*";
					if(typeof ef!="object")
						ef={};
					if(!(emp in ef))
						ef[emp]=0;
					vv=ef[emp]++;
				}
				else
					emp+=vv+"|";

				if(typeof LR[vv]!="object")
					LR[vv]={};
				LR=LR[vv];
			});
			LR[""]=v;
		});
		CORE.NormObj(R);
		return R;
	},
	NormObj:function(O)
	{
		var i;
		for(i in O)
			if("" in O[i])
				O[i]=O[i][""];
			else if(typeof O[i]=="object")
				CORE.NormObj(O[i]);
	},

	//Cookies
	cookie:"",//Префикс cookies
	/** Установка куки
	 * @param name Имя
	 * @param value Значение
	 * @param ttl Время жизни в секундах */
	SetCookie:function(name,value,ttl)
	{
		var data=new Date();
		data.setTime(data.getTime()+(ttl ? ttl : 31536000)*1000);//1 год
		document.cookie=encodeURIComponent(CORE.cookie+name)+"="+encodeURIComponent(value)+';expires='+data.toGMTString()+";domain="
			+location.host+";path="+CORE.dir;
	},
	/** Получение куки
	 * @param name Имя куки
	 * @return mixed */
	GetCookie:function(name)
	{
		var res;
		if(res=document.cookie.match(new RegExp(encodeURIComponent(CORE.cookie+name)+"=([^;]+)","i")))
			return decodeURIComponent(res[1]);

		return false;
	},

	//Добавляем стиль
	head:[],//Дополнения в head
	AddHead:function(key,data)
	{
		var m=false;
		if(m=key.match(/^[0-9]+$/) || $.inArray(key,CORE.head)!=1)
		{
			$("head:first").append(data);
			if(!m)
				CORE.head.push(key);
		}
	},

	//Загружаемые скрипты
	scripts:[],
	AddScript:function(s,func)
	{
		if(!$.isArray(s))
			s=[s];

		var num=0,
			texts={},
			F=function(){if(s.length==num){
				$.each(s,function(i,n){
					if(texts[i])
						$.globalEval(texts[i]);
				});
				if($.isFunction(func))
					func();
			}};
		$.each(s,function(i,n){
			if(n && $.inArray(n,CORE.scripts)==-1)
			{
				if(n.indexOf("://")>0)
					$.ajax({
						url:n,
						success:function(d){
							CORE.scripts.push(n);
							texts[i]=false;
							num++;
							F();
						},
						dataType:"script",
						async:false,
						cache:true
					});
				else
					$.get(n,{},function(d){
						CORE.scripts.push(n);
						texts[i]=d;
						num++;
						F();
					},"text");
			}
			else
			{
				texts[i]=false;
				num++;
				F();
			}
		});
	},

	//Для манипуляции с историей
	history:false,
	//Opera bug :-(
	OB:function(){ if(CORE.browser.opera) with(location){ $("head base").prop("href",protocol+"//"+hostname+(port ? ":"+port : "")+CORE.dir) } },
	HistoryInit:function(F,data)
	{
		CORE.history=[];
		if($.isFunction(F))
			CORE.history.push(F);
		else
		{
			CORE.history.push(false);
			F=false;
		}
		try
		{
			history.replaceState({f:F ? CORE.history.length-1 : false,data:data||false},"",location.href);
			CORE.OB();
		}catch(e){}

		var OnPop=function(e){
			var st=e.state||false;
			if(st && st.f!==false && st.f in CORE.history)
				CORE.history[st.f](st.data);
		};
		if(window.addEventListener)
			window.addEventListener("popstate",OnPop,false);
		else
			window.attachEvent("onpopstat",OnPop);
	},
	HistoryPush:function(href,F,data)
	{
		if(history.length<CORE.history.length);
			CORE.history=CORE.history.slice(0,history.length);
		if($.isFunction(F))
			CORE.history.push(F);
		else
		{
			CORE.history.push(false);
			F=false;
		}
		try
		{
			history.pushState({f:F ? CORE.history.length-1 : false,data:data||false},"",href);
			CORE.OB();
		}catch(e){}
	},

	//MultiSite
	is_user:false,
	service:"index",
	sites:[],
	MultiSite:$.Deferred(),
	Login:function(name)
	{
		if(CORE.MultiSite.state()!="resolved" || !(name in CORE.sites) || CORE.is_user)
			return;

		CORE.ShowLoading();
		$.getJSON(CORE.sites[name].address+"?direct=multisite&type=get-login&service="+CORE.service+(CORE.sites[name].secret ? "&secret=1" : ""),function(data){
			CORE.HideLoading();
			if(data)
				CORE.Ajax(
					$.extend(
						data,
						{
							direct:"multisite",
							type:"login",
							site:name,
							service:CORE.service
						}
					),
					function(r)
					{
						if(r)
							location.reload();
					}
				);
		});
	},
	Jump:function(name)
	{
		if(!(name in CORE.sites) || !CORE.is_user)
			return false;

		CORE.Ajax(
			{
				direct:"login",
				type:"pre-jump",
				name:name,
				service:CORE.service
			},
			function(r)
			{
				var form=$("<form method=\"post\">").prop("action",r.address+"?direct=multisite&type=jump&service="+CORE.service);
				$.each(r,function(k,v){
					$("<input type=\"hidden\">").prop({
						name:k,
						value:v
					}).appendTo(form);
				});
				form.appendTo("body").submit();
			}
		);
	}
},
EDITOR=
{
	active:null,
	editors:[],

	//Вставка текста, возможно с обрамлением pre
	Insert:function(pre,after,F,id)
	{
		if(!id)
			id=this.active;

		if(id && this.editors[id])
			try
			{
				return this.editors[id].Insert(pre,after,F||0);
			}catch(e){}
	},

	//Вставка объектов
	Embed:function(type,data,id)
	{
		if(!id)
			id=this.active;

		if(id && this.editors[id])
			try
			{
				return this.editors[id].Embed(type,data,id);
			}catch(e){}
	},

	//Получение значения
	Get:function(id)
	{
		if(!id)
			id=this.active;

		if(id && this.editors[id])
			try
			{
				return this.editors[id].Get();
			}catch(e){}
	},

	//Установка значения
	Set:function(text,id)
	{
		if(!id)
			id=this.active;

		if(id && this.editors[id])
			return this.editors[id].Set(text);
	},

	//Получение выделения
	Selection:function(id)
	{
		if(!id)
			id=this.active;

		if(id && this.editors[id])
			return this.editors[id].Selection();

	},

	//Служебные функции: новый редактор
	New:function(id,cbs)
	{
		var F=function(){ return false; };
		this.editors[id]=$.extend({
				Insert:F,//pre,after,PreFunc
				Get:F,
				Set:F,//Text
				Selection:F,
				Embed:F
			},cbs);
		this.active=id;
		return true;
	},

	//Установка активного редактора
	Active:function(id)
	{
		if(id && this.editors[id])
			this.active=id;
	}
};

$(function(){
	if(CORE.sites || CORE.is_user)
	{
		var n=0,
			logined=[];

		$.each(CORE.sites,function(name,site){
			n++;
			$.getJSON(site.address+"?direct=multisite&type=check&service="+CORE.service,function(json){
				if(json)
					logined[name]=json;

				if(--n==0)
					CORE.MultiSite.resolve(logined);
			});
		});
	}
	else
		CORE.MultiSite.reject();

	CORE.dir=$("head base").attr("href");

	//Определим какие скрипты подключены
	$("head script").each(function(){
		if($(this).attr("src"))
			CORE.scripts.push(this.src.indexOf(CORE.dir)==-1 ? this.src : this.src.substr(CORE.dir.length));
	});

	//CTRL + Enter для всех форм
	$(this).on("keypress","form textarea",function(e){
		if((e.keyCode==10 || e.keyCode==13) && e.ctrlKey)
			$(this).closest("form").submit();
	})

	//Отключение пересылки на сервер полей, значение которых не менялось (класс .sic - Send If Changed)
	.on("submit","form:has(.sic)",function(){
		var sic=$(".sic",this).each(function(){
				var th=$(this);
				if(th.val()==th.prop("defaultValue"))
					th.prop("disabled",true);
			});
		setTimeout(function(){
			sic.prop("disabled",false);
		},500);
	});
});