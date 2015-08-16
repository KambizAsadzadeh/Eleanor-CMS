/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
var modules=[];//'btford.socket-io'

if(typeof Uploader!="undefined")
	modules.push("Uploader");

angular.module('Admin', modules)
.controller('UploaderFilesHelper',["$scope",function($scope){
	$scope.Class=function(name){
		return name.match(/\.(jpe?g|gif|png|webp)$/) ? 'fl-img' : 'fl-'+name.match(/([a-z]+)$/)[1];
	};
	$scope.GoHelp=function(event,Go,where){
		if(event.currentTarget===event.target)
			Go(where);
	};
	$scope.BgImage=function(file)
	{
		var style={};
		if("image" in file)
			style['background-image']='url('+file.image+')';
		return style;
	};
	$scope.InsertHelp=function(event,Insert,file,index)
	{
		if(event.currentTarget===event.target)
			Insert(file,false,index,event);
	};
	$scope.Status=function(max_upload,max_files)
	{
		return CORE.Lang("uploader-status",[max_upload,max_files]);
	};
	$scope.Preview=function(file)
	{
		return file.name.match(/\.(jpe?g|png|gif|bmp|webp)$/) ? true : false;
	};
}]);