(function ($) {

    'use strict';

    var prefix  = 'gamipress-fluentcart-partial-payments-';
    var _prefix = 'gamipress_fluentcart_partial_payments_';

    // -----------------------------------------------------------------------
    // Points type selector: toggle per-type fields
    // -----------------------------------------------------------------------

    $('body').on('change', '#' + prefix + 'points-type', function () {

        var selectedType = $(this).val();

        // Hide all
        $('.' + prefix + 'points-label').hide();
        $('.' + prefix + 'points-preview').hide();
        $('.' + prefix + 'points').hide();
        $('.' + prefix + 'points-balance').hide();
        $('.' + prefix + 'points-type-fields').hide();

        // Show selected
        $('[data-points-type="' + selectedType + '"]').show();
        $('label[for="' + prefix + 'points-' + selectedType + '"]').show();
        $('#' + prefix + 'points-' + selectedType + '-preview').show();
        $('#' + prefix + 'points-' + selectedType).show();
        $('#' + prefix + 'points-' + selectedType + '-balance').show();

        updatePreview();
    });

    // -----------------------------------------------------------------------
    // Live preview update on points input / slider change
    // -----------------------------------------------------------------------

    $('body').on('input change', '.' + prefix + 'points', function () {
        var $this = $(this);
        // Update the numeric preview above the slider/input
        $('#' + $this.attr('id') + '-preview').text($this.val());
        updatePreview();
    });

    // -----------------------------------------------------------------------
    // Apply discount button
    // -----------------------------------------------------------------------

    $('body').on('click', '#gamipress-fluentcart-partial-payments button[name="apply_partial_payment"]', function (e) {

        e.preventDefault();

        var $form           = $(this).closest('.' + prefix + 'form');
        var $inputs         = $form.find('input, select, textarea');
        var $noticesWrapper = $('.' + prefix + 'notices');

        blockForm($form);

        $.ajax({
            url    : gamipress_fluentcart_partial_payments.ajaxurl,
            method : 'POST',
            data   : $inputs.serialize()
                   + '&action=gamipress_fluentcart_partial_payments_apply_partial_payment'
                   + '&nonce=' + gamipress_fluentcart_partial_payments.nonce,
            success: function (response) {
                clearNotices($noticesWrapper);
                if (response.success === false) {
                    showNotice('<div class="fluentcart-alert fluentcart-alert-error" role="alert">' + response.data + '</div>', $noticesWrapper);
                } else {
                    showNotice('<div class="fluentcart-alert fluentcart-alert-success" role="alert">' + response.data + '</div>', $noticesWrapper);
                    reloadPage();
                }
            },
            error: function () {
                clearNotices($noticesWrapper);
                showNotice('<div class="fluentcart-alert fluentcart-alert-error" role="alert">' + gamipress_fluentcart_partial_payments.error_generic + '</div>', $noticesWrapper);
            },
            complete: function () {
                unblockForm($form);
            }
        });
    });

    // -----------------------------------------------------------------------
    // Remove discount button
    // -----------------------------------------------------------------------

    $('body').on('click', '.' + prefix + 'remove', function (e) {

        e.preventDefault();

        var $this           = $(this);
        var $row            = $this.closest('tr');
        var $noticesWrapper = $('.' + prefix + 'notices');

        $.ajax({
            url    : gamipress_fluentcart_partial_payments.ajaxurl,
            method : 'POST',
            data   : {
                action     : 'gamipress_fluentcart_partial_payments_remove_partial_payment',
                nonce      : gamipress_fluentcart_partial_payments.nonce,
                points_type: $this.data('points-type')
            },
            success: function (response) {
                clearNotices($noticesWrapper);
                if (response.success === false) {
                    showNotice('<div class="fluentcart-alert fluentcart-alert-error" role="alert">' + response.data + '</div>', $noticesWrapper);
                } else {
                    $row.slideUp('fast');
                    showNotice('<div class="fluentcart-alert fluentcart-alert-success" role="alert">' + response.data + '</div>', $noticesWrapper);
                    reloadPage();
                }
            },
            error: function () {
                clearNotices($noticesWrapper);
                showNotice('<div class="fluentcart-alert fluentcart-alert-error">' + gamipress_fluentcart_partial_payments.error_generic + '</div>', $noticesWrapper);
            }
        });
    });

    // -----------------------------------------------------------------------
    // Utility: conversion preview
    // -----------------------------------------------------------------------

    function updatePreview() {

        var $form        = $('.' + prefix + 'form');
        var pointsType   = $form.find('*[name="points_type"]').val();
        var ptData       = gamipress_fluentcart_partial_payments.points_types[pointsType];

        if ( ! ptData ) return;

        var points        = parseInt( $form.find('*[name="' + pointsType + '_points"]').val(), 10 ) || 0;
        var pluralName    = ptData.plural_name;
        var conversionRate = ptData.conversion.money / ptData.conversion.points;
        var money          = points * conversionRate;

        var decimals          = gamipress_fluentcart_partial_payments.decimals;
        var decimalSeparator  = gamipress_fluentcart_partial_payments.decimal_separator;
        var thousandSeparator = gamipress_fluentcart_partial_payments.thousand_separator;

        $('.' + prefix + 'preview-points').text(points);
        $('.' + prefix + 'preview-points-type').text(pluralName);
        $('.' + prefix + 'preview-money').text(numberFormat(money, decimals, decimalSeparator, thousandSeparator));
    }

    // -----------------------------------------------------------------------
    // Utility: number formatting (mirrors PHP number_format)
    // -----------------------------------------------------------------------

    function numberFormat(number, decimals, decSep, thouSep) {

        decimals = Math.abs(decimals);
        decimals = isNaN(decimals) ? 2 : decimals;

        var sign     = number < 0 ? '-' : '';
        number       = Math.abs(Number(number) || 0);

        var strNum   = parseInt(number.toFixed(decimals)).toString();
        var thousands = strNum.length > 3 ? strNum.length % 3 : 0;

        return sign
            + (thousands ? strNum.substr(0, thousands) + thouSep : '')
            + strNum.substr(thousands).replace(/(\d{3})(?=\d)/g, '$1' + thouSep)
            + (decimals ? decSep + Math.abs(number - strNum).toFixed(decimals).slice(2) : '');
    }

    // -----------------------------------------------------------------------
    // Utility: UI helpers
    // -----------------------------------------------------------------------

    function blockForm($form) {
        $form.addClass('processing');
        $form.find('button[type="button"], button[type="submit"]').prop('disabled', true);
    }

    function unblockForm($form) {
        $form.removeClass('processing');
        $form.find('button[type="button"], button[type="submit"]').prop('disabled', false);
    }

    function reloadPage() {
        window.location.reload();
    }

    function clearNotices($wrapper) {
        $wrapper.find('.fluentcart-alert').remove();
    }

    function showNotice(html, $target) {
        if (!$target || !$target.length) {
            $target = $('.' + prefix + 'notices');
        }
        $target.prepend(html);
    }

    // -----------------------------------------------------------------------
    // Init: run preview once on load
    // -----------------------------------------------------------------------

    $(function () {
        if (Object.keys(gamipress_fluentcart_partial_payments.points_types).length) {
            updatePreview();
        }
    });

})(jQuery);
