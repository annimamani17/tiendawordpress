jQuery(document).ready(function ($) {
    const isPremiumTheme = ediot_ajax_object.is_premium_theme;
    const loaderHtml = `
        <div class="loader" style="display: inline-block;">
            <img src="${ediot_ajax_object.plugin_url}/loader/loader5.gif" alt="Loading..." style="width: 30px; height: 30px;" />
        </div>`;

    function ediotDisplayMessage(message, isError = false, messageContainerId = 'ediot-message') {
        const $messageContainer = $('#' + messageContainerId);
        $messageContainer
            .text(message)
            .removeClass('ediot-msg-success ediot-msg-error')
            .addClass(isError ? 'ediot-msg-error' : 'ediot-msg-success')
            .css('display', 'block');
    }

    function ediotHandleAjaxRequest(action, nonce, successCallback, button) {
        const $loader = $(loaderHtml).insertAfter(button);

        $.ajax({
            url: ediot_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: action,
                nonce: nonce
            },
            beforeSend: function () {
                $loader.show();
                button.prop('disabled', true);
            },
            success: function (response) {
                // Clean response if it contains unexpected output
                if (typeof response !== 'object') {
                    try {
                        response = JSON.parse(response.replace(/^.*?({.*})$/, '$1'));
                    } catch (e) {
                        ediotDisplayMessage('Error: Invalid response format.', true);
                        return;
                    }
                }
                successCallback(response);
            },
            error: function () {
                ediotDisplayMessage('Error occurred during the process.', true);
            },
            complete: function () {
                button.prop('disabled', false);
                $loader.remove();
            }
        });
    }

    $('#ediot-start-import').on('click', function () {
        const nonce = $('#ediot_import_demo_nonce_field').val();
        ediotHandleAjaxRequest('ediot_import_demo_content', nonce, function (response) {
            if (response.success) {
                ediotDisplayMessage(response.data, false, 'ediot-message-import');
                $('#ediot-next-card-6').show();

                if (isPremiumTheme) {
                    var ediot_view_site = document.getElementById("ediot-next-menu");
                    ediot_view_site.style.display = "block";
                } else {
                    $('#ediot-view-site-final').show();
                    $('#ediot-view-template-final').show();
                    $('#ediot-start-import').hide();
                }
            } else {
                ediotDisplayMessage(response.data, true, 'ediot-message-import');
            }
        }, $(this));
    });

    $('#ediot-next-menu').on('click', function () {
        $('.ediot-card-content').hide();
        $('#card-7').show();
        ediotUpdateActiveCard(7);
    });

    $('#ediot-import-pages-data').on('click', function () {
        const nonce = $('#ediot_import_pages_nonce_field').val();
        ediotHandleAjaxRequest('ediot_import_inner_pages_data', nonce, function (response) {
            if (response.success) {
                ediotDisplayMessage(response.data, false, 'ediot-message');
                $('#ediot-next-card-6').show();
            } else {
                ediotDisplayMessage(response.data, true, 'ediot-message');
                if (response.data.includes('logs')) {
                    ediotDisplayMessage(__('Please check the logs for more details.', 'easy-demo-import-for-omega-themes'), true);
                }
            }
        }, $(this));
    });

    $('#ediot-next-card-6').on('click', function () {
        $('.ediot-card-content').hide();
        $('#card-6').show();
        ediotUpdateActiveCard(6);
    });

    $('#ediot-import-menus').on('click', function () {
        const nonce = $('#ediot_import_menu_nonce_field').val();
        ediotHandleAjaxRequest('ediot_import_menu_data', nonce, function (response) {
            if (response.success) {
                ediotDisplayMessage(response.data, false, 'ediot-message-menus');
                $('#ediot-view-site-final').show();
                $('#ediot-view-template-final').show();
            } else {
                ediotDisplayMessage(response.data, true, 'ediot-message-menus');
            }
        }, $(this));
    });

    function ediotUpdateActiveCard(cardNumber) {
        const buttons = document.querySelectorAll('.ediot-card-button');
        buttons.forEach(button => {
            button.classList.remove('active');
            button.disabled = false;
            if (button.getAttribute('data-card') == cardNumber) {
                button.classList.add('active');
            }
        });
    }
});

