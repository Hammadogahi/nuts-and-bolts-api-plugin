jQuery(document).ready(function($) {
    var searchUrl = ''; // Variable to store the product URL

    // Handle search form submission
    $('#custom-search-form').on('submit', function(e) {
        e.preventDefault();

        searchUrl = $('#product-url').val(); // Store the URL to use after newsletter submission
        $('#custom-search-form').hide(); // Hide the search form

        // Show the newsletter form with fade-in effect
        $('#newsletter-form').css('display', 'block').animate({opacity: 1}, 250);
    });

    // Handle newsletter form submission
    $('#newsletter-form').on('submit', function(e) {
        e.preventDefault();

        var email = $('#newsletter-email').val();

        // AJAX request for newsletter subscription
        $.ajax({
            type: 'POST',
            url: custom_ajax_obj.ajax_url,
            data: {
                action: 'handle_newsletter_subscription',
                email: email,
                nonce: custom_ajax_obj.nonce,
            },
            success: function(response) {
                if (response.success) {
                    $('#newsletter-form').hide(); // Hide the newsletter form
                    $('#loading-spinner').show(); // Show the loading spinner
					$('#waiting-message').show();

                    // AJAX request for the search after successful newsletter submission
                    $.ajax({
                        type: 'POST',
                        url: custom_ajax_obj.ajax_url,
                        data: {
                            action: 'custom_ajax_search',
                            product_url: searchUrl,
                            nonce: custom_ajax_obj.nonce,
                        },
                        success: function(response) {
                            $('#loading-spinner').hide(); // Hide the loading spinner
							$('#waiting-message').hide();
                            if (response.success) {
                                $('#search-result').html(response.data); // Display the search results
                            } else {
                                $('#search-result').html('<p>' + response.data + '</p>'); // Display an error message
                            }
                        }
                    });
                } else {
                    alert(response.data); // Show error message if needed
                }
            }
        });
    });
});

jQuery(document).ready(function ($) {
    $('#custom-search-form-v2').on('submit', function (e) {
        e.preventDefault();
        var product_url = $('#product-url-v2').val();
        $('#custom-search-form-v2').hide(); // Hide the search form
        // Show the newsletter form with fade-in effect
        $('#newsletter-form').css('display', 'block').animate({opacity: 1}, 250);

        $('#newsletter-form').on('submit', function(e) {
            e.preventDefault();
    
            var email = $('#newsletter-email').val();

            // AJAX request for newsletter subscription
            $.ajax({
                type: 'POST',
                url: custom_ajax_obj.ajax_url,
                data: {
                    action: 'handle_newsletter_subscription',
                    email: email,
                    nonce: custom_ajax_obj.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        $('#newsletter-form').hide(); // Hide the newsletter form
                        $('#loading-spinner-v2').show(); // Show the loading spinner
                        $('#waiting-message').show();

                        $.ajax({
                            url: custom_ajax_obj.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'custom_ajax_search_v2',
                                product_url: product_url,
                                nonce: custom_ajax_obj.nonce
                            },
                            success: function (response) {
                                $('#loading-spinner-v2').hide();
                                $('#waiting-message').hide();
                                if (response.success) {
                                    $('#search-result-v2').html(response.data);
                                } else {
                                    $('#search-result-v2').html('<p>Error: ' + response.data + '</p>');
                                }
                            },
                            error: function (xhr, status, error) {
                                $('#loading-spinner-v2').hide();
                                $('#search-result-v2').html('<p>An error occurred: ' + error + '</p>');
                            },
                        });
                    }
                }, // Moved this comma here
                error: function (xhr, status, error) { // Added error handling for newsletter subscription
                    $('#newsletter-form').html('<p>An error occurred: ' + error + '</p>');
                }
            }); // Closing tag for the AJAX call
        });
    });
});

