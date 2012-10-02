/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
$.fn.DragAndDrop=function(opts)
{
	opts=$.extend(
		{
			items:"li",//������, ������� ����� �������
			move:false,//������, �� ������� ��������� ������ (���� ������ - ������� �� ���� ������)
			exclude:"input,textarea,a[href],select",//���� move - ������, ��� ����� �� ���� �������� ������ items, �������� �� ����� �����������,
			replace:"<li>",//������ ����� ������, ���� ����� �������� ��������� �����
			clean:false,//����, ��� �������� �������� ������ �� ������ ������ ��������������, � ��������: ������� � �������� ���� �����������
			alpha:0.5,//������������ ��� �����������
			//����� ��� �������������� �� ������ ������ � ������
			between:false,//���� ��������� ����� �����������.
			empty:"<li>",//������, ������� ����� � ������ � ��� ������, ���� �� ������ ����� ������� ��� �������� ������
			//Callbacks
			OnEnd:function(obj){}
		},
		opts
	);
	var th=this,
		lists,
		inmove=false,
		idr,
		MoveDown=function(e,el,i,l)
		{
				return;
			e.preventDefault();
			if(inmove)
				return false;
			inmove=true;
			idr=i;
			var r=$(opts.replace).height(el.height()).width(el.width()),
				mex=e.pageX-el.offset().left,
				mey=e.pageY-el.offset().top,
				dw=$(document).width(),
				dh=$(document).height(),
				DocMove=function(e){
					var left=e.pageX-mex,
						top=e.pageY-mey,
						elw=el.width(),
						elh=el.height();
					if(left+elw>=dw)
						left=dw-elw;
					if(top+elh>=dh)
						top=dh-elh;
					r.height(elh).width(elw);
					el.css({
						left:left>0 ? left : 0,
						top:top>0 ? top : 0
					});
					$.each(lists,function(li,lv){
								return;
							var pos=iv.o.offset();
							if(pos.left<e.pageX && pos.top<e.pageY && (pos.left+iv.o.width())>e.pageX && (pos.top+iv.o.height())>e.pageY)
							{
								{
									{
											return;
										do
										{
												idr--;
											else
												idr=parseInt(idr);
											var tmp=$(opts.replace).hide();
											tmp.insertBefore(lists[li][idr].o);
											lists[li][idr].o.insertBefore(r);
											r.replaceAll(tmp);
										}while(ii<idr)
										idr-=0.5;
									}
									else if(ii>idr)
									{
										if(iv.o.height()-e.pageY+pos.top>elh)
											return;
										do
										{
											var tmp=$(opts.replace).hide();
											tmp.insertAfter(lists[li][idr].o);
											lists[li][idr].o.insertAfter(r);
											r.replaceAll(tmp);
										}while(ii>idr);
										idr+=0.5;
									}
									br=true;
									return false;
								}
								else if(opts.between)
								{
							}
						if(br)
							return false;
					});
					return false;
				};

			el.stop(true,true).fadeTo("fast",opts.alpha).css({
				top:el.offset().top,
				position:"absolute"
			}).after(r);
			$(document).mouseup(function(e){
				e.preventDefault();
				$(this).off("mousemove",DocMove).off(e);
				el.stop(true,true).animate({
					left:r.offset().left,
					top:r.offset().top
				},200,function(){
					$(this).replaceAll(r).css({
						left:"",
						top:""
					});
					inmove=false;
					opts.OnEnd(this);
					ScanItems();
				}).fadeTo("fast",1);
			}).mousemove(DocMove);
			return false;
		ScanItems=function()
		{
			$.each(th,function(){
				$(opts.items,this).each(function(){
						i=items.length,
						l=lists.length;
					if(opts.move)
						h=h.find(opts.move);
					h.off("mousedown");
					if(opts.clean)
						return;
					h.mousedown(function(e){
					items.push({
						l:l,
						i:i
				});
				if(items.length>0)
					lists.push(items);
			});
	ScanItems();
	return this;
}