//end 

//template js 
jQuery(document).ready(function ($) {
    function ediotGetThemeInfo() {
        return {
            isPremiumTheme: ediot_ajax_object.is_premium_theme,
            hasPluginsJson: ediot_ajax_object.has_plugins_json
        };
    }
    const { isPremiumTheme, hasPluginsJson } = ediotGetThemeInfo();

    if (isPremiumTheme) {
        $('#card-3').css('display', 'block');
        ediotUpdateActiveCard(3);
    } else {
        $('#card-1').css('display', 'block');
    }

    $('#ediot-start-now').on('click', function () {

        if (hasPluginsJson) {
            $('#card-1').css('display', 'none');
            $('#card-4').css('display', 'block');
            ediotUpdateActiveCard(4);
        } else {
            $('#card-1').css('display', 'none');
            $('#card-2').css('display', 'block');
            ediotUpdateActiveCard(2);
        }

    });

    if ($('#ediot-install-plugins').length) {

        $('#ediot-install-plugins').on('click', function () {
            $('#card-3').css('display', 'none');
            $('#card-4').css('display', 'block');
            ediotUpdateActiveCard(4);
        });

        if (!isPremiumTheme && hasPluginsJson) {
            $('#ediot-skip-plugins').on('click', function () {

                if (ediot_ajax_object.has_setup_widgets) {

                    $('#card-4').css('display', 'none');
                    $('#card-5').css('display', 'block');
                    ediotUpdateActiveCard(5);

                } else {
                    $('#card-4').css('display', 'none');
                    $('#card-6').css('display', 'block');
                    ediotUpdateActiveCard(6);
                }
            });
        } else {
            $('#ediot-skip-plugins').on('click', function () {
                $('#card-4').css('display', 'none');
                $('#card-5').css('display', 'block');
                ediotUpdateActiveCard(5);
            });
        }
    }

    if ($('#ediot-skip-button').length) {
        $('#ediot-skip-button').on('click', function () {
            $('#card-2').css('display', 'none');
            if (isPremiumTheme) {
                $('#card-3').css('display', 'block');
                ediotUpdateActiveCard(3);
            } else {

                if (ediot_ajax_object.has_setup_widgets) {
                    ediotUpdateActiveCard(5);
                } else {
                    $('#card-6').css('display', 'block');
                    ediotUpdateActiveCard(6);
                }



            }
        });
    }

    function ediotUpdateActiveCard(cardNumber) {
        $('.ediot-card-button').each(function () {
            $(this).removeClass('active');
            $(this).prop('disabled', false);
            if ($(this).data('card') == cardNumber) {
                $(this).addClass('active');
            }
        });
    }

    if (isPremiumTheme) {
        $('#card-3').show();
        ediotUpdateActiveCard(3);
    } else {
        $('#card-1').show();
    }

    $('#ediot-start-now').on('click', function () {

        if (hasPluginsJson) {
            $('#card-1').hide();
            $('#card-4').show();
            ediotUpdateActiveCard(4);
        } else {
            $('#card-1').hide();
            $('#card-2').show();
            ediotUpdateActiveCard(2);
        }
    });


    $('#ediot-skip-button').on('click', function () {
        $('#card-2').hide();
        if (isPremiumTheme) {
            $('#card-3').show();
            ediotUpdateActiveCard(3);
        } else {
            $('#card-5').show();
            ediotUpdateActiveCard(5);
        }


        if (isPremiumTheme) {
            document.getElementById('card-3').style.display = 'block';
            ediotUpdateActiveCard(3);
        } else {
            document.getElementById('card-5').style.display = 'block';
            ediotUpdateActiveCard(5);
        }
    });

    function ediotUpdateActiveCard(cardNumber) {
        $('.ediot-card-button').each(function () {
            $(this).removeClass('active');
            $(this).prop('disabled', false);
            if ($(this).data('card') == cardNumber) {
                $(this).addClass('active');
            }
        });
    }
});
//end 

