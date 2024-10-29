/**
*
* File awcs-basic-wc-page-contents.js
*
* Finding specific content for shop / cart / checkout / my account pages
*
*/

(function ($) {

    $( window.top.document ).on("click", "#place_order", function(e) {
        block( $(this) );
    });

    // Load account page
    $(document).on('click', 'a[href="'+ awcs_wc_global_params.account_url +'"]', function (e) {

        e.preventDefault();

        var $this = $(this);

        var data = {
            action: 'awcs_wc_view_account'
        };

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: data,
            beforeSend: function() {
                awcs_set_target_element( awcs_get_target_element() );
                block( awcs_target );
                block( $this );
            },
            success: function (response) {

                var body_classes = ' page-template-default page woocommerce-account woocommerce-page singular ';

                awcs_insert_body_classes( body_classes );

                awcs_target.html(response).promise().done(function() {
                    var pm_link = $(document).find('li.woocommerce-MyAccount-navigation-link--payment-methods a');
                    if( pm_link.length ) {
                        pm_link.attr( 'href', pm_link.attr('href') + '?referrer=pm' );
                    }
                });

            },
            complete: function(response) {
                document.title = 'My account - ' + awcs_wc_global_params.page_title;
                unblock( awcs_target );
                unblock( $this );
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
                    $(document).find('.woocommerce-breadcrumb').last().hide();
                $.scroll_to_notices( $( '.entry-title, .page-title' ) );
            }
        });

        return false;
        
    });

    // Load shop page
    $(document).on('click', 'a[href="'+awcs_wc_global_params.shop_url+'"], .return-to-shop a, .awcs_return_to_shop_page, .woocommerce .woocommerce-MyAccount-content .woocommerce-Message--info a.woocommerce-Button:contains(Browse products)', function (e) {

        e.preventDefault();

        var $this = $(this);

        var data = {
            action: 'awcs_wc_view_shop'
        };

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: data,
            beforeSend: function() {
                awcs_set_target_element( awcs_get_target_element() );
                block( awcs_target );
                block( $this );
            },
            success: function (response) {

                // Insert body classes
                var body_classes = ' page-template-default page woocommerce woocommerce-page ';

                awcs_insert_body_classes( body_classes );

                awcs_target.html(response).promise().done(function() {

                    // Add prev button
                    $(document).find('nav.woocommerce-pagination ul.page-numbers').prepend('<li><a class="prev page-numbers" href="">←</a></li>');
                    $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev').hide();

                    // Current Page - Replace span with link
                    var previous = $(document).find('nav.woocommerce-pagination ul.page-numbers li span[aria-current="page"]');
                                    
                    if (previous) {
                        var text = $(previous).html();
                        $(previous).closest('li').html('<a class="page-numbers current" href="' + awcs_wc_global_params.home_url + 'wp-admin/admin-ajax.php?product-page='+text+'">'+text+'</a>');
                    }

                });

            },
            complete: function(response) {
                document.title = 'Products - ' + awcs_wc_global_params.page_title;
                unblock( awcs_target );
                unblock( $this );
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
                    $(document).find('.woocommerce-breadcrumb').last().hide();
                $.scroll_to_notices( $( '.entry-title, .page-title' ) );
            }

        });

        return false;
        
    });

    // Load cart page
    $(document).on('click', '.woocommerce-mini-cart__buttons a, a[href="'+awcs_wc_global_params.cart_url+'"]', function (e) {
       
        e.preventDefault();

        var $this = $(this);

        var data = {
            action: 'awcs_wc_view_cart'
        };

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: data,
            beforeSend: function() {
                awcs_set_target_element( awcs_get_target_element() );
                block( awcs_target );
                block( $this );
            },
            success: function (response) {

                var body_classes = ' page-template-default page woocommerce-cart woocommerce-page singular ';

                awcs_insert_body_classes( body_classes );

                awcs_target.html(response);

                jQuery(document.body).trigger('wc_fragment_refresh');

            },
            complete: function (response) {
                document.title = 'Cart - ' + awcs_wc_global_params.page_title;
                unblock( awcs_target );
                unblock( $this );
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
                    $(document).find('.woocommerce-breadcrumb').last().hide();
                $.scroll_to_notices( $( '.entry-title, .page-title' ) );               
            }

        });

        return false;
        
    });

    // Load checkout page from cart page, menu
    $(document).on('click', 'a[href="'+ awcs_wc_global_params.checkout_url +'"], .wc-proceed-to-checkout .checkout-button, .woocommerce-mini-cart__buttons a.checkout', function (e) {
        
        e.preventDefault();

        var $this = $(this);

        awcs_adjust_target_element( $this );

        block( $this );

        jQuery.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: { action: 'awcs_verify_checkout_content' },
            success: function (response) {

                block( awcs_target );

                var body_classes = ' page-template-default page woocommerce-checkout woocommerce-page singular ';

                awcs_insert_body_classes( body_classes );

                var checkout_heading = '<header class="woocommerce-products-header"><h1 class="woocommerce-products-header__title page-title">Checkout</h1></header>';
                    
                if( response.isCartEmpty ) {

                    awcs_target.html(checkout_heading + response.checkout).fadeIn("medium");

                } else {

                    var checkout_url = awcs_wc_global_params.checkout_url + '?referrer=awcs';
                                    
                    var checkout_page = '<div id="awcs_basic_checkout_wrapper" style="display: block; visibility: hidden;">';
                        checkout_page += '<iframe id="awcs_checkout_iframe" src="' + checkout_url + '" frameborder="0" allowtransparency="true" scrolling="no" allowpaymentrequest="true" data-origwidth="" data-origheight="" style="width: 100%; height: 0px;"></iframe>';
                        checkout_page += '</div>';

                    if( awcs_target.find('#awcs_basic_checkout_wrapper #awcs_checkout_iframe').length ) {

                        unblock( awcs_target );

                    } else {

                        if( $this.is('a[href="'+ awcs_wc_global_params.checkout_url +'"]') ) {

                            var loading_image = '';
                            
                            if( awcs_wc_global_params.single_loading_img ) {
                                loading_image = awcs_wc_global_params.single_loading_img;
                            }

                            awcs_target.html( '<div class="awcs_loading_div" style="text-align: center;">' + loading_image + '</div>' );
                        }

                        awcs_target.append( checkout_page );

                    }
                                
                    awcs_target.find('#awcs_checkout_iframe').on('load', function() {
                        awcs_target.find('.awcs_loading_div').hide();
                        awcs_checkout_content(); 
                    });
                        
                }
            
            },
            complete: function (response) {
                unblock( $this );
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
                    $(document).find('.woocommerce-breadcrumb').last().hide();
            }

        });

        document.title = 'Checkout - ' + awcs_wc_global_params.page_title;
        return false;
    });

    // Order again from checkout order confirmation
    jQuery(document).on('click', '.woocommerce-order .woocommerce-order-details .order-again a', function(e) {
                                
        e.preventDefault();

        var $target = jQuery(this);

        block( $target );

        jQuery.ajax({
            type: 'GET',
            url: $target.attr('href'),
            dataType: 'html',
            beforeSend: function() {
                awcs_set_target_element( awcs_get_target_element() );
            },
            success:  function( response ) {
                                            
                var data = {
                    action: 'awcs_order_again_view_cart'
                };

                jQuery.ajax({
                    type: 'post',
                    url: awcs_wc_global_params.ajax_url,
                    data: data,
                    success: function (response) {
                        jQuery( '.woocommerce-error, .woocommerce-message, .woocommerce-info' ).remove();
                                        
                        awcs_target.html( response ).fadeIn();

                        jQuery(document.body).trigger('wc_fragment_refresh');
                    },
                    complete: function() {
                        jQuery.scroll_to_notices( jQuery( '[role="alert"]' ) );
                    }
                });

            },
            complete: function() {
                unblock( $content );
                unblock( $target );
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
                    $(document).find('.woocommerce-breadcrumb').last().hide();
            }

        });

    });

    // Single product woocommerce js
    function awcs_wc_single_product() {

        // Exit if single product script already exists
        if ( wc_single_product_params ) {
            return;
        }

        var wc_single_product_params = { "i18n_required_rating_text": "Please select a rating", "review_rating_required": "yes", "flexslider": { "rtl":false, "animation":"slide", "smoothHeight":true, "directionNav":false, "controlNav":"thumbnails", "slideshow":false, "animationSpeed":500, "animationLoop":false, "allowOneSlide":false }, "zoom_enabled":"1", "zoom_options":[], "photoswipe_enabled":"1", "photoswipe_options": { "shareEl":false, "closeOnScroll":false, "history":false, "hideAnimationDuration":0, "showAnimationDuration":0 }, "flexslider_enabled":"1" };

        $(document)
            // Tabs
            .on( 'init', '.wc-tabs-wrapper, .woocommerce-tabs', function() {
                $( this ).find( '.wc-tab, .woocommerce-tabs .panel:not(.panel .panel)' ).hide();

                var hash  = window.location.hash;
                var url   = window.location.href;
                var $tabs = $( this ).find( '.wc-tabs, ul.tabs' ).first();

                if ( hash.toLowerCase().indexOf( 'comment-' ) >= 0 || hash === '#reviews' || hash === '#tab-reviews' ) {
                    $tabs.find( 'li.reviews_tab a' ).click();
                } else if ( url.indexOf( 'comment-page-' ) > 0 || url.indexOf( 'cpage=' ) > 0 ) {
                    $tabs.find( 'li.reviews_tab a' ).click();
                } else if ( hash === '#tab-additional_information' ) {
                    $tabs.find( 'li.additional_information_tab a' ).click();
                } else {
                    $tabs.find( 'li:first a' ).click();
                }
            } )
            .on( 'click', '.wc-tabs li a, ul.tabs li a', function( e ) {
                e.preventDefault();
                var $tab          = $( this );
                var $tabs_wrapper = $tab.closest( '.wc-tabs-wrapper, .woocommerce-tabs' );
                var $tabs         = $tabs_wrapper.find( '.wc-tabs, ul.tabs' );

                $tabs.find( 'li' ).removeClass( 'active' );
                $tabs_wrapper.find( '.wc-tab, .panel:not(.panel .panel)' ).hide();

                $tab.closest( 'li' ).addClass( 'active' );
                $tabs_wrapper.find( $tab.attr( 'href' ) ).show();
            } )
            // Review link
            .on( 'click', 'a.woocommerce-review-link', function() {
                $( '.reviews_tab a' ).click();
                return true;
            } )
            // Star ratings for comments
            .on( 'init', '#rating', function() {
                if ( $(document).find('p.stars').length ) {

                } else {
                    $( '#rating' )
                        .hide()
                        .before(
                            '<p class="stars">\
                                <span>\
                                    <a class="star-1" href="#">1</a>\
                                    <a class="star-2" href="#">2</a>\
                                    <a class="star-3" href="#">3</a>\
                                    <a class="star-4" href="#">4</a>\
                                    <a class="star-5" href="#">5</a>\
                                </span>\
                            </p>'
                        );
                }
            } )
            .on( 'click', '#respond p.stars a', function() {
                var $star       = $( this ),
                    $rating     = $( this ).closest( '#respond' ).find( '#rating' ),
                    $container  = $( this ).closest( '.stars' );

                $rating.val( $star.text() );
                $star.siblings( 'a' ).removeClass( 'active' );
                $star.addClass( 'active' );
                $container.addClass( 'selected' );

                return false;
            } )
            .on( 'click', '#respond #submit', function() {
                var $rating = $( this ).closest( '#respond' ).find( '#rating' ),
                    rating  = $rating.val();

                if ( $rating.length > 0 && ! rating && wc_single_product_params.review_rating_required === 'yes' ) {
                    window.alert( wc_single_product_params.i18n_required_rating_text );

                    return false;
                }
            } );

        // Init Tabs and Star Ratings
        $(document).find( '.wc-tabs-wrapper, .woocommerce-tabs, #rating' ).trigger( 'init' );

        /**
         * Product gallery class.
         */
        var ProductGallery = function( $target, args ) {
            this.$target = $target;
            this.$images = $( '.woocommerce-product-gallery__image', $target );

            // No images? Abort.
            if ( 0 === this.$images.length ) {
                this.$target.css( 'opacity', 1 );
                return;
            }

            // Make this object available.
            $target.data( 'product_gallery', this );

            // Pick functionality to initialize...
            this.flexslider_enabled = $.isFunction( $.fn.flexslider ) && wc_single_product_params.flexslider_enabled;
            this.zoom_enabled       = $.isFunction( $.fn.zoom ) && wc_single_product_params.zoom_enabled;
            this.photoswipe_enabled = typeof PhotoSwipe !== 'undefined' && wc_single_product_params.photoswipe_enabled;

            // ...also taking args into account.
            if ( args ) {
                this.flexslider_enabled = false === args.flexslider_enabled ? false : this.flexslider_enabled;
                this.zoom_enabled       = false === args.zoom_enabled ? false : this.zoom_enabled;
                this.photoswipe_enabled = false === args.photoswipe_enabled ? false : this.photoswipe_enabled;
            }

            // ...and what is in the gallery.
            if ( 1 === this.$images.length ) {
                this.flexslider_enabled = false;
            }

            // Bind functions to this.
            this.initFlexslider       = this.initFlexslider.bind( this );
            this.initZoom             = this.initZoom.bind( this );
            this.initZoomForTarget    = this.initZoomForTarget.bind( this );
            this.initPhotoswipe       = this.initPhotoswipe.bind( this );
            this.onResetSlidePosition = this.onResetSlidePosition.bind( this );
            this.getGalleryItems      = this.getGalleryItems.bind( this );
            this.openPhotoswipe       = this.openPhotoswipe.bind( this );

            if ( this.flexslider_enabled ) {
                this.initFlexslider( args.flexslider );
                $target.on( 'woocommerce_gallery_reset_slide_position', this.onResetSlidePosition );
            } else {
                this.$target.css( 'opacity', 1 );
            }

            if ( this.zoom_enabled ) {
                this.initZoom();
                $target.on( 'woocommerce_gallery_init_zoom', this.initZoom );
            }

            if ( this.photoswipe_enabled ) {
                this.initPhotoswipe();
            }
        };

        /**
         * Initialize flexSlider.
         */
        ProductGallery.prototype.initFlexslider = function( args ) {
            var $target = this.$target,
                gallery = this;

            var options = $.extend( {
                selector: '.woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image',
                start: function() {
                    $target.css( 'opacity', 1 );
                },
                after: function( slider ) {
                    gallery.initZoomForTarget( gallery.$images.eq( slider.currentSlide ) );
                }
            }, args );

            $target.flexslider( options );

            // Trigger resize after main image loads to ensure correct gallery size.
            $( '.woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image:eq(0) .wp-post-image' ).one( 'load', function() {
                var $image = $( this );

                if ( $image ) {
                    setTimeout( function() {
                        var setHeight = $image.closest( '.woocommerce-product-gallery__image' ).height();
                        var $viewport = $image.closest( '.flex-viewport' );

                        if ( setHeight && $viewport ) {
                            $viewport.height( setHeight );
                        }
                    }, 100 );
                }
            } ).each( function() {
                if ( this.complete ) {
                    $( this ).trigger( 'load' );
                }
            } );
        };

        /**
         * Init zoom.
         */
        ProductGallery.prototype.initZoom = function() {
            this.initZoomForTarget( this.$images.first() );
        };

        /**
         * Init zoom.
         */
        ProductGallery.prototype.initZoomForTarget = function( zoomTarget ) {
            if ( ! this.zoom_enabled ) {
                return false;
            }

            var galleryWidth = this.$target.width(),
                zoomEnabled  = false;

            $( zoomTarget ).each( function( index, target ) {
                var image = $( target ).find( 'img' );

                if ( image.data( 'large_image_width' ) > galleryWidth ) {
                    zoomEnabled = true;
                    return false;
                }
            } );

            // But only zoom if the img is larger than its container.
            if ( zoomEnabled ) {
                var zoom_options = $.extend( {
                    touch: false
                }, wc_single_product_params.zoom_options );

                if ( 'ontouchstart' in document.documentElement ) {
                    zoom_options.on = 'click';
                }

                zoomTarget.trigger( 'zoom.destroy' );
                zoomTarget.zoom( zoom_options );

                setTimeout( function() {
                    if ( zoomTarget.find(':hover').length ) {
                        zoomTarget.trigger( 'mouseover' );
                    }
                }, 100 );
            }
        };

        /**
         * Init PhotoSwipe.
         */
        ProductGallery.prototype.initPhotoswipe = function() {
            if ( this.$target.find('a.woocommerce-product-gallery__trigger').length < 1 && this.zoom_enabled && this.$images.length > 0 ) {
                this.$target.prepend( '<a href="#" class="woocommerce-product-gallery__trigger">🔍</a>' );
                this.$target.on( 'click', '.woocommerce-product-gallery__trigger', this.openPhotoswipe );
                this.$target.on( 'click', '.woocommerce-product-gallery__image a', function( e ) {
                    e.preventDefault();
                });

                // If flexslider is disabled, gallery images also need to trigger photoswipe on click.
                if ( ! this.flexslider_enabled ) {
                    this.$target.on( 'click', '.woocommerce-product-gallery__image a', this.openPhotoswipe );
                }
            } else {
                this.$target.on( 'click', '.woocommerce-product-gallery__image a', this.openPhotoswipe );
            }
        };

        /**
         * Reset slide position to 0.
         */
        ProductGallery.prototype.onResetSlidePosition = function() {
            this.$target.flexslider( 0 );
        };

        /**
         * Get product gallery image items.
         */
        ProductGallery.prototype.getGalleryItems = function() {
            var $slides = this.$images,
                items   = [];

            if ( $slides.length > 0 ) {
                $slides.each( function( i, el ) {
                    var img = $( el ).find( 'img' );

                    if ( img.length ) {
                        var large_image_src = img.attr( 'data-large_image' ),
                            large_image_w   = img.attr( 'data-large_image_width' ),
                            large_image_h   = img.attr( 'data-large_image_height' ),
                            alt             = img.attr( 'alt' ),
                            item            = {
                                alt  : alt,
                                src  : large_image_src,
                                w    : large_image_w,
                                h    : large_image_h,
                                title: img.attr( 'data-caption' ) ? img.attr( 'data-caption' ) : img.attr( 'title' )
                            };
                        items.push( item );
                    }
                } );
            }

            return items;
        };

        /**
         * Open photoswipe modal.
         */
        ProductGallery.prototype.openPhotoswipe = function( e ) {
            e.preventDefault();

            var pswpElement = $( '.pswp' )[0],
                items       = this.getGalleryItems(),
                eventTarget = $( e.target ),
                clicked;

            if ( eventTarget.is( '.woocommerce-product-gallery__trigger' ) || eventTarget.is( '.woocommerce-product-gallery__trigger img' ) ) {
                clicked = this.$target.find( '.flex-active-slide' );
            } else {
                clicked = eventTarget.closest( '.woocommerce-product-gallery__image' );
            }

            var options = $.extend( {
                index: $( clicked ).index(),
                addCaptionHTMLFn: function( item, captionEl ) {
                    if ( ! item.title ) {
                        captionEl.children[0].textContent = '';
                        return false;
                    }
                    captionEl.children[0].textContent = item.title;
                    return true;
                }
            }, wc_single_product_params.photoswipe_options );

            // Initializes and opens PhotoSwipe.
            var photoswipe = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options );
            photoswipe.init();
        };

        /**
         * Function to call wc_product_gallery on jquery selector.
         */
        $.fn.wc_product_gallery = function( args ) {
            new ProductGallery( this, args || wc_single_product_params );
            return this;
        };

        /*
         * Initialize gallery.
         */

        $( '.woocommerce-product-gallery:eq(0)' ).trigger( 'wc-product-gallery-before-init', [ $( '.woocommerce-product-gallery:eq(0)' ), wc_single_product_params ] );

        $( '.woocommerce-product-gallery:eq(0)' ).wc_product_gallery( wc_single_product_params );

        $( '.woocommerce-product-gallery:eq(0)' ).trigger( 'wc-product-gallery-after-init', [ $( '.woocommerce-product-gallery:eq(0)' ), wc_single_product_params ] );

    }

})(jQuery);

