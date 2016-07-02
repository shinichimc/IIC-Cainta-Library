

$(function(){
	$("div").corner();

	/*$("div, .myButton").click(function(){
		$(this).fadeOut(500);
	});*/

	$(".drop").chosen({
    disable_search_threshold: 10,
    no_results_text: "Oops, nothing found!"
  });

	

	$(".button").click(function(){
		
		var num = $(".txtfield").val();
		for(var x=0;x<=10;x++){
			var d =$("<div>").css({"width":"100px","height":"100px","background-color":"white"});
			
			$(".area").prepend(d);

		}
		

	});

	$(".button_off").click(function(){
		$(".area p:first-child").remove();
		
	});

	$(".ef").click(function(){
		
		$(this).toggle(1000,function(){
			alert("hehehe");
		});
	});



});