// new add for license key 
jQuery(document).ready(function ($) {

    const isPremiumTheme = ediot_ajax_object.is_premium_theme;

    function ediotUpdateActiveCard(cardNumber) {
        const buttons = document.querySelectorAll('.ediot-card-button');
        buttons.forEach(button => {
            button.classList.remove('active');
            button.disabled = false;
            if (button.getAttribute('data-card') == cardNumber) {
                button.classList.add('active');
            }
        });
    }

    function ediotShowCard(cardNumber) {
        document.querySelectorAll('.ediot-card-content').forEach(card => {
            card.style.display = 'none';
        });

        const selectedCard = document.getElementById('card-' + cardNumber);
        if (selectedCard) {
            selectedCard.style.display = 'block';
        }

        ediotUpdateActiveCard(cardNumber);
    }

    $('#ediot-start-now').on('click', function () {

        if (ediot_ajax_object.has_plugins_json) {
            ediotShowCard(4);
        } else {
            ediotShowCard(2);
        }
        // ediotShowCard(2);
    });

    $('#ediot-install-elementor').on('click', function () {
        $.post(ediot_ajax_object.ajax_url, {
            action: 'ediot_install_elementor',
            nonce: ediot_ajax_object.ediot_nonce
        }, function (response) {
            if (response.success) {
                if (!isPremiumTheme) {

                    if (ediot_ajax_object.has_setup_widgets) {
                        ediotShowCard(5);
                    } else {
                        ediotShowCard(6);
                    }
                    // ediotShowCard(6);
                } else {
                    ediotShowCard(3);
                }
            } else {
                alert(response.data);
                ediotShowCard(1);
            }
        });
    });

    $('#ediot-skip-button').on('click', function () {
        if (ediot_ajax_object.has_setup_widgets) {
            ediotShowCard(5);
        } else {
            ediotShowCard(6);
        }
    });


    // Handle "Change Key" functionality
    $('#ediot-change-key').on('click', function (e) {
        e.preventDefault();

        $.post(ediot_ajax_object.ajax_url, {
            action: 'ediot_deactivate_license',
        }, function (response) {
            if (response.success) {
                $('input[name="ediot_license_key"]').val('');
                $('input[name="ediot_license_key"]').prop('disabled', false);
                $('#ediot_license_form button[type="submit"]').prop('disabled', false).text('Activate');

                $('#ediot-change-key').hide();

                $('#ediot-next-step').remove();
            } else {
                alert('Failed to deactivate the license. Please try again.');
            }
        });
    });

    // Handle license key submission
    $('#ediot_license_form').on('submit', function (e) {
        e.preventDefault();

        const licenseKey = $('input[name="ediot_license_key"]').val();

        $.post(ediot_ajax_object.ajax_url, {
            action: 'ediot_verify_license',
            ediot_license_key: licenseKey
        }, function (response) {
            let jsonResponse;

            try {
                jsonResponse = (typeof response === 'string') ? JSON.parse(response) : response;
            } catch (e) {
                console.error('Response is not valid JSON:', response);
                alert('Unexpected server response. Please try again or contact support.');
                return;
            }

            // handle json
            if (jsonResponse.status) {
                // if (jsonResponse.success) {
                ediotShowCard(3);
                location.reload(true);
            } else {
                console.warn('License verification failed.');
                let message = 'License Activation Failed.';
                if (jsonResponse.data && typeof jsonResponse.data === 'object') {
                    message = jsonResponse.data.message || message;
                } else if (typeof jsonResponse.data === 'string') {
                    message = jsonResponse.data;
                }

                alert(message);
                location.reload(true);
            }
        });
    });
    // end 

    if ($('#ediot_license_form button').is('[disabled]')) {
        $('<button id="ediot-next-step" class="button button-primary">Next</button>').insertAfter('#ediot_license_form');
        $('#ediot-next-step').on('click', function () {
            ediotShowCard(4);
        });
    }

});

// end 