function awcs_checkout_content() {

    var iframe = document.getElementById("awcs_checkout_iframe");

    iframe.contentWindow.document.body.style.backgroundColor = jQuery('#awcs_checkout_iframe').closest('#awcs_basic_checkout_wrapper').css("background-color");
    iframe.contentWindow.document.body.style.backgroundImage = 'none';
    
    iframe.style.height = ( iframe.contentWindow.document.body.scrollHeight + 20 ) + 'px';
    iframe.style.width = '100%';

    var frame_contents = jQuery('#awcs_checkout_iframe').contents();

    if(  frame_contents.find('.awcs-product-ticker-header, .awcs-product-ticker-footer').length )
        frame_contents.find('.awcs-product-ticker-header, .awcs-product-ticker-footer').hide();

    var order_info = frame_contents.find('.woocommerce-order-details .order_details .product-name a');

    if( order_info.length ) {
        frame_contents.find( '.woocommerce-order .woocommerce-order-downloads .download-product a, .woocommerce-order .woocommerce-order-details .order_item .product-name a' ).addClass( 'awcs_sps_single_product' );
        awcs_target.html( '<div id="awcs_basic_order_confirmation">' + frame_contents.find('body .awcs_order_received_frame').html() + '</div>' ).fadeIn("medium");
        jQuery.scroll_to_notices( awcs_target.find( 'h1.page-title' ) );
        awcs_target.find('#awcs_basic_order_confirmation').css({ "font-size": "25px" });
    }

    jQuery('#awcs_basic_checkout_wrapper').siblings().remove();

    jQuery('#awcs_basic_checkout_wrapper').css({ "visibility": "visible" });

    unblock( awcs_target );

    var $form_coupon_toggle = frame_contents.find('.woocommerce-form-coupon-toggle');

    var frame_orig_height = iframe.contentWindow.document.body.scrollHeight;

    $form_coupon_toggle.on('click', '.showcoupon', function() {
        var coupon_form_height = frame_contents.find('form.checkout_coupon').height();
        var frame_height = frame_orig_height + coupon_form_height + 50;
        iframe.style.height = frame_height + 'px';
    });

    var $form = frame_contents.find('form.checkout');

    $form.on('click', 'li.woocommerce-SavedPaymentMethods-new input[type="radio"]', function() {
        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
    });

    $form.on('submit', function(e) {

        block( $form.find('#place_order') );
        clearInterval(timer);

        if( ! awcs_target.find( '#awcs_processing_order_wrapper' ).length ) {
            if( frame_contents.find('form.woocommerce-checkout ul.woocommerce-error li').length ) {
                frame_contents.find('form.woocommerce-checkout ul.woocommerce-error').remove();
            }
            awcs_target.prepend( '<header id="awcs_processing_order_wrapper" class="woocommerce-products-header"><h1 class="woocommerce-products-header__title page-title"> Processing Order <span id="awcs_processing_order" style="margin-left: 15px;">' + awcs_wc_global_params.single_loading_img + '</span> </h1></header>' );
            iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
            jQuery.scroll_to_notices( awcs_target.find( '.page-title' ) );
        }

        var timer = setInterval(function() {
            if( frame_contents.find('form.woocommerce-checkout ul.woocommerce-error li').length ) {
                iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
                frame_orig_height = iframe.contentWindow.document.body.scrollHeight;
                unblock( $form.find('#place_order') );
                if( awcs_target.find( '#awcs_processing_order_wrapper' ).length ) {
                    awcs_target.find( '#awcs_processing_order_wrapper' ).remove();
                }
                clearInterval(timer);
            }
        }, 500);

    });

    jQuery.scroll_to_notices( frame_contents.find( '.page-title' ) );

    jQuery('#awcs_checkout_iframe').attr('scrolling', 'yes');

}