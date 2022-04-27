'use strict';

require('bxslider/dist/jquery.bxslider');
require('@fancyapps/fancybox');
require('../../../libs/starrating/js/rating.js');
require('../../../libs/lightslider/js/lightslider.js');
require('jquery-bootstrap-scrolling-tabs/dist/jquery.scrolling-tabs.min.js');

function initSearchBox() {
    var $formSearch = $('#form-search');
    var $searchField = $('.search-field');

    $searchField.keypress(function (e) {
        if (e.which == 13) {
            e.preventDefault();

            if ($searchField.val() === '') {
                $searchField.focus();
            } else {
                $formSearch.submit();
            }
        }
    });
}

function initProtectedContent() {
    $('body').bind('cut copy', function (e) {
        e.preventDefault();
    });
}

function initGoToTop() {
    var $goToTop = $('.go-to-top');

    $goToTop.click(function() {
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });
}

function initProjectHotSlider() {
    $('.bxslider').show().bxSlider({
        auto: true,
        autoControls: false,
        stopAutoOnClick: true,
        pager: false,
        controls: true,
        minSlides: 1,
        maxSlides: 4,
        moveSlides: 1,
        slideMargin: 20,
        touchEnabled: false,
        autoHover: true,
        adaptiveHeight: true
    });
}

function initNewsSlider() {
    $('.post-sidebar-bxslider').bxSlider({
        mode: 'vertical',
        auto: true,
        speed: 300,
        autoControls: false,
        stopAutoOnClick: true,
        pager: false,
        controls: false,
        minSlides: 10,
        maxSlides: 10,
        moveSlides: 1,
        slideWidth: 375,
        touchEnabled: false,
        autoHover: true
    });
}

function initFixedMenu() {
    $(window).scroll(function() {
        var $nav = $("#nav");
        var $scrollUp = $('.td-scroll-up');
        var scroll = $(window).scrollTop();
    
        if (scroll >= 139) {
            $nav.addClass("navbar-fixed-top");
            $scrollUp.removeClass("hidden");
        } else {
            $nav.removeClass("navbar-fixed-top");
            $scrollUp.addClass("hidden");
        }
    });
}

function initFixedSidebar() {
    $(window).scroll(function() {
        var $sidebar = $("#sidebar .sidebar"),
            $pageDetail = $('.wrapper-post-container'),
            $pageDetailLeft = $('.wrapper-post-container-left'),
            scrollTop = $(this).scrollTop(),
            pageDetailHeight =  $pageDetail.outerHeight(),
            pageDetailLeftHeight =  $pageDetailLeft.outerHeight(),
            sidebarHeight = $sidebar.height(),
            parentSidebarWidth = $sidebar.parent('.col-md-12').width(),
            positionFixedMax = pageDetailHeight - sidebarHeight,
            positionFixed = scrollTop < 65 ? 65 : positionFixedMax > scrollTop ? 65 : positionFixedMax - scrollTop ;
        
        if (pageDetailLeftHeight > sidebarHeight) {
            if (scrollTop > 220) {
                $sidebar.css({
                    'top': positionFixed,
                    'position': 'fixed',
                    'width': parentSidebarWidth
                });
            } else {
                $sidebar.removeAttr("style");
            }
        }
    });
}

function initCostConstruction() {
    var $formType = $('.costs #form_type');
    var $formFloor = $('.costs #form_floor');

    if ($formType.val() == 3) {
        $formFloor.val(1);
        $formFloor.attr('disabled', 'disabled');
    }

    $formType.change(function(e) {
        if ($(this).val() == 3) {
            $formFloor.val(1);
            $formFloor.attr('disabled', 'disabled');
        } else {
            $formFloor.removeAttr('disabled');
        }
    })
}

function initFancybox() {
    var $rating = $('.rating-container .rating');
    var $ratingMessage = $('p.rating-message');
    var $star = $('#form-rating-review .rating-well .star');
    var $formRating = $('#form-rating-review');

    $rating.click(function() {
        $formRating.show();
        $ratingMessage.html('');
        
        $.fancybox.open({
            src: '#form-rating-container',
            touch : false
        });

        return false;
    });

    $('a#rating').click(function(e) {
        e.preventDefault();

        $formRating.show();
        $ratingMessage.html('');
        
        $.fancybox.open({
            src: '#form-rating-container',
            touch : false
        });

        return false;
    });

    $star.on('click', function(e) {
        var rating = $(this).data('value');
        var newsId = $formRating.data('newsId');

        $.ajax({
            type: "POST",
            url: $formRating.attr('action'),
            data: 'rating=' + rating + '&newsId=' + newsId,
            success: function(data) {
                var response = JSON.parse(data);
                
                if (response.status === 'success') {
                    $formRating.hide();
                    $ratingMessage.html(response.message);
                }
            }
        });
    });
}

function initFlickity() {
    $('#image-gallery-3').lightSlider({
        item: 3,
        loop: true,
        slideMove: 1,
        easing: 'cubic-bezier(0.25, 0, 0.25, 1)',
        speed: 600,
        responsive : [
            {
                breakpoint: 800,
                settings: {
                    item: 2,
                    slideMove: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    item: 1,
                    slideMove: 1
                }
            }
        ]
    });
}

exports.init = function () {
    initSearchBox();
    initProjectHotSlider();
    initProtectedContent();
    initGoToTop();
    initFixedMenu();
    initCostConstruction();
    initFlickity();

    $('.nav-tabs').scrollingTabs();
};