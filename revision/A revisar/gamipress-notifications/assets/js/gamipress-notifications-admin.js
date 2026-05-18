(function( $ ) {

    // Auto hide delay visibility
    $('#gamipress_notifications_auto_hide').on('change', function(e) {
        var target = $('.cmb2-id-gamipress-notifications-auto-hide-delay');

        if( $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( ! $('#gamipress_notifications_auto_hide').prop('checked') ) {
        $('.cmb2-id-gamipress-notifications-auto-hide-delay').hide().addClass('cmb2-tab-ignore');
    }

    // Auto hide achievement notification
    $('#gamipress_notifications_disable_achievements').on('change', function(e) {
        var target = $('.cmb2-id-gamipress-notifications-achievement-title-pattern, .cmb2-id-gamipress-notifications-achievement-content-pattern');

        if( ! $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( $('#gamipress_notifications_disable_achievements').prop('checked') ) {
        $('.cmb2-id-gamipress-notifications-achievement-title-pattern, .cmb2-id-gamipress-notifications-achievement-content-pattern').hide().addClass('cmb2-tab-ignore');
    }

    // Auto hide step notification
    $('#gamipress_notifications_disable_steps').on('change', function(e) {
        var target = $('.cmb2-id-gamipress-notifications-step-title-pattern, .cmb2-id-gamipress-notifications-step-content-pattern');

        if( ! $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( $('#gamipress_notifications_disable_steps').prop('checked') ) {
        $('.cmb2-id-gamipress-notifications-step-title-pattern, .cmb2-id-gamipress-notifications-step-content-pattern').hide().addClass('cmb2-tab-ignore');
    }

    // Auto hide points award notification
    $('#gamipress_notifications_disable_points_awards').on('change', function(e) {
        var target = $('.cmb2-id-gamipress-notifications-points-award-title-pattern, .cmb2-id-gamipress-notifications-points-award-content-pattern');

        if( ! $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( $('#gamipress_notifications_disable_points_awards').prop('checked') ) {
        $('.cmb2-id-gamipress-notifications-points-award-title-pattern, .cmb2-id-gamipress-notifications-points-award-content-pattern').hide().addClass('cmb2-tab-ignore');
    }

    // Auto hide points deduct notification
    $('#gamipress_notifications_disable_points_deducts').on('change', function(e) {
        var target = $('.cmb2-id-gamipress-notifications-points-deduct-title-pattern, .cmb2-id-gamipress-notifications-points-deduct-content-pattern');

        if( ! $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( $('#gamipress_notifications_disable_points_deducts').prop('checked') ) {
        $('.cmb2-id-gamipress-notifications-points-deduct-title-pattern, .cmb2-id-gamipress-notifications-points-deduct-content-pattern').hide().addClass('cmb2-tab-ignore');
    }

    // Auto hide rank notification
    $('#gamipress_notifications_disable_ranks').on('change', function(e) {
        var target = $('.cmb2-id-gamipress-notifications-rank-title-pattern, .cmb2-id-gamipress-notifications-rank-content-pattern');

        if( ! $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( $('#gamipress_notifications_disable_ranks').prop('checked') ) {
        $('.cmb2-id-gamipress-notifications-rank-title-pattern, .cmb2-id-gamipress-notifications-rank-content-pattern').hide().addClass('cmb2-tab-ignore');
    }

    // Auto hide rank requirement notification
    $('#gamipress_notifications_disable_rank_requirements').on('change', function(e) {
        var target = $('.cmb2-id-gamipress-notifications-rank-requirement-title-pattern, .cmb2-id-gamipress-notifications-rank-requirement-content-pattern');

        if( ! $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( $('#gamipress_notifications_disable_rank_requirements').prop('checked') ) {
        $('.cmb2-id-gamipress-notifications-rank-requirement-title-pattern, .cmb2-id-gamipress-notifications-rank-requirement-content-pattern').hide().addClass('cmb2-tab-ignore');
    }

    // Admin bar menu actions
    $( document ).on('click', '#wp-admin-bar-gamipress-notifications-view-all > a, #wp-admin-bar-gamipress-notifications > a', function( e ) {
        // Let the link work normally - no AJAX needed
        return true;
    });

    $( document ).on('click', '#wp-admin-bar-gamipress-notifications-mark-all-read > a', function( e ) {
        e.preventDefault();
        var data = {
            action: 'gamipress_notifications_mark_all_read',
            nonce: gamipress_notifications_admin.nonce
        };

        $.post( gamipress_notifications_admin.ajaxurl, data, function( response ) {
            if ( response.success ) {
                alert( gamipress_notifications_admin.text_marked_all_read );
                var badge = $('#wp-admin-bar-gamipress-notifications .gamipress-notifications-count');
                if ( badge.length ) {
                    badge.remove();
                }
                // Reload page to update notification counts
                location.reload();
            } else {
                alert( gamipress_notifications_admin.text_error );
            }
        });

        return false;
    });

    // Mark All as Read button on notifications page
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Script loaded - Looking for Mark All as Read button');
        
        var btn = document.getElementById('gamipress-notifications-mark-all-read-btn');
        
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Button clicked - Starting AJAX request');
                
                btn.disabled = true;
                var originalText = btn.textContent;
                btn.textContent = 'Procesando...';
                
                if (typeof gamipress_notifications_admin === 'undefined') {
                    console.error('gamipress_notifications_admin is not defined');
                    alert('Error: Variables no inicializadas');
                    btn.disabled = false;
                    btn.textContent = originalText;
                    return false;
                }
                
                var formData = new FormData();
                formData.append('action', 'gamipress_notifications_mark_all_read');
                formData.append('nonce', gamipress_notifications_admin.nonce);
                
                fetch(gamipress_notifications_admin.ajaxurl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(response) {
                    return response.text();
                })
                .then(function(text) {
                    console.log('Response received');
                    
                    try {
                        var response = JSON.parse(text);
                        
                        if (response.success) {
                            console.log('AJAX success - marked count:', response.data.marked_count);
                            
                            // Paso 1: Obtener todas las notificaciones sin leer
                            var unreadItems = document.querySelectorAll('.gamipress-notification-item.gamipress-notification-unread');
                            console.log('Found ' + unreadItems.length + ' unread items');
                            
                            if (unreadItems.length > 0) {
                                // Paso 2: Obtener o crear la sección de Read Notifications
                                var readSection = getOrCreateReadSection();
                                
                                // Paso 3: Mover items de unread a read
                                var itemsArray = Array.from(unreadItems);
                                itemsArray.forEach(function(item) {
                                    // Cambiar clase de unread a read
                                    item.classList.remove('gamipress-notification-unread');
                                    item.classList.add('gamipress-notification-read');
                                    
                                    // Mover el elemento a la sección Read
                                    readSection.appendChild(item);
                                });
                                
                                // Paso 4: Ocultar la sección Unread si está vacía
                                var unreadSection = getUnreadSection();
                                if (unreadSection) {
                                    var remainingUnread = unreadSection.querySelectorAll('.gamipress-notification-item').length;
                                    console.log('Remaining unread items in section:', remainingUnread);
                                    if (remainingUnread === 0) {
                                        unreadSection.style.display = 'none';
                                    }
                                }
                            }
                            
                            // Paso 5: Actualizar contador en admin bar
                            updateAdminBarCounter();
                            
                            console.log('UI updated successfully');
                            alert('Todas las notificaciones marcadas como leídas.');
                            
                            btn.disabled = false;
                            btn.textContent = originalText;
                            
                        } else {
                            console.error('AJAX response not successful:', response.data);
                            alert('Error: ' + (response.data || 'Error desconocido'));
                            btn.disabled = false;
                            btn.textContent = originalText;
                        }
                    } catch(parseError) {
                        console.error('JSON parse error:', parseError);
                        alert('Error al procesar respuesta');
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                })
                .catch(function(error) {
                    console.error('Fetch error:', error);
                    alert('Error en la solicitud: ' + error.message);
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
                
                return false;
            });
        } else {
            console.error('Button not found!');
        }
    });
    
    // Helper function: obtener sección Unread
    function getUnreadSection() {
        var sections = document.querySelectorAll('.gamipress-notifications-section');
        for (var i = 0; i < sections.length; i++) {
            var h3 = sections[i].querySelector('h3');
            if (h3 && h3.textContent.toLowerCase().includes('unread')) {
                return sections[i];
            }
        }
        return null;
    }
    
    // Helper function: obtener o crear sección Read
    function getOrCreateReadSection() {
        var sections = document.querySelectorAll('.gamipress-notifications-section');
        
        // Buscar sección Read existente
        for (var i = 0; i < sections.length; i++) {
            var h3 = sections[i].querySelector('h3');
            if (h3 && h3.textContent.toLowerCase().includes('read')) {
                console.log('Found existing Read section');
                return sections[i];
            }
        }
        
        // Si no existe, crear nueva sección Read
        console.log('Creating new Read section');
        var container = document.querySelector('.gamipress-notifications-list');
        var readSection = document.createElement('div');
        readSection.className = 'gamipress-notifications-section';
        readSection.innerHTML = '<h3 class="gamipress-notifications-section-title">Notificaciones Leídas</h3>';
        container.appendChild(readSection);
        
        return readSection;
    }
    
    // Helper function: actualizar contador admin bar
    function updateAdminBarCounter() {
        console.log('Updating admin bar counter');
        
        // Remover badges de contador
        var badges = document.querySelectorAll('.gamipress-notifications-count');
        badges.forEach(function(badge) {
            badge.remove();
        });
        
        // Actualizar label si existe
        var label = document.querySelector('#wp-admin-bar-gamipress-notifications .ab-label');
        if (label) {
            label.textContent = 'Notificaciones';
        }
    }




})( jQuery );