/*!--
Author: Nagella Desaiah
email: 
URL: http://www.kavayahsolutions.com
--*/
require([
    'jquery',
    'ksPopup',
    'js/slick',
    'js/gsap',
    'js/flip'
], function ($, kp) {
    


    $(document).ready(function () {

        var deleteLink = document.querySelector('.delete');

        const grid = document.querySelector(".popular-container");
        const items = gsap.utils.toArray(".popular-container .card");
        const newgrid = document.querySelector(".new-arrival");
        const newitems = gsap.utils.toArray(".new-arrival .card");
        const salesgrid = document.querySelector(".sales");
        const salesitems = gsap.utils.toArray(".sales .card");

        document.querySelector(".bestseller-title").addEventListener("click", () => {
        var activeclass = $(".bestseller-title").data('name');
        if (!$(".bestseller-title").hasClass('selected')) {
            $('.bestselling-items').children().removeClass('active');
            $('.top-selling.product').children().removeClass('selected');
            $(".bestseller-title").addClass('selected');
            $('.'+ activeclass).addClass('active');
            // Get the state
            const state = Flip.getState([items, grid]);  
            // Do the actual shuffling
            for(let i = items.length; i >= 0; i--) {
                grid.appendChild(grid.children[Math.random() * i | 0]);
            }
            
            // Animate the change
            Flip.from(state, {
                absolute: items
            });
        }
        });
        document.querySelector(".newarrival-title").addEventListener("click", () => {
            var activeclass = $(".newarrival-title").data('name');
            if (!$(".newarrival-title").hasClass('selected')) {
                $('.bestselling-items').children().removeClass('active');
                $('.top-selling.product').children().removeClass('selected');
                $(".newarrival-title").addClass('selected');
                $('.'+ activeclass).addClass('active');
                // Get the state
                const state = Flip.getState([newitems, newgrid]);  
                // Do the actual shuffling
                for(let i = newitems.length; i >= 0; i--) {
                    newgrid.appendChild(newgrid.children[Math.random() * i | 0]);
                }
                
                // Animate the change
                Flip.from(state, {
                    absolute: newitems
                });
            }
            });
            document.querySelector(".sales-title").addEventListener("click", () => {
                var activeclass = $(".sales-title").data('name');
                if (!$(".sales-title").hasClass('selected')) {
                    $('.bestselling-items').children().removeClass('active');
                    $('.top-selling.product').children().removeClass('selected');
                    $(".sales-title").addClass('selected');
                    $('.'+ activeclass).addClass('active');
                    // Get the state
                    const state = Flip.getState([salesitems, salesgrid]);  
                    // Do the actual shuffling
                    for(let i = salesitems.length; i >= 0; i--) {
                        salesgrid.appendChild(salesgrid.children[Math.random() * i | 0]);
                    }
                    
                    // Animate the change
                    Flip.from(state, {
                        absolute: salesitems
                    });
                }
                });

        /**
         * New Home Page Slider
         */
            
            $('.main-slider').slick({

                // dots: true,
                // prevArrow: '<div class="slide-arrow prev-arrow"></div>',
                // nextArrow: '<div class="slide-arrow next-arrow"></div>',
                arrows: false,
                infinite: true,
                autoplay: false,
                pauseOnFocus: false,
                pauseOnHover: false,
                autoplaySpeed: 5000,
                speed:1000,
                fade:true,
                slidesToShow: 1,
                cssEase: 'linear',
                slidesToScroll: 1,
            }); 

            $('.product-container').slick({

                prevArrow: false,
                nextArrow: false,
                speed: 300,
                slidesToShow: 1,
                autoplay: false,
                // initialSlide: 4,
                slidesToScroll: 4,
                infinite: true,
                // adaptiveHeight: true,
                draggable: true,
                variableWidth: true,
                // centerMode: true 
            }); 
    });


    $(window).scroll(function () {
        if ($(window).width() <= 1024) { //extending to 1024px
            $('.header.content').removeClass('fixed-header');
        } else {
            if ($(window).scrollTop() > 35) {
                $('.header.content').addClass('fixed-header');
            }else if ($(window).scrollTop() < 35)  {             
                $('.header.content').removeClass('fixed-header');             
            }
        }
    });

});
