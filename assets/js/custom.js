  //custon js starts here

  //ajax on click for data update
  jQuery(document).ready(function($) {
    $("#ren-spin-ajax, #renew-defload").on('click', function(e) {

        $.ajax({
            type: "GET",
            url: ajaxurl,
            data: {
                'action': "renew_get_data_test"
            },
            success: function(response) {

            },
            beforeSend: function() {

                $(".renew-rem-button-sect, .renew-rem-progress").replaceWith(" <div id='renew-rem-page-wrap'>  <div class='renew-spin-meter'> <span class='renew-inner-span' style='width: 7%'></span> </div> </div>");

            },
            complete: function(data) {

                $(".renew-inner-span").animate({
                    width: "100%"
                });
                setTimeout(function() {
                    $("#renew-rem-page-wrap").replaceWith("<span class='ren-completed-status'>Completed Synchronisation! This Tab will be automatically redirect to Settings.</span>");
                }, 1000);
                //redirect after 3 sec once sync is done
                var renew_url = window.location.href + "&tab=settings";
                //time out
                setTimeout(function() {
                    window.location.replace(renew_url);
                }, 3000);

            },
            error: function(jqXHR, exception) {
                console.log("Ajax Error");
                // Your error handling logic here..
            }
        });


    });
    //code to run the progress bar
    jQuery(".renew-spin-meter > span").each(function() {
        jQuery(this)
            .data("origWidth", $(this).width())
            .width(0)
            .animate({
                    width: $(this).data("origWidth")
                },
                2000
            );
    });
});



//code for disable tab buttons if not sync first!    
jQuery(document).ready(function($) {

    if (window.location.href.indexOf("&tab=") == -1) {
        $(".nav-tab").click(false);

        $(".nav-tab").attr('ren-data-tooltip', 'Synchronize first to access the settings!');
    }

});

//code for customtooltip

jQuery(document).ready(function($) {
    $('.adm-tooltip-renew-rem').mouseenter(function() {
        $tooltiplvalue = $(this).attr("data-tooltip");

        console.log($tooltiplvalue);
        $content = "<div id='sphoveringTooltip' >" + $tooltiplvalue + "</div>";
        $(this).prepend($content);
        $('#sphoveringTooltip').css({
            'background': '#070707',
            'position': 'absolute',
            'opacity': '75%',
            'margin-top': '28px',
            'margin-left': '24px',
            'transition': 'all',
            'padding': '10px',
            'color': 'white',
            'border-radius': '3px',
            'box-shadow': '1px 1px .5px rgb(8, 8, 8)',
            'max-width': '200px',
            'z-index': "9999",
        })

    });

    $('.adm-tooltip-renew-rem').mouseleave(function() {
        $('#sphoveringTooltip').remove();
    });

});

// copy
function copy(that) {
    var inp = document.createElement('input');
    document.body.appendChild(inp);
    inp.value = that.textContent;
    inp.select();
    document.execCommand('copy', false);
    inp.remove();

    var allElements = document.getElementsByClassName('copied');
    for (var i = 0; i < allElements.length; i++) {
        allElements[i].title = '';
        allElements[i].classList.remove('copied');
    }

    if (!that.classList.contains('copied')) {
        that.title = 'Copied';
        that.classList.add('copied');
    }
}

jQuery(document).ready(function(){
    jQuery(".sp-ad-dismiss").click(function(){
      jQuery(".sp-ad").fadeOut();
    });
  });