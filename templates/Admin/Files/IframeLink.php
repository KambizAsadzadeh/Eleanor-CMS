<div class="modal fade" id="iframe" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<iframe style="width:100%;border:0;height:100px;"></iframe>
			</div>
		</div>
	</div>
</div>
<script>$(function(){
	$("#iframe iframe").load(function(){
		var th=$(this),
			old_left=0,
			cw=this.contentWindow;

		$("#iframe h4").text( $("head title",cw.document).text() );


		$(cw.document).scroll(function(e,force){
			var left=$(this).scrollLeft(),
				th2=this;
			if(left==old_left || force)
				setTimeout(function(){
					var h1=$("html",th2).height(),
						wh=$(cw).height();
					th.height( h1 );

					if(wh>0 && wh<h1 && wh!=100)
						th.height( 2*h1-wh );

				},50);

			old_left=left;
		}).trigger("scroll",[true]);
	});

	$(document).on("click","a.iframe[href]",function(e){
		e.preventDefault();
		$('#iframe').modal("show").find("iframe").prop("src",$(this).prop("href")+"&iframe=1");
	});

	$('#iframe').on("hidden.bs.modal",function(){
		$("iframe",this).height("100px");
	});
})</script>