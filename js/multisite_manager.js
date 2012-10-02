/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

$(function(){
		children="table",//Имя дочерних тегов, которые являются контернерами каждого сайта

		max=form.children(children).size(),
		waitsubmit=false,
		tosubmit=0;

	form
	.children(children).each(function(){
			sites,
			F=function(){
				var empty=true;
				sites.each(function(){
					var v=$(this).is(":checkbox") ? ($(this).prop("checked") ? 1 : "") : $(this).val();
					if($.inArray(v,["",$(this).data("def")])==-1)
					{
						return false;
					}
				});
				if(empty)
					th.addClass("empty");
				else
					th.removeClass("empty");
			}
		sites=th.on("checkempty",F).find("[name^=\"sites[\"]");
		F();

	//AddSite
	.on("click",".addsite",function(){
		.find(":input").prop("name",function(ind,old){
				return old.replace(/sites\[[^\]]*\]$/,"["+max+"]");
			}).not("[type=button],[type=submit],[type=number]").val("").end()
			.prop("disabled",false).removeClass("redf greenf").end()
		.find("[id]").prop("id",function(ind,old){
			return old+"-"+max;
		}).end().appendTo(form)
		.find(".langtabs").each(function(){
			try
			{
				var actcl=false;
				$("a",this).each(function(){
					if($(this).hasClass("selected"))
						actcl=$(this);
					$(this).data("rel",$(this).data("rel")+"-"+max)
				}).Tabs();
				if(actcl)
					actcl.click();
			}
			catch(e){}
		}).end();
		max++;
		return false;

	//DeleteSite
	.on("click",".delsite",function(){
			t.remove();
		else
			t.find(".db").removeClass("redf greenf").end()
			.find(":input").not("[type=button],[type=submit],[type=number]").val("");

	//Check Db
	.on("click",".checkdb",function(){
			data={},
			dbs=$(this).closest(children).find(".db:not(:disabled,[name$=\"[host]\"][value=\"\"])").removeClass("redf greenf")
			.filter("[name$=\"[host]\"],[name$=\"[db]\"],[name$=\"[user]\"]").each(function(){
				{
					$(this).addClass("redf");
					can=false;
				}
		if(!can)
			return false;
		dbs.each(function(){
			{
				file:"multisite",
				event:"checkdb",
			},
			function(r)
			{
					switch(r)
					{
							dbs.filter("[name$=\"[host]\"],[name$=\"[user]\"],[name$=\"[pass]\"],[name$=\"[db]\"]").addClass("redf");
						break;
						case"prefix":
							dbs.filter("[name$=\"[prefix]\"]").addClass("redf");
						break;
							dbs.filter("[name$=\"[prefix]\"],[name$=\"[db]\"]").addClass("redf");
						break;
						default:
							dbs.addClass("redf");
					}
				else
				{
					if(tosubmit>0 && --tosubmit==0 && waitsubmit)
						form.submit();
				}
		);

	//Changing db fields
	.on("change",".db",function(){
			dbs=th.closest(children).find(".db").removeClass("redf greenf");
			dbs.not(this).not("[name$=\"[prefix]\"]").prop("disabled",th.val()=="")
	})
	.find("[name$=\"[host]\"]").change().end()

	//Changing secret of site
	.on("change","[name$=\"[secret]\"]",function(){
			trs=th.closest(children).find(".checkdb").closest("tr").nextAll().andSelf();
		if(th.val()=="")
			trs.show();
		else
			trs.hide();
	.find("input[name$=\"[secret]\"]").change().end()

	//Default highlight errors
	.find(children+":not(.empty) .checkdb").change().end()

	//Form submit
	.submit(function(){
		tosubmit=0;
			{
				$(this).find(".checkdb:first").click();
		waitsubmit=true;
		can=tosubmit==0;