/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
var votings=[];

function Voting(opts)
{
		{
			similar:false,
			AfterSwitch:function(type){},
			request:{},
			type:"",
			module:"",
			qcnt:0
		},
		opts
	);

	var types=[],
		th=this;

	{
			return;
		types[opts.type]=$(opts.form).children().detach();
			$(opts.form).html(content);
		else
			types[type].appendTo(opts.form);
		opts.AfterSwitch(type,opts.type);
		opts.type=type;
		$.each(votings[opts.similar],function(k,v){
			if(v!=th)
				v.Switch(type,content);
		})
	}

	this.Load=function(type,data)
	{
				$.extend(
					{
						language:CORE.language,
						voting:{
							data:data,
							type:type
						}
					opts.request
				),
				function(r)
				{
			);
		else
			th.Switch(type);

	if(typeof votings[opts.similar]=="undefined")
		votings[opts.similar]=[];
	votings[opts.similar].push(this);
	$(opts.form).submit(function(){
			th.Switch("vote",types["vote"]);
		else
		{
				cnt=0;
			$.each($(this).serializeArray(),function(k,v){
					cnt++;
				da[v.name]=true;
			if(opts.qcnt==cnt)
				th.Load("vote",CORE.Inputs2object($(this)));
			else
				alert(CORE.Lang("noaq"));
		return false;
}

Voting.ChecksLimit=function(container,max)
{
	var sels=0,
		bl=false,
		Bl,
		checks=$(":checkbox",container).click(function(){
			{
					return false;
			}
			else if(sels>0)
				sels--;
			Bl();
	Bl=function(){
			checks.filter(function(){
		else
			checks.prop("disabled",false);
	checks.triggerHandler("click");
}