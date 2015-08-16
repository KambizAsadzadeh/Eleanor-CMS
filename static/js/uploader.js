/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
var CancelUpload=$.Callbacks("unique");

//Если написать 'ui.bootstrap' - начинает глючить меню
angular.module("Uploader",['ui.bootstrap.pagination','ui.bootstrap.progressbar','ui.bootstrap.tpls'])
.controller("UploaderFiles",["$scope",function($scope){
	//Данные для загрузки
	$scope.max_upload=0;//"Остаток" свободного места
	$scope.max_files=0;//"Остаток" файлов
	$scope.can_upload=false;
	$scope.progress=-1;

	//Пагинатор
	$scope.total=0;
	$scope.pages=0;//Read only
	$scope.page=0;
	$scope.pp=0;

	//Содержимое панелей
	$scope.commands=[];
	$scope.pathway=[];
	$scope.dirs=[];
	$scope.files=[];
	$scope.watermark=false;
	$scope.current="";

	//Связанный с загрузчиком редактор
	$scope.editor=false;

	var init=false,
		query,
		sid,
		uniq,
		preview='',
		ExternalCreateEdit,
		Go=function(r){
			//Данные для загрузки
			$scope.max_upload=r.max_upload;//"Остаток" свободного места
			$scope.max_files=r.max_files;//"Остаток" файлов
			$scope.can_upload=r.can_upload;

			//Пагинатор
			$scope.total=r.total;
			$scope.page=r.page;

			//Содержимое панелей
			$scope.pathway=r.pathway;
			$scope.dirs=r.dirs;
			$scope.files=r.files;
			$scope.current=r.current;

			$scope.$apply();
		},
		CreateEditFile=function(r,creating,current,name){
			ExternalCreateEdit(r,function(content,After){
					creating=false;
					CORE.Ajax(query,{sid:sid,uniq:uniq,event:"save",current:current,what:name,content:content},function(r){
						if(After)
							After();
					});
				},function(){
					if(creating)
						CORE.Ajax(query,{sid:sid,uniq:uniq,event:"delete",current:current,what:name});
			});
		};

	$scope.Upload=function(files)
	{
		console.error("Was not init yet.");
	};

	$scope.Constructor=function(config)
	{
		query=config.query;
		sid=config.sid;
		uniq=config.uniq;
		preview=config.preview.join(',');
		ExternalCreateEdit=config.CreateEdit;

		var Upload=Uploader(config.query,config.max_files,config.max_uploads,function(uploaded,total){
			$scope.progress=Math.round(uploaded/total*100);
			$scope.$apply();
		},function(uploaded){
			$scope.progress=-1;

			if(uploaded)
				$scope.Go();
			else
				$scope.$apply();
		});

		$scope.Upload=function(files)
		{
			var files_;
			if(config.types.length>0)
				$.each(files,function(i,v){
					var ext=v.match(/\.([a-z0-9_]+)$/);

					if(ext && $.inArray(ext[1],config.types))
						files_.push(v);
				});
			else
				files_=files;

			if(files_.length>0)
				Upload(files_,{sid:sid,uniq:uniq,event:"upload",current:$scope.current,watermark:$scope.watermark ? 1 : 0,preview:preview});
		};

		init=true;

		$scope.commands=config.commands;
		$scope.current=config.current;
		$scope.watermark=localStorage.getItem("uploader"+config.uniq+"-watermark");
		$scope.can_upload=true;
		$scope.pp=config.pp;

		//$scope.$apply();
		$scope.Go();
	};

	$scope.Cancel=CancelUpload.fire;

	$scope.Go=function(where)
	{
		if(!init)
			return;

		where=where||"";

		if(where)
			$scope.page=1;

		CORE.Ajax(query,{sid:sid,uniq:uniq,event:"go",current:$scope.current,where:where,page:$scope.page,pp:$scope.pp,preview:preview},Go);
	};

	$scope.Delete=function(item,index)
	{
		CORE.Ajax(query,{sid:sid,uniq:uniq,event:"delete",current:$scope.current,what:item.name},function(r){
			if(r)
			{
				if("size" in item)
					$scope.files.splice(index, 1);
				else
					$scope.dirs.splice(index, 1);

				$scope.$apply();
			}
		});
	};

	$scope.Rename=function(item)
	{
		var to=prompt(CORE.Lang("input-new-name"),item.name);

		if(to && to!=item.name)
			CORE.Ajax(query,{sid:sid,uniq:uniq,event:"rename",current:$scope.current,what:item.name,to:to},function(r){
				if(r)
				{
					item.name=r;
					$scope.$apply();
				}
			});
	};

	$scope.Insert=function(item,attach,index,event)
	{
		if(!("http" in item))
			return;

		if(!event || !event.altKey)
		{
			if(!attach && item.http.match(/\.(jpe?g|bmp|gif|ico|png|webp)$/))
				EDITOR.Embed("image",{src:item.http},$scope.editor);
			else
				EDITOR.Insert(attach ? "[attach="+item.http+"]" : item.http,null,null,$scope.editor);
		}
		else
			prompt(CORE.Lang("copy-and-paste"),attach ? "[attach="+item.http+"]" : item.http);
	};

	$scope.Download=function(item)
	{
		open(CORE.dir+("http" in item ? item.http : item.download.replace(/&amp;/ig,"&")));
	};

	$scope.CreateFolder=function()
	{
		var name=prompt(CORE.Lang("input-new-name"));

		if(name)
			CORE.Ajax(query,{sid:sid,uniq:uniq,event:"create-folder",name:name,current:$scope.current,page:$scope.page,pp:$scope.pp,preview:preview},Go);
	};

	$scope.CreateFile=function()
	{
		var name=prompt(CORE.Lang("input-new-name"));

		if(name)
		{
			CORE.ShowLoading();
			$.post(query, {
				sid : sid,
				uniq : uniq,
				event : "create-file",
				name : name,
				current : $scope.current
			},function(r){ CreateEditFile(r,true,$scope.current,name); },"json").always(function(){
				CORE.HideLoading();
			});
		}
	};

	$scope.Edit=function(item)
	{
		CORE.ShowLoading();
		$.post(query,{sid:sid,uniq:uniq,event:"edit",current:$scope.current,what:item.name},
			function(r){ CreateEditFile(r,false,$scope.current,item.name); },"json")
			.always(function(){
				CORE.HideLoading();
			});
	};
}]);

