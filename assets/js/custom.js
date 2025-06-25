//custon js starts here

//ajax on click for data update
jQuery(document).ready(function($) {
    // Use event delegation to handle dynamically created buttons
    $(document).on('click', "#ren-spin-ajax, #renew-defload", function(e) {
        e.preventDefault(); // Prevent form submission
        
        // Add loading state to button
        const $btn = $(this);
        const originalText = $btn.html(); // Use html() to preserve icons
        const isManualSync = $btn.attr('id') === 'renew-defload';
        
        // Prevent double clicks
        if ($btn.hasClass('loading')) {
            return false;
        }
        
        $btn.html('⏳ Processing...').prop('disabled', true).addClass('loading');

        $.ajax({
            type: "GET",
            url: ajaxurl,
            data: {
                'action': "renew_get_data_test"
            },
            beforeSend: function() {
                // Different handling for manual sync vs initial sync
                if (isManualSync) {
                    // For manual sync, find the sync section and replace content
                    const $tabContent = $btn.closest('.tab-content-inner');
                    const $progressArea = $tabContent.find('.renew-rem-progress');
                    const $buttonArea = $tabContent.find('.renew-rem-button-sect-default');
                    
                    // Clear any existing content and hide elements
                    $progressArea.empty().hide();
                    $buttonArea.fadeOut(300, function() {
                        // Create progress indicator
                        $progressArea.html(`
                            <div id='renew-rem-page-wrap' style='opacity: 0; margin: 30px auto; text-align: center; max-width: 500px;'>
                                <div class='renew-spin-meter'> 
                                    <span class='renew-inner-span' style='width: 7%'></span> 
                                </div>
                                <div style='text-align: center; margin-top: 15px; color: #667eea; font-weight: 600; font-size: 16px;'>
                                    🔄 Synchronizing subscription data...
                                </div>
                            </div>
                        `).show();
                        
                        // Animate progress bar
                        $("#renew-rem-page-wrap").animate({opacity: 1}, 500, function() {
                            // Start progress animation
                            $(".renew-inner-span").animate({
                                width: "30%"
                            }, 1000);
                        });
                    });
                } else {
                    // For initial sync, use original behavior
                    $(".renew-rem-button-sect, .renew-rem-progress").fadeOut(300, function() {
                        $(this).replaceWith(`
                            <div id='renew-rem-page-wrap' style='opacity: 0;'>
                                <div class='renew-spin-meter'> 
                                    <span class='renew-inner-span' style='width: 7%'></span> 
                                </div>
                                <div style='text-align: center; margin-top: 15px; color: #667eea; font-weight: 600;'>
                                    🔄 Synchronizing data...
                                </div>
                            </div>
                        `);
                        $("#renew-rem-page-wrap").animate({opacity: 1}, 500, function() {
                            $(".renew-inner-span").animate({
                                width: "30%"
                            }, 1000);
                        });
                    });
                }
            },
            success: function(response) {
                console.log('Sync successful:', response);
                
                // Complete progress bar animation
                $(".renew-inner-span").animate({
                    width: "100%"
                }, 800);
            },
            complete: function(data) {
                setTimeout(function() {
                    $("#renew-rem-page-wrap").fadeOut(400, function() {
                        $(this).replaceWith(`
                            <div class='ren-completed-status' style='opacity: 0; margin: 20px auto; max-width: 500px;'>
                                <span style='margin-right: 10px; font-size: 20px;'>✅</span>
                                Synchronization Complete! ${isManualSync ? 'Data updated successfully.' : 'Redirecting to settings...'}
                            </div>
                        `);
                        $(".ren-completed-status").animate({opacity: 1}, 500);
                        
                        // For manual sync, restore the interface after showing success
                        if (isManualSync) {
                            setTimeout(function() {
                                $(".ren-completed-status").fadeOut(300, function() {
                                    $(this).remove();
                                    
                                    // Find the tab content container
                                    const $tabContent = $('.tab-content-inner');
                                    
                                    // Clear progress area completely
                                    $tabContent.find('.renew-rem-progress').empty().hide();
                                    
                                    // Restore or create button area
                                    let $buttonArea = $tabContent.find('.renew-rem-button-sect-default');
                                    if ($buttonArea.length === 0) {
                                        // Create new button area if it doesn't exist
                                        $tabContent.append(`
                                            <div class="renew-rem-progress" style="display: none;"></div>
                                            <div class="renew-rem-button-sect-default" style="padding-top: 30px; text-align: center;">
                                                <button class="button-primary" id="renew-defload" style="font-size: 14px;">
                                                    🔄 Start Manual Sync
                                                </button>
                                            </div>
                                        `);
                                    } else {
                                        // Restore existing button area
                                        $buttonArea.html(`
                                            <button class="button-primary" id="renew-defload" style="font-size: 14px;">
                                                🔄 Start Manual Sync
                                            </button>
                                        `).show();
                                    }
                                });
                            }, 2500); // Show success message for 2.5 seconds
                        }
                    });
                }, 1500);
                
                // Only redirect for initial sync, not manual sync
                if (!isManualSync) {
                    //redirect after 3 sec once sync is done
                    var renew_url = window.location.href + "&tab=settings";
                    setTimeout(function() {
                        // Add smooth transition effect
                        $('body').fadeOut(300, function() {
                            window.location.replace(renew_url);
                        });
                    }, 4000);
                }
            },
            error: function(jqXHR, exception) {
                console.log("Ajax Error:", jqXHR.responseText);
                
                // Show error message
                if ($("#renew-rem-page-wrap").length) {
                    $("#renew-rem-page-wrap").replaceWith(`
                        <div class='notice notice-error' style='padding: 15px; text-align: center; margin: 20px auto; border-radius: 8px; max-width: 500px;'>
                            <strong>⚠️ Error:</strong> Unable to synchronize data. Please try again.
                            <br><small>If the problem persists, please contact support.</small>
                        </div>
                    `);
                    
                    // Restore interface for manual sync after error
                    if (isManualSync) {
                        setTimeout(function() {
                            $('.notice-error').fadeOut(300, function() {
                                $(this).remove();
                                
                                // Find the tab content container
                                const $tabContent = $('.tab-content-inner');
                                
                                // Clear progress area and restore button
                                $tabContent.find('.renew-rem-progress').empty().hide();
                                
                                // Restore button area
                                let $buttonArea = $tabContent.find('.renew-rem-button-sect-default');
                                if ($buttonArea.length === 0) {
                                    $tabContent.append(`
                                        <div class="renew-rem-progress" style="display: none;"></div>
                                        <div class="renew-rem-button-sect-default" style="padding-top: 30px; text-align: center;">
                                            <button class="button-primary" id="renew-defload" style="font-size: 14px;">
                                                🔄 Start Manual Sync
                                            </button>
                                        </div>
                                    `);
                                } else {
                                    $buttonArea.html(`
                                        <button class="button-primary" id="renew-defload" style="font-size: 14px;">
                                            🔄 Start Manual Sync
                                        </button>
                                    `).show();
                                }
                            });
                        }, 3000); // Show error for 3 seconds
                    }
                } else {
                    // If no progress wrap exists, show error near button and restore button state
                    if (isManualSync) {
                        // Show error message
                        const $tabContent = $('.tab-content-inner');
                        $tabContent.append(`
                            <div class='notice notice-error' style='padding: 15px; text-align: center; margin: 20px auto; border-radius: 8px; max-width: 500px;'>
                                <strong>⚠️ Error:</strong> Unable to synchronize data. Please try again.
                            </div>
                        `);
                        
                        // Remove error after timeout
                        setTimeout(function() {
                            $('.notice-error').fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 5000);
                    }
                    
                    // Restore button state
                    $btn.html(originalText).prop('disabled', false).removeClass('loading');
                }
            }
        });
    });

    // Improved progress bar animation
    $(document).on('DOMNodeInserted', '.renew-spin-meter', function() {
        const $progressBar = $(this).find('.renew-inner-span');
        if ($progressBar.length && !$progressBar.hasClass('animated')) {
            $progressBar.addClass('animated');
        }
    });
});

//code for disable tab buttons if not sync first!    
jQuery(document).ready(function($) {
    if (window.location.href.indexOf("&tab=") == -1) {
        $(".nav-tab").click(function(e) {
            e.preventDefault();
            // Show animated tooltip
            const $tab = $(this);
            $tab.addClass('shake-animation');
            setTimeout(() => $tab.removeClass('shake-animation'), 500);
        });

        $(".nav-tab").attr('ren-data-tooltip', 'Synchronize first to access the settings!');
        
        // Add shake animation CSS
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .shake-animation {
                    animation: shake 0.5s;
                }
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }
            `)
            .appendTo('head');
    }
});

//code for customtooltip with improved animations
jQuery(document).ready(function($) {
    $('.adm-tooltip-renew-rem').mouseenter(function() {
        var $this = $(this);
        var tooltipValue = $this.attr("data-tooltip");
        
        // Remove any existing tooltip
        $('#sphoveringTooltip').remove();
        
        if (tooltipValue) {
            var content = "<div id='sphoveringTooltip'>" + tooltipValue + "</div>";
            $('body').append(content);
            
            var $tooltip = $('#sphoveringTooltip');
            var offset = $this.offset();
            var elementHeight = $this.outerHeight();
            var elementWidth = $this.outerWidth();
            var tooltipWidth = $tooltip.outerWidth();
            var tooltipHeight = $tooltip.outerHeight();
            var windowWidth = $(window).width();
            var scrollTop = $(window).scrollTop();
            
            // Calculate position
            var left = offset.left + (elementWidth / 2) - (tooltipWidth / 2);
            var top = offset.top - tooltipHeight - 8;
            
            // Adjust if tooltip goes off screen
            if (left < 10) {
                left = 10;
            } else if (left + tooltipWidth > windowWidth - 10) {
                left = windowWidth - tooltipWidth - 10;
            }
            
            // If tooltip goes above viewport, show it below
            if (top < scrollTop + 10) {
                top = offset.top + elementHeight + 8;
            }
            
            $tooltip.css({
                'position': 'absolute',
                'left': left + 'px',
                'top': top + 'px',
                'opacity': '0',
                'z-index': '99999',
                'background': 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
                'color': 'white',
                'padding': '8px 12px',
                'border-radius': '6px',
                'font-size': '12px',
                'font-weight': '500',
                'box-shadow': '0 4px 15px rgba(0,0,0,0.2)',
                'max-width': '250px',
                'word-wrap': 'break-word',
                'text-align': 'left',
                'line-height': '1.4',
                'pointer-events': 'none',
                'white-space': 'normal'
            }).animate({
                opacity: 1
            }, 200);
        }
    });

    $('.adm-tooltip-renew-rem').mouseleave(function() {
        $('#sphoveringTooltip').animate({
            opacity: 0
        }, 150, function() {
            $(this).remove();
        });
    });
    
    // Remove tooltip on scroll or resize
    $(window).on('scroll resize', function() {
        $('#sphoveringTooltip').remove();
    });
});

// Enhanced copy function with visual feedback
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
        that.title = 'Copied!';
        that.classList.add('copied');
        
        // Add visual feedback
        jQuery(that).css({
            'background': 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)',
            'color': 'white',
            'transform': 'scale(1.05)'
        });
        
        setTimeout(function() {
            jQuery(that).css({
                'background': '',
                'color': '',
                'transform': ''
            });
        }, 1000);
    }
}

// Enhanced ad dismiss with smooth animation
jQuery(document).ready(function(){
    jQuery(".sp-ad-dismiss").click(function(){
        jQuery(".sp-ad").animate({
            opacity: 0,
            transform: 'scale(0.95)'
        }, 300, function() {
            jQuery(this).slideUp(200, function() {
                jQuery(this).remove();
            });
        });
    });
    
    // Add hover effects to interactive elements
    jQuery('.screenshots .img-card').hover(
        function() {
            jQuery(this).find('img').css('transform', 'scale(1.05)');
        },
        function() {
            jQuery(this).find('img').css('transform', 'scale(1)');
        }
    );
    
    // Smooth scroll for anchor links
    jQuery('a[href^="#"]').click(function(e) {
        e.preventDefault();
        var target = jQuery(jQuery(this).attr('href'));
        if(target.length) {
            jQuery('html, body').animate({
                scrollTop: target.offset().top - 20
            }, 500);
        }
    });
});

// Add entrance animations when page loads
document.addEventListener('DOMContentLoaded', () => {
    const titleEl = document.querySelector('.sp-ad-title-typer');
    const textEl = document.querySelector('.sp-ad-text-typer');
    const pricingFooterEl = document.querySelector('.sp-pricing-footer');

    // Hide initial elements until typing starts
    [titleEl, textEl].forEach(el => {
        if (el) el.style.visibility = 'hidden';
    });

    // Hide pricing footer initially
    if (pricingFooterEl) {
        pricingFooterEl.style.opacity = '0';
        pricingFooterEl.style.transform = 'translateY(20px)';
        pricingFooterEl.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
    }

    // Utility to create a blinking cursor span
    function addCursor(el) {
        const cursor = document.createElement('span');
        cursor.classList.add('typewriter-cursor');
        cursor.textContent = '|';
        el.parentNode.insertBefore(cursor, el.nextSibling);
        return cursor;
    }

    function typeWrite(el, text, speed) {
        return new Promise((resolve) => {
            // Clear any existing content and show element
            el.textContent = '';
            el.style.visibility = 'visible';

            const cursor = addCursor(el);
            let i = 0;

            function type() {
                if (i < text.length) {
                    el.textContent += text.charAt(i++);
                    setTimeout(type, speed);
                } else {
                    cursor.remove();
                    resolve();
                }
            }

            type();
        });
    }

    async function run() {
        if (!titleEl) return;
        const fullTitle = titleEl.textContent.trim();
        if (fullTitle) {
            await typeWrite(titleEl, fullTitle, 20);
            if (textEl) {
                const fullText = textEl.textContent.trim();
                if (fullText) {
                    await typeWrite(textEl, fullText, 20);
                }
            }
        }
        
        // Show pricing footer after typing is complete
        if (pricingFooterEl) {
            setTimeout(() => {
                pricingFooterEl.style.opacity = '1';
                pricingFooterEl.style.transform = 'translateY(0)';
            }, 300);
        }
    }

    run();
});


