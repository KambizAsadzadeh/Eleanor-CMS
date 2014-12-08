/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
function CheckGroup(obj)
{
	if($(obj).find(":checked").size()==0)
	{
		alert(CORE.Lang('nothing_selected'));
		return false;
	}
	return true;
}

function One2AllCheckboxes(parents,main,subnames,and)
{
	and=and||false;

	main=$(main).change(function(e,mcl){
		if(typeof mcl=="undefined")
			mcl=true;

		if(mcl)
			parents.find(subnames).prop("checked",main.prop("checked")).trigger("change",[true,false]);
	});

	parents=$(parents).on("change",subnames,function(e,mcl,scl){
		if(typeof scl=="undefined")
			scl=true;

		if(scl)
		{
			var checks=parents.find(subnames),
				checked=checks.filter(":checked").size();

			main.prop("checked",and ? checked==checks.size() : checked>0).trigger("change",[false,true]);
		}
	});

	parents.find(subnames).filter(":first").change();
}