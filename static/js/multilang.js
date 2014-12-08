/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
function MultilangChecks(opts)
{
	this.opts=$.extend(
		{
			mainlang:CORE.language,
			general:$("#single-lang"),
			langs:$("input[name=\"language[]\"]"),
			where:document,
			Switch:function(show,hide,where){
				for(var i in show)
					show[i]="."+show[i];
				for(var i in hide)
					hide[i]="."+hide[i];
				$(show.join(","),where).show().filter(show[0]).trigger("switch");
				$(hide.join(","),where).hide();
			}
		},
		opts
	);

	var th=this;

	this.Click=function()
	{
		var act=[],
			deactive=[],
			main=th.opts.general ? th.opts.general.prop("checked") : false;

		th.opts.langs.each(function(){
			if(!main && this.checked)
				act.push(this.value);
			else
				deactive.push(this.value);
		});

		if(act.length==0 || act.length==1 && $.inArray(th.opts.mainlang,act)!=-1)
			th.opts.langs.filter("[value="+th.opts.mainlang+"]").prop("disabled",true).prop("checked",true);
		else
			th.opts.langs.filter("[value="+th.opts.mainlang+"]").prop("disabled",false);

		if(act.length==0)
		{
			if(!main)
				deactive.splice( $.inArray(th.opts.mainlang,deactive) ,1);

			th.opts.Switch([th.opts.mainlang],deactive,th.opts.where);
		}
		else
			th.opts.Switch(act,deactive,th.opts.where);
	};

	th.opts.langs.click(th.Click);
	th.Click();
}