$(function(){
		$('.cases_box').jCarouselLite({
            btnNext: ".turn_left",
            btnPrev: ".turn_right",
            visible: 5,
            auto: 2000,
            speed: 2000,
            scroll: 1,
			onMouseOver: true
        });
		
		$('.partners_box').jCarouselLite({
            btnNext: ".prev",
            btnPrev: ".next",
            visible: 6,
            speed: 2000,
            scroll: 1,
			onMouseOver: true
        });
		
		$(".carousel").jCarouselLite({
			btnNext: ".next1",
			btnPrev: ".prev1"
		  });
		
		$('#tabs').tabs();
		$("#tabs a,.prev1 a,.next1 a,.prev2,.next2,.link1,.link2,.link3,#menu ul li a,.newsNav li a").focus(function () { this.blur()}); 
		
		var originalFontSize = $('body').css('font-size');   
		// Increase Font Size
		$(".increaseFont").click(function() {
			var currentFontSize = $('body').css('font-size');
			var currentFontSizeNum = parseFloat(currentFontSize, 10);
			var largestFontSize = 16;
			
			if(currentFontSizeNum < largestFontSize){
				var newFontSize = Math.floor(currentFontSizeNum + 2);
				$('.text').css('font-size', newFontSize);
			}
			
			else {
				$('.text').css('font-size', largestFontSize);
			}
			return false;
		});
		// Decrease Font Size
		$(".decreaseFont").click(function() {
			var currentFontSize = $('body').css('font-size');
			var currentFontSizeNum = parseFloat(currentFontSize, 10);    	
			var smallFontSize = 12;   	
			
			if(currentFontSizeNum > smallFontSize){
				var newFontSize = Math.floor(currentFontSizeNum - 2);
				$('.text').css('font-size', newFontSize);
			}
			
			else {
				$('.text').css('font-size', smallFontSize);
			}
			return false;
		});


});