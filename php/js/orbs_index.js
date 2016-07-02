$(function(){	

	// $("#nothing").effect("explode");	

	$(".clickme").click(function(){
		var num = $(".txtfield").val();
		var li = $("<li>").text("hello there");
		var ul = $(".listcontainer");

		for(var i = 0 ; i < num ; i++){
			var box = $('<div>').addClass("boxdeco col-sm-5");
			$(".smallcontainer").append(box)	;
		}
		
	});

	$(".clearme").click(function(){
		$(".smallcontainer").empty();
	});

	// $(".arrangeme").click(function(){
	// 	$("div.boxdeco").toggleClass("boxdeco");
	// });
	
	

	
});

