/**
 * Flione IT - Main JavaScript File
 * Version: 1.0
 */

(function($) {
    'use strict';
    
    // Preloader
    $(window).on('load', function() {
        if ($('#preloader').length) {
            $('#preloader').delay(100).fadeOut('slow', function() {
                $(this).remove();
            });
        }
    });
    
    // Smooth scroll for the navigation menu and links with .scrollto classes
    $(document).on('click', '.nav-menu a, .mobile-nav a, .scrollto', function(e) {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            e.preventDefault();
            var target = $(this.hash);
            if (target.length) {
                var scrollto = target.offset().top;
                var scrolled = 20;
                
                $('html, body').animate({
                    scrollTop: scrollto - scrolled
                }, 1000, 'easeInOutExpo');
                
                if ($(this).parents('.nav-menu, .mobile-nav').length) {
                    $('.nav-menu .active, .mobile-nav .active').removeClass('active');
                    $(this).closest('li').addClass('active');
                }
                
                return false;
            }
        }
    });
    
    // Mobile Navigation
    if ($('.nav-menu').length) {
        var $mobile_nav = $('.nav-menu').clone().prop({
            class: 'mobile-nav d-lg-none'
        });
        $('body').append($mobile_nav);
        $('body').prepend('<button type="button" class="mobile-nav-toggle d-lg-none"><i class="fas fa-bars"></i></button>');
        $('body').append('<div class="mobile-nav-overly"></div>');
        
        $(document).on('click', '.mobile-nav-toggle', function(e) {
            $('body').toggleClass('mobile-nav-active');
            $('.mobile-nav-toggle i').toggleClass('fa-bars fa-times');
            $('.mobile-nav-overly').toggle();
        });
        
        $(document).on('click', '.mobile-nav .drop-down > a', function(e) {
            e.preventDefault();
            $(this).next().slideToggle(300);
            $(this).parent().toggleClass('active');
        });
        
        $(document).click(function(e) {
            var container = $(".mobile-nav, .mobile-nav-toggle");
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                if ($('body').hasClass('mobile-nav-active')) {
                    $('body').removeClass('mobile-nav-active');
                    $('.mobile-nav-toggle i').toggleClass('fa-bars fa-times');
                    $('.mobile-nav-overly').fadeOut();
                }
            }
        });
    } else if ($(".mobile-nav, .mobile-nav-toggle").length) {
        $(".mobile-nav, .mobile-nav-toggle").hide();
    }
    
    // Stick the header at top on scroll
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('.header').addClass('header-scrolled');
            $('.back-to-top').addClass('active');
        } else {
            $('.header').removeClass('header-scrolled');
            $('.back-to-top').removeClass('active');
        }
    });
    
    if ($(window).scrollTop() > 100) {
        $('.header').addClass('header-scrolled');
        $('.back-to-top').addClass('active');
    }
    
    // Back to top button
    $('.back-to-top').click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 1000, 'easeInOutExpo');
        return false;
    });
    
    // Initiate the counter
    $('.counter').counterUp({
        delay: 10,
        time: 1000
    });
    
    // Testimonials carousel
    $(".testimonials-carousel").owlCarousel({
        autoplay: true,
        dots: true,
        loop: true,
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 2
            },
            900: {
                items: 3
            }
        }
    });
    
    // Porfolio isotope and filter
    $(window).on('load', function() {
        var portfolioIsotope = $('.portfolio-container').isotope({
            itemSelector: '.portfolio-item',
            layoutMode: 'fitRows'
        });
        
        $('#portfolio-flters li').on('click', function() {
            $("#portfolio-flters li").removeClass('filter-active');
            $(this).addClass('filter-active');
            
            portfolioIsotope.isotope({
                filter: $(this).data('filter')
            });
        });
    });
    
    // Initiate venobox (lightbox feature used in portofilo)
    $(document).ready(function() {
        $('.venobox').venobox();
    });
    
    // Portfolio details carousel
    $(".portfolio-details-carousel").owlCarousel({
        autoplay: true,
        dots: true,
        loop: true,
        items: 1
    });
    
    // Animation on scroll
    $(window).on('load', function() {
        AOS.init({
            duration: 1000,
            easing: "ease-in-out",
            once: true,
            mirror: false
        });
    });


})(jQuery);