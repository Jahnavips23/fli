/**
 * Flione IT - Counter Animation JavaScript
 * Version: 1.0
 */

(function($) {
    'use strict';
    
    // Counter animation
    function startCounterAnimation() {
        $('.counter-number').each(function() {
            var $this = $(this);
            var countTo = parseInt($this.attr('data-count'));
            
            // Check if the element is in viewport
            if (isElementInViewport($this) && !$this.hasClass('counted')) {
                $this.addClass('counted');
                
                // Determine animation speed based on the count value
                var duration = countTo > 1000 ? 2000 : 1500;
                
                // Format for large numbers
                var formatNumber = function(num) {
                    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                };
                
                // Animate the counter
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: duration,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(formatNumber(countTo));
                    }
                });
            }
        });
    }
    
    // Check if element is in viewport
    function isElementInViewport(el) {
        if (typeof jQuery === "function" && el instanceof jQuery) {
            el = el[0];
        }
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // Start counter animation when scrolling
    $(window).on('scroll', function() {
        startCounterAnimation();
    });
    
    // Start counter animation on page load
    $(document).ready(function() {
        setTimeout(startCounterAnimation, 500);
    });
    
})(jQuery);