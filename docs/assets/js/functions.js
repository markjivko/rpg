/* global webpHero, URL */
/**
 * @copyright (c) 2021, Mark Jivko
 * @author    Mark Jivko (//markjivko.com)
 * @version   0.1
 * @license   //gnu.org/licenses/gpl-3.0.txt
 * @param $ jQuery
 */
(function ($) {
    "user strict";
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) {
        document.write('<script src="https://unpkg.com/webp-hero@0.0.2/dist-cjs/polyfills.js"></script>');
        document.write('<script src="https://unpkg.com/webp-hero@0.0.2/dist-cjs/webp-hero.bundle.js"></script>');
    }
    
    $(window).on('load', function() {
        $('.preloader').fadeOut(1000);
        var img = $('.bg_img');
        img.css('background-image', function() {
            return `url(${img.data('background')})`;
        });
        $('[data-paroller-factor]').paroller();
    });
    
    $(document).ready(function() {
        window.setTimeout(function() {
            var bannerCarousel = $('.banner-1-slider').owlCarousel({
                loop: true,
                nav: false,
                dots: false,
                items: 1,
                autoplay: true,
                autoplayTimeout: 2000,
                autoplayHoverPause: false,
                margin: 0,
                mouseDrag: false,
                touchDrag: false,
                animateOut: 'fadeOut',
                animateIn: 'fadeIn'
            });
            window.setTimeout(function() {
                bannerCarousel.trigger('next.owl.carousel');
            }, 3800);
            $('.sponsor-slider').owlCarousel({
                loop: true,
                margin: 0,
                responsiveClass: true,
                nav: false,
                dots: false,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: false,
                responsive: {
                    0: {
                        items: 2
                    },
                    480: {
                        items: 3
                    },
                    768: {
                        items: 4
                    }
                }
            });
            var historySlider = $('.history-slider');
            historySlider.children().each(function(index) {
                $(this).attr('data-position', index);
            });
            window.setTimeout(() => {
                historySlider.owlCarousel({
                    loop: false,
                    margin: 0,
                    responsiveClass: true,
                    nav: true,
                    dots: false,
                    autoplay: true,
                    autoplayTimeout: 2000,
                    autoplayHoverPause: true,
                    center: true,
                    navText: ['<span class="feat-prev"><i class="flaticon-left"></i></span>', '<span class="feat-next"><i class="flaticon-right"></i></span>'],
                    responsive: {
                        0: {
                            items: 1
                        },
                        767: {
                            items: 3
                        },
                        1199: {
                            items: 5
                        }
                    }
                });
                $(document).on('click', '.history-slider .owl-item > div', function() {
                    historySlider.trigger('to.owl.carousel', [$(this).data( 'position' ), 300]);
                });
            }, 150);
            $('.feat-slider').owlCarousel({
                center: true,
                items: 1,
                loop: true,
                margin: 0,
                singleItem: true,
                nav: false,
                dots: false,
                thumbs: true,
                mouseDrag: false,
                touchDrag: true,
                thumbsPrerendered: true,
                animateOut: 'fadeOut',
                animateIn: 'fadeIn'
            });
            $('.feature-content-slider-20').owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                margin: 30,
                singleItem: true,
                nav: false,
                dots: false
            });
            var owlFeat = $('.feat-slider, .feature-content-slider-20');
            $('.feat-prev').on('click', function() {
                owlFeat.trigger('prev.owl.carousel');
            });
            $('.feat-next').on('click', function() {
                owlFeat.trigger('next.owl.carousel', [300]);
            });
        }, 250);
            
        "undefined" !== typeof webpHero && (new webpHero.WebpMachine()).polyfillDocument();
        $('.select-bar').niceSelect();
        $('.popup').magnificPopup({
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: true,
            fixedContentPos: false,
            disableOn: 300
        });
        $('body').find(".img-pop").magnificPopup({type: "image", gallery: {enabled: true}});
        new WOW().init();
        $('.faq-wrapper .faq-title').on('click', function (e) {
            var element = $(this).parent('.faq-item');
            if (element.hasClass('open')) {
                element.removeClass('open');
                element.find('.faq-content').removeClass('open');
                element.find('.faq-content').slideUp(300, "swing");
            } else {
                element.addClass('open');
                element.children('.faq-content').slideDown(300, "swing");
                element.siblings('.faq-item').children('.faq-content').slideUp(300, "swing");
                element.siblings('.faq-item').removeClass('open');
                element.siblings('.faq-item').find('.faq-title').removeClass('open');
                element.siblings('.faq-item').find('.faq-content').slideUp(300, "swing");
            }
        });
        $('.faq--area .faq-title').on('click', function (e) {
            var element = $(this).parent('.faq--item');
            if (element.hasClass('open')) {
                element.removeClass('open');
                element.find('.faq-content').removeClass('open');
                element.find('.faq-content').slideUp(300, "swing");
            } else {
                element.addClass('open');
                element.children('.faq-content').slideDown(300, "swing");
                element.siblings('.faq--item').children('.faq-content').slideUp(300, "swing");
                element.siblings('.faq--item').removeClass('open');
                element.siblings('.faq--item').find('.faq-title').removeClass('open');
                element.siblings('.faq--item').find('.faq-content').slideUp(300, "swing");
            }
        });
        $('ul>li>.submenu').parent("li").addClass("menu-item-has-children");
        $('.submenu').parent('li').hover(function() {
            var menu = $(this).find("ul");
            var menupos = $(menu).offset();
            if (menupos.left + menu.width() > $(window).width()) {
                var newpos = -$(menu).width();
                menu.css({
                    left: newpos
                });
            }
        });
        $('.menu li a').on('click', function (e) {
            var element = $(this).parent('li');
            if (element.hasClass('open')) {
                element.removeClass('open');
                element.find('li').removeClass('open');
                element.find('ul').slideUp(300, "swing");
            } else {
                element.addClass('open');
                element.children('ul').slideDown(300, "swing");
                element.siblings('li').children('ul').slideUp(300, "swing");
                element.siblings('li').removeClass('open');
                element.siblings('li').find('li').removeClass('open');
                element.siblings('li').find('ul').slideUp(300, "swing");
            }
        });
        
        var scrollTopButton = $('.scrollToTop');
        var headerSection = $('.header-section');
        $(window).on('scroll', function() {
            var scrollTopPx = $(this).scrollTop();
            scrollTopPx < 500
                ? scrollTopButton.removeClass("active") 
                : scrollTopButton.addClass("active");
                
            scrollTopPx < 1
                ? headerSection.removeClass("active")
                : headerSection.addClass("active");
        });
        scrollTopButton.on('click', function() {
            $('html, body').animate({
                scrollTop: 0
            }, 500);
            return false;
        });
        
        $('.header-bar, .overlay').on('click', function() {
            $('.header-bar').toggleClass('active');
            $('.overlay').toggleClass('active');
            $('.menu').toggleClass('active');
        });
        var scrollPosition = window.scrollY;
        if (scrollPosition >= 1) {
            $('.header-bottom').addClass('active');
            $('.header-section-2').removeClass('plan-header');
        }
        $('.tab ul.tab-menu li').on('click', function (g) {
            var tab = $(this).closest('.tab'),
            index = $(this).closest('li').index();
            tab.find('li').siblings('li').removeClass('active');
            $(this).closest('li').addClass('active');
            tab.find('.tab-area').find('div.tab-item').not('div.tab-item:eq(' + index + ')').hide(10);
            tab.find('.tab-area').find('div.tab-item:eq(' + index + ')').fadeIn(10);
            g.preventDefault();
        });
        $('.tab-up ul.tab-menu li').on('click', function (g) {
            var tabT = $(this).closest('.tab-up'), indexT = $(this).closest('li').index();
            tabT.find('li').siblings('li').removeClass('active');
            $(this).closest('li').addClass('active');
            tabT.find('.tab-area').find('div.tab-item').not('div.tab-item:eq(' + indexT + ')').slideUp(400);
            tabT.find('.tab-area').find('div.tab-item:eq(' + indexT + ')').slideDown(400);
            g.preventDefault();
        });
        $('.counter').countUp({time: 1500, delay: 10});
        $('.social-icons li a').on('mouseover', function (e) {
            var social = $(this).parent('li');
            if (social.children('a').hasClass('active')) {
                social.siblings('li').children('a').removeClass('active');
                $(this).addClass('active');
            } else {
                social.siblings('li').children('a').removeClass('active');
                $(this).addClass('active');
            }
        });
        
        $(function() {
            $('#usd-range').slider({
                range: "min",
                value: 250,
                min: 1,
                max: 500,
                slide: function (event, ui) {
                    $('#usd-amount').val(ui.value + " Users");
                }
            });
            $('#usd-amount').val($('#usd-range').slider("value") + " Users");
        });
        $('.up--down--overflow').prev('*').addClass('pb-lg-200');
        $('.up--down--overflow').next('*').addClass('pt-lg-200');
        $('.up--down--overflow').addClass('pt-lg-0 pb-lg-0');
        $('.client-item-16 .client-thumb').on('mouseover', function (e) {
            var element = $(this).parent('.client-item-16');
            if (element.hasClass('open')) {
                element.removeClass('open');
                element.removeClass('active');
            } else {
                element.siblings('.client-item-16').find('.client-content').removeClass('active');
                element.siblings('.client-item-16').removeClass('active');
                element.children('.client-content').addClass('active');
                element.addClass('active');
            }
        });
        
        var pwa = function() {
            var getPath = relativePath => {
                if ("string" !== typeof relativePath) {
                    relativePath = '';
                }
                return window.location.origin + '/' + relativePath;
            };
            var workers = function() {
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register(getPath('pwa.js'), {
                        scope: getPath(),
                        useCache: true
                    }).then(function() {}).catch(function() {});
                }
            };
            var manifest = function() {
                var linkObject = document.getElementById('pwa_manifest');
                if (null === linkObject) {
                    return;
                }
                if (null !== linkObject.getAttribute('href')) {
                    return;
                }
                var myDynamicManifest = {
                    "description": 'Stephino RPG: browser-based MMORPG framework for play-to-earn games',
                    "short_name": 'Stephino RPG',
                    "name": "Stephino RPG",
                    "theme_color": "#8d1dbd",
                    "background_color": "#ffffff",
                    "dir": "ltr",
                    "lang": "en-US",
                    "start_url": getPath(),
                    "scope": getPath(),
                    "icons": [{
                        "src": getPath('assets/media/core/64.png'),
                        "sizes": "64x64",
                        "type": "image/png",
                        "purpose": "maskable any"
                    }, {
                        "src": getPath('assets/media/core/192.png'),
                        "sizes": "192x192",
                        "type": "image/png",
                        "purpose": "maskable any"
                    }, {
                        "src": getPath('assets/media/core/512.png'),
                        "sizes": "512x512",
                        "type": "image/png",
                        "purpose": "maskable any"
                    }],
                    "display": "fullscreen",
                    "orientation": "landscape"
                };
                var manifestURL = URL.createObjectURL(new Blob([JSON.stringify(myDynamicManifest)], {
                    type: 'application/json'
                }));
                linkObject.setAttribute('href', manifestURL);
            };
            workers();
            manifest();
        };
        pwa();
    });
})(jQuery);