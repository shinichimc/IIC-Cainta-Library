
$(function(){
	$("dl.subList").hide().children("dd").hide();
	
	$("ul#accordionBox h3").click(function () {
		funcClickEvent($(this), $("dl.subList"), $("ul#accordionBox h3"));					  
	});
	
	$("dl.subList dt").click(function () {
		funcClickEvent($(this), $("dl.subList dd"), $("dl.subList dt"));	
	});
	
	function funcClickEvent(dt, dd, h3){
		$(dt).toggleClass("subOpened");
		$(h3).toggleClass("mainOpened");
		$(h3).not($(dt)).removeClass("subOpened").removeClass("mainOpened");
		$(dt).next(dd).slideToggle('normal');
		$(dd).not(dt.next(dd)).hide();
		$("html, body").animate({ scrollTop: p }, 'fast');
			return false;    
		}
});

    
    