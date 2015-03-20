/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
CORE.DRAFT=function(opts)
{
	opts=$.extend({
			url:"",//URL для сохранения
			interval:10,//Интеревал сохранения в секундах
			form:false,//Форма, которую нужно сохранять
			enabled:true,//Флаг включенности
			OnSave:false,//Событие после сохранения
			OnChange:false//Событие после изменения какого-нибудь контрола
		},
		opts
	);
	opts.url=$("<textarea>").html(opts.url).val();

	var th=this,
		to=false,
		fn=false,
		ClearTO=function(){
			if(to)
				clearInterval(to);
			to=false;
		},
		frame,oa,ot;

	this.changed=false;
	this.enabled=opts.enabled;
	this.OnSave=$.Callbacks("unique");
	this.OnChange=$.Callbacks("unique");

	if(opts.OnSave)
		this.OnSave.add(opts.OnSave);

	if(opts.OnChange)
		this.OnChange.add(opts.OnChange);

	this.Change=function()//Функция насильного уведомления о том, что содержимое изменилось
	{
		if(th.enabled)
		{
			th.changed=true;
			th.OnChange.fire();
			ClearTO();
			to=setTimeout(th.Save,opts.interval*1000);
		}
	};

	this.Save=function()//Функция насильного сохранения черновика
	{
		ClearTO();

		var f=$(opts.form);

		if(!fn)
		{
			fn="f"+(new Date().getTime());
			frame=$("<iframe>").css({position:"absolute",left:"-100px","top":"-100px"})
				.attr("name",fn).width("1px").height("1px").appendTo("html body")
				.load(function(){
					th.changed=false;
					th.OnSave.fire($(this.contentWindow.document.body).text());
				});
			oa=f.attr("action")||false;
			ot=f.attr("target")||false;
			f.submit(ClearTO);
		}

		f.attr({action:opts.url,target:fn}).submit();

		if(oa)
			f.attr({action:oa});
		else
			f.removeAttr("action");

		if(ot)
			f.attr({target:ot});
		else
			f.removeAttr("target");
	};

	opts.form.on("change input",":input",th.Change);
};