//above required plugin function 
jQuery(document).ready(function ($) {
    function ediotGetThemeInfo() {
        return {
            isPremiumTheme: ediot_ajax_object.is_premium_theme,
            hasPluginsJson: ediot_ajax_object.has_plugins_json
        };
    }
    var ediot_skip = document.getElementById("ediot-skip-plugins");
    $.ajax({
        url: ediot_ajax_object.ajax_url,
        method: 'POST',
        data: {
            action: 'ediot_get_required_plugins',
            _ajax_nonce: ediot_ajax_object.ediot_nonce
        },
        success: function (response) {
            if (response.success) {
                const plugins = response.data;
                const pluginListContainer = $('#required-plugins-list');
                pluginListContainer.empty();
                let requiredPlugins = [];

                $.each(plugins, function (index, plugin) {
                    if (plugin.status === 'required') {
                        requiredPlugins.push(plugin);
                        ediot_skip.style.display = "none";

                        const pluginItem = `
                            <div class="plugin-item" id="plugin-${plugin.slug}">
                                <h4>${plugin.name}</h4>
                                <span class="plugin-status" id="plugin-status-${plugin.slug}">Required</span>
                            </div>`;
                        pluginListContainer.append(pluginItem);
                    }
                });

                // skip plugin
                if (requiredPlugins.length === 0) {
                    pluginListContainer.html('<p class="success-message">All required plugins are already installed and activated. <strong>Skip for next</strong>.</p>');
                    $('#ediot-install-plugins').hide();
                    // ediot_skip.style.display = "block";
                }
            } else {
                $('#required-plugins-list').html(`<p class="error">${response.data}</p>`);
            }
        },


        //end 
        error: function () {
            $('#required-plugins-list').html('<p class="error">An error occurred while fetching the plugins.</p>');
        }
    });

    // Plugin installation logic
    $('#ediot-install-plugins').on('click', function () {
        this.disabled = true;
        this.innerText = 'Installing...';

        // Fetch plugins again for installation
        $.ajax({
            url: ediot_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'ediot_get_required_plugins',
                _ajax_nonce: ediot_ajax_object.tti_nonce
            },
            success: function (response) {
                if (response.success) {
                    // Filter only the required plugins (not installed & not activated)
                    const requiredPlugins = response.data.filter(plugin => plugin.status === 'required');

                    if (requiredPlugins.length === 0) {
                        $('#ediot-install-plugins').text('All plugins are already installed and activated.');
                        return;
                    }

                    installPlugin(requiredPlugins, 0);
                } else {
                    const errorMessage = response.data ? response.data : 'An unexpected error occurred while fetching plugins.';
                    alert(errorMessage);
                }
            },
            error: function () {
                alert('An error occurred while fetching the plugins.');
            }
        });

        function installPlugin(plugins, index) {
            const { isPremiumTheme, hasPluginsJson } = ediotGetThemeInfo();
            if (index >= plugins.length) {
                $('#ediot-install-plugins').text('All plugins installed successfully.');
                setTimeout(function () {
                    if (!isPremiumTheme && hasPluginsJson) {

                        if (ediot_ajax_object.has_setup_widgets) {

                            $('#card-4').hide();
                            $('#card-5').show();
                            ediotUpdateActiveCard(5);

                        } else {
                            $('#card-4').hide();
                            $('#card-6').show();
                            ediotUpdateActiveCard(6);
                        }
                    } else {

                        $('#card-4').hide();
                        $('#card-5').show();
                        ediotUpdateActiveCard(5);

                    }
                }, 3000);
                return;
            }

            const plugin = plugins[index];
            const statusElement = $(`#plugin-status-${plugin.slug}`);
            statusElement.html('<span class="spinner is-active"></span> Installing...');

            $.ajax({
                url: ediot_ajax_object.ajax_url,
                method: 'POST',
                data: {
                    action: 'ediot_install_single_plugin',
                    plugin_slug: plugin.slug,
                    _ajax_nonce: ediot_ajax_object.ediot_nonce
                },
                success: function (response) {
                    const statusElement = $(`#plugin-status-${plugin.slug}`);

                    if (typeof response === 'string') {
                        const jsonResponseMatch = response.match(/(\{.*\})$/);

                        if (jsonResponseMatch) {
                            try {
                                const jsonResponse = JSON.parse(jsonResponseMatch[0]);

                                if (jsonResponse.success) {
                                    statusElement.html('<span class="dashicons dashicons-yes"></span> ' + (jsonResponse.data.message || 'success.'));
                                } else {
                                    const errorMessage = jsonResponse.data.message || 'fail.';
                                    statusElement.html(`<span class="dashicons dashicons-no-alt"></span> ${errorMessage}`);
                                }
                            } catch (e) {
                                statusElement.html('<span class="dashicons dashicons-no-alt"></span> Fail.');
                            }
                        } else {
                            statusElement.html('<span class="dashicons dashicons-no-alt"></span> Fail.');
                        }
                    } else if (typeof response === 'object' && response !== null) {
                        if (response.success) {
                            statusElement.html('<span class="dashicons dashicons-yes"></span> ' + (response.data.message || 'success.'));
                        } else {
                            const errorMessage = response.data.message || 'An unknown error occurred during installation.';
                            statusElement.html(`<span class="dashicons dashicons-no-alt"></span> ${errorMessage}`);
                        }
                    } else {
                        statusElement.html('<span class="dashicons dashicons-no-alt"></span> Fail.');
                    }

                    installPlugin(plugins, index + 1); // Proceed to the next plugin
                },


                error: function () {
                    const statusElement = $(`#plugin-status-${plugin.slug}`);
                    statusElement.html('<span class="dashicons dashicons-no-alt"></span> Installation failed.');
                    installPlugin(plugins, index + 1); // Continue with the next plugin
                }
            });
        }
    });

    function ediotUpdateActiveCard(cardNumber) {
        const buttons = document.querySelectorAll('.ediot-card-button');
        buttons.forEach(button => {
            button.classList.remove('active');
            button.disabled = false;
            if (button.getAttribute('data-card') == cardNumber) {
                button.classList.add('active');
            }
        });
    }
});


