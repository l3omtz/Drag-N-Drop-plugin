// OPD CUSTOM MACARON PICKER
// A custom macaron picker that generates a array of sorted elements
// version 1.0, 20th Aug 2015
// by Shaun Gillon

//
// 1. BASE DRAGGABLE AND DROPPABLE SETTINGS
//

$(function() {

 $("#left-pane .ui-draggable").draggable({
        helper: "clone",
        cursor: "move"
    });

     // 1. Let BOX be droppable, accepting the Macarons items
    $("#right-pane .empty").droppable({
        accept: "#left-pane .ui-draggable",
        activeClass: "ui-state-highlight",
        drop: function(event, ui) {
            if ($(this).hasClass("empty")) {

	            // 2. Swap in New Image
                $(this).children("img").remove();
                $(this).append(jQuery(ui.draggable).clone());
                $(this).removeClass("empty");
                $(this).addClass("containsMac");

                // 3. Count Macarons and show add button when full
                var totalMacs = $(".showBox .slot").length
                var currentMacs = $(".showBox .containsMac").length;

                console.log(totalMacs);
                console.log(currentMacs);

                if (currentMacs == totalMacs) {
                    event.stopPropagation();
                    $(this).MacaronPickerResults();
                }
			}
        }
	});

	//
	// 2. REMOVE MACARON FROM BASKET ON CLICK
	//

    $(".slot").on( "click", function()  {

        // 1 .Fetch Class marked Draggable
        var name = $(this).find('.ui-draggable');

        // 2 .Check if class exists on click
        if(name.length != 0) {

	        	// 3 .Remove draggable image and replace with default
	        	$(this).find("div").remove();
	        	$(this).removeClass('containsMac');
	        	$(this).addClass('empty');
				$('<img src="https://cdn.shopify.com/s/files/1/0924/5464/files/MacBlank.png?7488275844738515826" alt="Macaron" class="ui-droppable">').appendTo(jQuery(this));

        }
    });


	//
	// 3 . ADD MACARON TO BASKET ON CLICK
    //

    $("#left-pane .ui-draggable").click(function() {

        var macaronCount = $(".showBox .containsMac").length;
        var totalBoxMacs = $(".slot").length

        if (macaronCount < totalBoxMacs) {

	        // 1 .Search through for empty basket item
	        for (var n = 1; n < 19; ++ n){

	        var macaron = '.showBox #slot-' + n;


	        	// 2. If empty replace with clicked macaron
	        	if ($(macaron).hasClass("empty")) {

			        $(macaron).children("img").remove();
	                $(this).clone().appendTo(macaron).css("transform", "rotate(-90deg)" );
	                $(macaron).removeClass("empty");
	                $(macaron).addClass("containsMac");

			        //Count Macarons and show add button when full
		            var totalBasketMacs = $(".showBox .slot").length
		            var macaronsAdded = $(".showBox .containsMac").length;

		            if (macaronsAdded == totalBasketMacs) {
						$(this).MacaronPickerResults();
		            }

	                return false;
                }
         	}
         }
    });

  //
  // 4 .Add On Click to Box Selection
  //
      $(document).on('click','.showMacLink',function(e){

      jQuery('.showBox').removeClass('showBox');

      if(this.id == "mac-0"){
        jQuery('#boxof6').addClass('showBox');
      } else if(this.id == "mac-1"){
        jQuery('#boxof12').addClass('showBox');
      } else if(this.id == "mac-2"){
        jQuery('#boxof18').addClass('showBox');
      }

      $(this).resetMacaron();

      $('.selected-mac').removeClass('selected-mac');
      $(this).parent().children('.inner-mac').addClass('selected-mac');

		// Fade In next div and scroll
        $('.picker').fadeIn("fast", function() {
           $('html, body').animate({
                scrollTop: $("#picker").offset().top - 50
            }, 500);
        });
    });


  	//
	// 5 . RESET MACARON BOX
    //

   $(document).on('click','a[href = "#reset-mac"]',function(e){

     		$(this).resetMacaron();

    });

  	$.fn.resetMacaron = function() {

        var macaronCount = $(".showBox .containsMac").length;

        // 1 .Search through for empty basket item
        for (var n = 1; n <= macaronCount; ++ n){

        var macaron = '.showBox #slot-' + n

        // 3 .Remove draggable image and replace with default
        $(macaron).find("div").remove();
        $(macaron).removeClass('containsMac');
        $(macaron).addClass('empty');
        $('<img src="https://cdn.shopify.com/s/files/1/0924/5464/files/MacBlank.png?7488275844738515826" alt="Macaron" class="ui-droppable">').appendTo(jQuery(macaron));

    	}

         //Reset Variables
         $('#right-pane .full-basket').fadeOut();
         $('.pay-picker').fadeOut();
         $(".confirmation-mac").fadeOut(function(){ $("#left-pane").fadeIn(); });
         $('.showBox').removeClass('not-active');
    }

	//
	// 6 .CHECK IF CUSTOM BASKET IS FULL - PUBLIC METHOD
	//

	$.fn.MacaronPickerResults = function() {

					//Stop box selection
                     $('.showBox').addClass('not-active');

  					$("#left-pane").fadeOut(function(){ $(".confirmation-mac").fadeIn(); });
					$('.full-basket').fadeIn('slow');

                    // 1 .Perfrom Fade and List generation functions when Box full
                    $("#flavours").fadeOut().promise().done(function() {
                        $('#confirmationMac').fadeIn();

                        // 2 .Generate List from Macarons
                        var IDs = [];
                        $(".showBox .containsMac").find("img").each(function() {
                            IDs.push(this.alt);
                        });
                        console.log(IDs);

                        // 3 .Count number of occurances of variables
                        counts = {};
                        jQuery.each(IDs, function(key, value) {
                            if (!counts.hasOwnProperty(value)) {
                                counts[value] = 1;
                            } else {
                                counts[value] ++;
                            }
                        });

                        console.log(counts)

                        // 4 .Switch values around to a array to make 3 x ...
                        var blkstr = [];
                        jQuery.each(counts, function(idx2, val2) {
                            var str = val2 + " x " + idx2;
                            blkstr.push(str + " ");
                        });

                        console.log(blkstr)

                        // 5 .Add Text to Selection Label
                        $("#selection").val(blkstr);
						$(".selection-input").text(blkstr);

                        //Add List to LI
                        var cList = jQuery('#confirmationMac .macList')
                        jQuery.each(blkstr, function(i) {
                            var li = jQuery('<li/>').addClass('ui-menu-item').attr('role', 'menuitem').text(blkstr[i]).appendTo(cList);
                        });

                        });

  						// Fade In next div and scroll
                        $('.pay-picker').fadeIn("fast", function() {
                           $('html, body').delay(500).animate({
                                scrollTop: $("#pay-picker").offset().top
                            }, 500);
                        });


                        }

});
