/**
*
* File awcs-basic-single-product.js
*
* Loading single product contents
*
* Submitting product review
*
*/

(function ($) {

	// Single product selectors
	var single_product_selectors = 'ul.products li a.woocommerce-LoopProduct-link, ul.products li.product .shop-item-title-link, ul.products li.product .collection_title, ul.products li.product .products-title, ul.products li.product .woocommerce-loop-product__title, ul.products li .product_text a, ul.products li .product-inner .title a, ul.products li.outofstock a.button, ul.products li.product-type-variable a.button, ul.products li.product-type-grouped a.button, ul.products li a.woocommerce-loop-product__link, ul.products li a img, .awcs-ticker-single-product, form.woocommerce-cart-form .product-name a, form.woocommerce-cart-form img, .woocommerce .woocommerce-MyAccount-content .woocommerce-order-details .product-name a, .woocommerce .woocommerce-MyAccount-content .woocommerce-order-downloads .download-product a, .woocommerce-order .woocommerce-order-downloads .download-product a, .woocommerce-order .woocommerce-order-details .order_item .product-name a, .woocommerce-grouped-product-list-item a';

	// Append selectors from settings
	if( awcs_wc_global_params.single_product_selector ) {

		single_product_selectors += ', ';
		single_product_selectors += awcs_wc_global_params.single_product_selector;

	}

	// Load single product
    $(document).on('click awcs_single_product', single_product_selectors, function (e) {

        e.preventDefault();

        var $this = $(this);

        if ( $this.is('img') ) {
        	block( $this.parent() );
        } else {
        	block( $this );
        }

        if ( $this.closest('li').hasClass('product-type-simple') ) {
            var product_id = $this.closest('li').find('a.product_type_simple').data('product_id') ? $this.closest('li').find('a.product_type_simple').data('product_id') : '';
        } else if ( $this.closest('li').hasClass('product-type-variable') ) {
            var product_id = $this.closest('li').find('a.product_type_variable').data('product_id') ? $this.closest('li').find('a.product_type_variable').data('product_id') : '';
        } else if ( $this.closest('li').hasClass('product-type-grouped') ) {
            var product_id = $this.closest('li').find('a.product_type_grouped').data('product_id') ? $this.closest('li').find('a.product_type_grouped').data('product_id') : '';
        } else if ( $this.closest('li').hasClass('product-type-external') ) {
            var product_id = $this.closest('li').find('a.product_type_external').data('product_id') ? $this.closest('li').find('a.product_type_external').data('product_id') : '';
        } else {
        	var product_id = $this.attr('product-id') ? $this.attr('product-id') : $this.data('product_id');
        }
        
        if ( !product_id ) {
            if ( $this.closest('.cart_item').find('.product-remove a').data('product_id') ) {
            	var product_id = $this.closest('.cart_item').find('.product-remove a').data('product_id');
            } else if( $this.closest('li.product').find('a.add_to_cart_button').length && $this.closest('li.product').find('a.add_to_cart_button').data('product_id') ) {
		    	var product_id = $this.closest('li.product').find('a.add_to_cart_button').data('product_id');
		    } else if( $this.closest('li.product').find('a.product_type_grouped').length && $this.closest('li.product').find('a.product_type_grouped').data('product_id') ) {
		    	var product_id = $this.closest('li.product').find('a.product_type_grouped').data('product_id');
		    } else if( $this.closest('td.product-name').find('.awcs_order_item_product_id').length ) {
		        var product_id = $this.closest('td.product-name').find('.awcs_order_item_product_id').val();
		    } else if( $this.closest('li.product').find('.awcs_data_product_id').length ) {
		    	var product_id = $this.closest('li.product').find('.awcs_data_product_id').val();
		    } else if( $this.closest('.woocommerce-grouped-product-list-item').length ) {
		    	var grouped_product_id = $this.closest('.woocommerce-grouped-product-list-item').attr('id');
		    	if( grouped_product_id.length )
		    		var product_id = grouped_product_id.split('product-')[1];
		    }
        }

        if ( !product_id ) {
		    alert( "Product ID not found." );
		    return;
		}

		if( $this.attr('href') && $this.attr('href').indexOf('attribute_') >= 0 ) {

			var data = {
	            action: 'awcs_wc_single_product',
	            'product_id': product_id,
			    'product_url': $this.attr('href')
	        };		    

		} else {

			var data = {
	            action: 'awcs_wc_single_product',
	            'product_id': product_id
	        };

		}        

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: data,
            beforeSend: function() {
                awcs_set_target_element( awcs_get_target_element() );
            },
            success: function (response) {

                if (response) {

                    var body_classes = ' product-template-default single single-product woocommerce woocommerce-page singular ';

                    awcs_insert_body_classes( body_classes );

                    awcs_target.html(response).promise().done( function() {

                    	document.title = $('.awcs_wc_single_product_wrapper').find('.product_title').first().text() + ' - ' + awcs_wc_global_params.page_title;

                    	if( awcs_wc_basic_single_product_params.override_single_product ) {
                    		awcs_load_single_product();
                    	}

	                    awcs_wc_single_product();

	                    if ( $this.closest('li').hasClass('product-type-variable') || $('.awcs_wc_single_product_wrapper').find( 'form.variations_form' ).length )
	                        awcs_hide_variation_data( $('.awcs_wc_single_product_wrapper').find( 'form.variations_form' ) );

	                    if ( $this.closest('.product-name').find('.variation') || ( $this.attr('href') && $this.attr('href').indexOf('attribute_') >= 0 ) ) {

		                    var attributes_str = $this.attr('href') && $this.attr('href').indexOf('/?') >= 0 ? $this.attr('href').split('/?')[1] : '';
		                    var attributes = attributes_str && attributes_str.indexOf('&') >= 0 ? attributes_str.split('&') : attributes_str;

		                    if ( attributes ) {

			                    for( var key in attributes ) {
			                        var attribute = attributes[key].split('=')[1];
			                        $('.awcs_wc_single_product_wrapper').find( 'form.variations_form select:eq('+ key +')' ).val( attribute );
			                    }

			                    $('.awcs_wc_single_product_wrapper').find( 'form.variations_form select' ).change();

			                    if ( $('.awcs_wc_single_product_wrapper').find( 'a.reset_variations' ).css( 'visibility' ) === 'hidden' ) {
									$('.awcs_wc_single_product_wrapper').find( 'a.reset_variations' ).css( 'visibility', 'visible' ).hide().fadeIn();
								}
			                        
		                    }

	                    }

                    });

                } else {
                    alert('No more product available.');        
                }

            },
            complete: function() {
                
                if ( $this.is('img') ) {
		        	unblock( $this.parent() );
		        } else {
		        	unblock( $this );
		        }

		        if( $(document).find('.woocommerce-breadcrumb').length > 1 )
		        	$(document).find('.woocommerce-breadcrumb').last().hide();

                $.scroll_to_notices( $( '.entry-title, .page-title, .awcs_single_product_frame' ) );
            }

        });

        return false;

    });


    // Submit Review - single product page
    $(document).on('click', '#respond #submit', function(e) {

        e.preventDefault();

        var $this = $(this);

        var $form = $this.closest('form');
        
        $form.validate();
        
        if ( ! $form.valid() ) {
            return false;
        }

        block( $(document).find('#respond #submit') );
        block( $form );
        
        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            success: function(response) {

                var product_id = $form.find('#comment_post_ID').val();

                var data = {
                    action: 'awcs_wc_single_product',
                    'product_id': product_id
                };

                $.ajax({
                    type: 'post',
                    url: awcs_wc_global_params.ajax_url,
                    data: data,
                    beforeSend: function() {
		                awcs_set_target_element( awcs_get_target_element() );
		            },
                    success: function (response) {

                    	if (response) {
	                        var single_product = $('<div></div>');
	                        single_product.html( response );

	                        var review_tab_text = $( '.woocommerce-tabs li#tab-title-reviews a', single_product ).text();
	                        var rating_html = $( 'div.woocommerce-product-rating', single_product ).first();
	                        var reviews_html = $( 'div#comments', single_product );

							if( $this.closest('.woocommerce-tabs').length ) {

								$this.closest('.woocommerce-tabs').find('li#tab-title-reviews a').text( review_tab_text ).hide().fadeIn('medium');
	                        	$this.closest('.awcs_wc_single_product_wrapper').find('div.woocommerce-product-rating').first().replaceWith( rating_html ).hide().fadeIn('medium');
	                        	$this.closest('.woocommerce-tabs').find('#comments').replaceWith( reviews_html ).hide().fadeIn('medium');

							} else {

								$this.closest('.awcs_wc_single_product_wrapper').find('.woocommerce-tabs li#tab-title-reviews a').text( review_tab_text ).hide().fadeIn('medium');
	                        	$this.closest('.awcs_wc_single_product_wrapper').find('div.woocommerce-product-rating').first().replaceWith( rating_html ).hide().fadeIn('medium');
	                        	$this.closest('.awcs_wc_single_product_wrapper').find('#comments').replaceWith( reviews_html ).hide().fadeIn('medium');
							
							}
	                        
	                    }

                    }

                });

            },
            complete: function() {
                unblock( $form );
                unblock( $(document).find('#respond #submit') );
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
		        	$(document).find('.woocommerce-breadcrumb').last().hide();
                $.scroll_to_notices( $( '.woocommerce-tabs ul.tabs' ) );
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
				var $star   	= $( this ),
					$rating 	= $( this ).closest( '#respond' ).find( '#rating' ),
					$container 	= $( this ).closest( '.stars' );

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