// for free themes pagination
jQuery(document).ready(function ($) {

    function loadThemes(page, search = '') {
        const loader = document.getElementById('templates-loader-overlay');
        if (loader) loader.style.display = 'block';
        $('.main-grid-card-parent').addClass('loading');

        $.ajax({
            url: ediot_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'ediot_free_themes_ajax',
                page: page,
                search: search
            },
            success: function (response) {
                $('.main-grid-card-parent').removeClass('loading');
                if (response.success) {
                    $('.theme-templates').html(response.data.html);
                    $('.main-grid-card-pagination span').text('Page ' + page + ' of ' + response.data.total_pages);

                    $('.pagination-previous-btn').data('page', page - 1);
                    $('.pagination-next-btn').data('page', page + 1);

                    if (page <= 1) $('.pagination-previous-btn').hide(); else $('.pagination-previous-btn').show();
                    if (page >= response.data.total_pages) $('.pagination-next-btn').hide(); else $('.pagination-next-btn').show();
                }
                if (loader) loader.style.display = 'none';
            },
            error: function () {
                $('.main-grid-card-parent').removeClass('loading');
                if (loader) loader.style.display = 'none';
            }
        });
    }

    let searchTimeout;

    $(document).on('input', '#ediot-theme-search', function () {
        let searchValue = $(this).val();

        clearTimeout(searchTimeout);

        let delay = searchValue.length > 2 ? 800 : 2000;

        searchTimeout = setTimeout(function () {
            loadThemes(1, searchValue);
        }, delay);
    });

    $(document).on('click', '.pagination-next-btn', function () {
        let nextPage = $(this).data('page');
        let searchValue = $('#ediot-theme-search').val();
        loadThemes(nextPage, searchValue);
    });

    $(document).on('click', '.pagination-previous-btn', function () {
        let prevPage = $(this).data('page');
        let searchValue = $('#ediot-theme-search').val();
        loadThemes(prevPage, searchValue);
    });


    $(document).on('click', '.grid-install-free', function (e) {
        e.preventDefault();
        const loader = document.getElementById('templates-loader-overlay');
        if (loader) loader.style.display = 'block';

        var install_btn = $(this);
        var theme_domain = install_btn.data('theme');

        $.ajax({
            url: ediot_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'ediot_install_free_theme',
                theme_domain: theme_domain,
                _wpnonce: ediot_ajax_object.ediot_nonce_obj.wpnonce
            },
            success: function (response) {
                const loader = document.getElementById('templates-loader-overlay');
                if (loader) loader.style.display = 'none';
                location.reload();
            },
            error: function () {
                alert('An error occurred. Please try again.');
            }
        });
    });

});