$.event.props.push("dataTransfer");

/** Непосредственно загрузчик файлов на сервер
 * @param url Адрес загрузки
 * @param max_file_uploads Серверное ограничение на количество одновременно загружаемых файлов
 * @param upload_max_filesize Серверное ограничение на максимальный размер файла
 * @param progress CallBack прогресса загрузки
 * @param finish CallBack завершения загрузки
 * @returns {Upload} */
function Uploader(url,max_file_uploads,upload_max_filesize,progress,finish)
{
	var in_progress=0,
		total_size=0,//Сколько всего нужно загрузить
		uploaded_size=0,//Размер загруженного
		Upload=function(files,fields)
		{
			var data=new FormData(),
				ajax,
				cancel=function(){
					ajax.abort();
				},
				cursize=0,//Текущий размер файлов
				curnum=0,//Текущее число файлов
				toobig=[],//Массив файлов, размер которых слишком велик
				overflow=[];//Массив файлов, которые не влезают в текущий POST

			$.each(files,function(k,v){
				if(v.size>upload_max_filesize)
					toobig.push(v.name);
				else if((cursize+v.size)>upload_max_filesize || curnum>=max_file_uploads)
					overflow.push(v);
				else
				{
					curnum++;
					cursize+=v.size;
					data.append("file[]",v);
				}
			});

			if(toobig.length>0)
				alert( CORE.Lang("too-big",[toobig]));

			if(curnum>0)
			{
				$.each(fields,function(i,v){
					data.append(i,v);
				});
				in_progress++;

				ajax=$.ajax({
					url:url,
					data:data,
					processData:false,
					contentType:false,
					type:"POST",
					success:function(r){
						if(r.errors)
							console.error(r.errors.join(","));
					},
					xhr:function(){
						var r=$.ajaxSettings.xhr();

						if(r.upload)
						{
							var old_loaded=0,
								old_total=0;

							$(r.upload).on("progress load",function(e){
								var oe=e.originalEvent;

								if(oe.loaded>0)
									uploaded_size+=oe.loaded-old_loaded;

								if(old_total==0 && oe.loaded>0)
									total_size+=oe.total;

								old_total=oe.total;
								old_loaded=oe.loaded;

								progress(uploaded_size,total_size);
							}).on("error abort",function(){
								total_size-=old_total;
								uploaded_size-=old_loaded;
							}); 
						}

						return r;
					},
					dataType:"json"
				}).fail(function(r){
					console.error(r);
				}).always(function(){
					if(--in_progress==0)
					{
						finish(total_size>0);

						uploaded_size=0;
						total_size=0;
					}

					CancelUpload.remove(cancel);
				});

				CancelUpload.add(cancel);
			}

			if(overflow.length>0)
				Upload(overflow);
		};

	return Upload;
};