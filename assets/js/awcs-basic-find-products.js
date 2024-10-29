/**
*
* File awcs-basic-find-products.js
*
* This script is for finding products by Sorting, Paging, using Category / Tag
*
*/

(function ($) {

    // Prevent normal form submission
    $(document).find('form.woocommerce-ordering').submit(function(e) {
        e.preventDefault();
    });

    // Change Products By Sort Order
    $(document).on('change', 'form.woocommerce-ordering select.orderby', function(e) {

        e.preventDefault();
                   
        var category = $(this).closest('form.woocommerce-ordering').find('input[name="category"]').length ? $(this).closest('form.woocommerce-ordering').find('input[name="category"]').val() : '';
        var tag = $(this).closest('form.woocommerce-ordering').find('input[name="tag"]').length ? $(this).closest('form.woocommerce-ordering').find('input[name="tag"]').val() : '';
        var orderby = $(this).find('option:selected').val();

        if( !category && window.location.href.indexOf("/product-category/") >= 0 ) {
            category = window.location.href.split("/product-category/")[1].split("/");
            if( category[ category.length - 1 ] ) {
                category = category[ category.length - 1 ];
            } else {
                category = category[ category.length - 2 ];
            }
        }

        if( !tag && window.location.href.indexOf("/product-tag/") >= 0 ) {
            tag = window.location.href.split("/product-tag/")[1].split("/");
            if( tag[ tag.length - 1 ] ) {
                tag = tag[ tag.length - 1 ];
            } else {
                tag = tag[ tag.length - 2 ];
            }
        }

        $(this).find('option:selected').attr("selected", "selected");

        var data = {
            action: 'awcs_wc_sorted_products',
            'orderby': orderby,
            'category': category,
            'tag': tag
        };

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: data,
            beforeSend: function() {
                awcs_set_target_element( awcs_get_target_element() );
                block( awcs_target );
            },
            success: function (response) {

                var catalog = $('<div></div>');
                catalog.html(response);

                var products = $( 'ul.products:first', catalog );

                awcs_target.find('ul.products').first().replaceWith(products).promise().done(function() {
                    $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev').hide();
                    $(document).find('nav.woocommerce-pagination ul.page-numbers li a.next').show();
                    $(document).find('input[name="paged"]').val(1);
                    $(document).find('nav.woocommerce-pagination ul.page-numbers li a.page-numbers').removeAttr('aria-current').removeClass('current');
                    $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev, nav.woocommerce-pagination ul.page-numbers li a.next').removeAttr('aria-current').removeClass('current');
                    $(document).find('nav.woocommerce-pagination ul.page-numbers li a.page-numbers:not(a.prev, a.next):first').attr("aria-current", "page").addClass("current");
                });

                $.ajax({
                    type: 'post',
                    url: awcs_wc_global_params.ajax_url,
                    data: { action: 'awcs_get_breadcrumb_with_page', 'category': category, 'tag': tag },
                    success: function (breadcrumb) {
                        $(document).find('nav.woocommerce-breadcrumb').replaceWith(breadcrumb);
                    }
                }); 

                $.ajax({
                    type: 'post',
                    url: awcs_wc_global_params.ajax_url,
                    data: { action: 'awcs_show_result_count', 'category': category, 'tag': tag },
                    success: function (result) {
                        $(document).find('p.woocommerce-result-count').replaceWith(result);
                    }
                });

                if(category) {
                    document.title = category + ' - ' + awcs_wc_global_params.page_title;
                } else if(tag) {
                    document.title = tag + ' - ' + awcs_wc_global_params.page_title;
                } else {
                    if( awcs_wc_global_params.page_title.indexOf('Products') >= 0 ) {
                        document.title = awcs_wc_global_params.page_title;
                    } else {
                        document.title = 'Products - ' + awcs_wc_global_params.page_title;
                    }
                }

            },
            complete: function() {
                $.scroll_to_notices( $( '.entry-title, .page-title' ) );
                unblock( awcs_target );
            }
        });

    });
    // End change products by sort order

    // Change Products By Paging
    $(document).on('click', 'nav.woocommerce-pagination ul.page-numbers li a.page-numbers, nav.woocommerce-pagination ul.page-numbers li a.prev, nav.woocommerce-pagination ul.page-numbers li a.next', function(e) {
                   
        e.preventDefault();

        var link = $(this).attr('href');

        var page = link.split("product-page=")[1] ? link.split("product-page=")[1] : link.split("admin-ajax.php?paged=")[1];

        if (!page) {
            var page_from_url = link.split("/page/")[1] ? link.split("/page/")[1] : '';
            page = page_from_url.split("/")[0];
        }

        if( parseInt(page) == $(document).find('nav.woocommerce-pagination ul.page-numbers li a.page-numbers:not(a.prev, a.next)').length ) {
            $(document).find('nav.woocommerce-pagination ul.page-numbers li a.next').hide();
        } else {
            $(document).find('nav.woocommerce-pagination ul.page-numbers li a.next').show();
        }
                                
        if( parseInt(page) > 1 ) {

            if( ! $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev').length ) {

                // Add prev button
                $(document).find('nav.woocommerce-pagination ul.page-numbers').prepend('<li><a class="prev page-numbers" href="">←</a></li>');
                $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev').hide();

                // Current Page - Replace span with link
                var previous = $(document).find('nav.woocommerce-pagination ul.page-numbers li span[aria-current="page"]');
                                    
                if (previous) {
                    var text = previous.html();
                    previous.closest('li').html('<a class="page-numbers current" href="' + awcs_wc_global_params.home_url + 'wp-admin/admin-ajax.php?product-page='+text+'">'+text+'</a>');
                }

            }

            $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev').show();
        }

        if( parseInt(page) == 1 ) {
            $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev').hide();
        }

        $(document).find('input[name="paged"]').val(page);
        $(document).find('nav.woocommerce-pagination ul.page-numbers li a.page-numbers').removeAttr('aria-current').removeClass('current');
                                
        $(document).find('nav.woocommerce-pagination ul.page-numbers li a.page-numbers:contains("'+page+'")').attr("aria-current", "page");
        $(document).find('nav.woocommerce-pagination ul.page-numbers li a.page-numbers:contains("'+page+'")').addClass("current");

        $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev, nav.woocommerce-pagination ul.page-numbers li a.next').removeAttr('aria-current').removeClass('current');

        var previous_page = parseInt(page) > 1 ? parseInt(page) - 1 : '';

        var next_page = parseInt(page) < $(this).closest('ul.page-numbers').find('li a.page-numbers:not(a.prev, a.next)').length ? parseInt(page) + 1 : '';

        if (next_page) {

            var next_link = awcs_wc_global_params.home_url + 'wp-admin/admin-ajax.php?product-page=' + next_page;

            $(document).find('ul.page-numbers li a.next').attr('href', next_link);

        }

        if (previous_page) {

            var previous_link = awcs_wc_global_params.home_url + 'wp-admin/admin-ajax.php?product-page=' + previous_page;
                                    
            $(document).find('ul.page-numbers li a.prev').attr('href', previous_link);
                                    
        }

        var orderby = $(document).find('form.woocommerce-ordering select.orderby option:selected').length ? $(document).find('form.woocommerce-ordering select.orderby option:selected').val() : '';
        var category = $(document).find('form.woocommerce-ordering input[name="category"]').length ? $(document).find('form.woocommerce-ordering input[name="category"]').val() : '';
        var tag = $(document).find('form.woocommerce-ordering input[name="tag"]').length ? $(document).find('form.woocommerce-ordering input[name="tag"]').val() : '';

        if( !category && window.location.href.indexOf("/product-category/") >= 0 ) {
            category = window.location.href.split("/product-category/")[1].split("/");
            if( category[ category.length - 1 ] ) {
                category = category[ category.length - 1 ];
            } else {
                category = category[ category.length - 2 ];
            }
        }

        if( !tag && window.location.href.indexOf("/product-tag/") >= 0 ) {
            tag = window.location.href.split("/product-tag/")[1].split("/");
            if( tag[ tag.length - 1 ] ) {
                tag = tag[ tag.length - 1 ];
            } else {
                tag = tag[ tag.length - 2 ];
            }
        }

        var data = {
            action: 'awcs_wc_paged_products',
            'orderby': orderby,
            'page': page,
            'category': category,
            'tag': tag
        };

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: data,
            beforeSend: function() {
                awcs_set_target_element( awcs_get_target_element() );
                block( awcs_target );
            },
            success: function (response) {

                var catalog = $('<div></div>');
                catalog.html(response);

                var products = $( 'ul.products', catalog ).first();
                
                awcs_target.find('ul.products').first().replaceWith(products).promise().done(function() {

                    category = category ? category : '';
                    tag = tag ? tag : '';

                    $.ajax({
                        type: 'post',
                        url: awcs_wc_global_params.ajax_url,
                        data: { action: 'awcs_get_breadcrumb_with_page', 'page': page, 'category': category, 'tag': tag },
                        success: function (breadcrumb) {
                            $(document).find('nav.woocommerce-breadcrumb').replaceWith(breadcrumb);
                        }
                    });

                    $.ajax({
                        type: 'post',
                        url: awcs_wc_global_params.ajax_url,
                        data: { action: 'awcs_show_result_count', 'page': page, 'category': category, 'tag': tag },
                        success: function (result) {
                            $(document).find('p.woocommerce-result-count').replaceWith(result);
                                if(category) {
                                    document.title = category + ' - Page - ' + page + ' - ' + awcs_wc_global_params.page_title;
                                } else if(tag) {
                                    document.title = tag + ' - Page - ' + page + ' - ' + awcs_wc_global_params.page_title;
                                } else {
                                    if( awcs_wc_global_params.page_title.indexOf('Products') >= 0 ) {
                                        document.title = 'Page - ' + page + ' - ' + awcs_wc_global_params.page_title;
                                    } else {
                                        document.title = 'Products - Page - ' + page + ' - ' + awcs_wc_global_params.page_title;
                                    }
                                }
                        }
                    });

                });
            
            },
            complete: function() {
                $.scroll_to_notices( $( '.entry-title, .page-title' ) );
                unblock( awcs_target );
            }
        });

    });

    // End products by paging

    // Category click event

    var category_selector = 'div.product_meta .posted_in a, .woocommerce-breadcrumb a, li.product .category a';

    if( awcs_wc_global_params.category_selector ) {

        category_selector += ', ';

        category_selector += awcs_wc_global_params.category_selector;

    }

	$(document).on('click', category_selector, function (e) {

        e.preventDefault();

        if( $(this).parents('.awcs_basic_single_product_summary').length ) {
            return;
        }

        if( $(this).parents('.woocommerce-breadcrumb').length ) {

            if( $(this).attr('href').indexOf("product-category/") >= 0 ) {

                // Skip - breadcrumb category link

            } else if( $(this).attr('href') == awcs_wc_global_params.shop_url ) {

                return;

            } else {

               window.open(
                  $(this).attr('href'),
                  '_blank'
                );

               return;

            }

        }

        var category = $(this).text();

        var category_name = $(this).text();

        block( $(this) );

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: { action: 'awcs_wc_categorized_products', 'category': category },
            beforeSend: function() {
                awcs_adjust_target_element( $(this) );
            },
            success: function (response) {
                awcs_target.html(response).promise().done(function() {
                    $(document).find('form.woocommerce-ordering').append('<input type="hidden" name="category" value="'+category+'" />');

                    // Add prev button
                    $(document).find('nav.woocommerce-pagination ul.page-numbers').prepend('<li><a class="prev page-numbers" href="">←</a></li>');
                    $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev').hide();

                    // Current Page - Replace span with link
                    var previous = $(document).find('nav.woocommerce-pagination ul.page-numbers li span[aria-current="page"]');
                                    
                    if (previous) {
                        var text = previous.html();
                        previous.closest('li').html('<a class="page-numbers current" href="' + awcs_wc_global_params.home_url + 'wp-admin/admin-ajax.php?product-page='+text+'">'+text+'</a>');
                    }

                });
            },
            complete: function (response) {
                document.title = category_name + ' - ' + awcs_wc_global_params.page_title;
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
                    $(document).find('.woocommerce-breadcrumb').last().hide();
                $.scroll_to_notices( $( '.entry-title, .page-title' ) );
                unblock( $(this) );
            }
        });

    });

	// Tag click event

    var tag_selector = 'div.product_meta .tagged_as a';

    if( awcs_wc_global_params.tag_selector ) {

        tag_selector += ', ';
        
        tag_selector += awcs_wc_global_params.tag_selector;

    }

    $(document).on('click', tag_selector, function (e) {

        e.preventDefault();

        if( $(this).parents('.awcs_basic_single_product_summary').length ) {
            return;
        }

        var tag = $(this).text();

        var tag_name = $(this).text();

        block( $(this) );

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: { action: 'awcs_wc_tagged_products', 'tag': tag },
            beforeSend: function() {
                awcs_adjust_target_element( $(this) );
            },
            success: function (response) {
                awcs_target.html(response).promise().done(function() {
                    $(document).find('form.woocommerce-ordering').append('<input type="hidden" name="tag" value="'+tag+'" />');

                    // Add prev button
                    $(document).find('nav.woocommerce-pagination ul.page-numbers').prepend('<li><a class="prev page-numbers" href="">←</a></li>');
                    $(document).find('nav.woocommerce-pagination ul.page-numbers li a.prev').hide();

                    // Current Page - Replace span with link
                    var previous = $(document).find('nav.woocommerce-pagination ul.page-numbers li span[aria-current="page"]');
                                    
                    if (previous) {
                        var text = previous.html();
                        previous.closest('li').html('<a class="page-numbers current" href="' + awcs_wc_global_params.home_url + 'wp-admin/admin-ajax.php?product-page='+text+'">'+text+'</a>');
                    }

                });
            },
            complete: function (response) {
                document.title = tag_name + ' - ' + awcs_wc_global_params.page_title;
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
                    $(document).find('.woocommerce-breadcrumb').last().hide();
                $.scroll_to_notices( $( '.entry-title, .page-title' ) );
                unblock( $(this) );
            }
        });

    });

    // View number of products
    $(document).on( 'click', '.oceanwp-toolbar .result-count li', function(e) {

        e.preventDefault();

        var orderby = $(document).find('form.woocommerce-ordering select.orderby option:selected').length ? $(document).find('form.woocommerce-ordering select.orderby option:selected').val() : '';
        var category = $(document).find('form.woocommerce-ordering input[name="category"]').length ? $(document).find('form.woocommerce-ordering input[name="category"]').val() : '';
        var tag = $(document).find('form.woocommerce-ordering input[name="tag"]').length ? $(document).find('form.woocommerce-ordering input[name="tag"]').val() : '';

        if( !category && window.location.href.indexOf("/product-category/") >= 0 ) {
            category = window.location.href.split("/product-category/")[1].split("/");
            if( category[ category.length - 1 ] ) {
                category = category[ category.length - 1 ];
            } else {
                category = category[ category.length - 2 ];
            }
        }

        if( !tag && window.location.href.indexOf("/product-tag/") >= 0 ) {
            tag = window.location.href.split("/product-tag/")[1].split("/");
            if( tag[ tag.length - 1 ] ) {
                tag = tag[ tag.length - 1 ];
            } else {
                tag = tag[ tag.length - 2 ];
            }
        }

        var limit = $(this).text();
        
        var data = {
            action: 'awcs_wc_view_numberof_products',
            'limit': limit,
            'orderby': orderby,
            'category': category,
            'tag': tag
        };

        $.ajax({
            type: 'post',
            url: awcs_wc_global_params.ajax_url,
            data: data,
            beforeSend: function() {
                awcs_set_target_element( awcs_get_target_element() );
                block( awcs_target );
            },
            success: function (response) {
                // Insert body classes
                var body_classes = ' page-template-default page woocommerce woocommerce-page ';
                awcs_insert_body_classes( body_classes );
                awcs_target.html(response);
            },
            complete: function() {
                unblock( awcs_target );
                if( $(document).find('.woocommerce-breadcrumb').length > 1 )
                    $(document).find('.woocommerce-breadcrumb').last().hide();
                $.scroll_to_notices( $( '.entry-title, .page-title' ) );
            }
        });

    });

})(jQuery);