(function ( $ ) {

    var config = {
        type: "line",
        data: []
    };

    shortlinkspro_refresh_chart( $('#shortlinkspro-clicks-chart') );

    // On change period
    $('.button.shortlinkspro-period').on('click', function() {
        var $this = $(this);
        var period = $this.val();
        var controls = $this.closest('.shortlinkspro-clicks-chart-controls');
        var custom_period_controls = controls.find('.shortlinkspro-clicks-chart-custom-period-controls');

        if( period === 'custom' ) {
            custom_period_controls.slideDown('fast');
        } else {
            custom_period_controls.slideUp('fast');
        }

        controls.find('.button-primary').removeClass('button-primary');
        $this.addClass('button-primary');

        shortlinkspro_refresh_chart( $('#shortlinkspro-clicks-chart') );
    })

})( jQuery );

function shortlinkspro_chart_init( chart, config ) {

    // Initialize our global object
    if( window.shortlinkspro === undefined ) {
        window.shortlinkspro = {
            charts: {}
        };
    }

    var canvas = chart.find('canvas');
    var chart_id = canvas.attr('id');

    if( window.shortlinkspro.charts[chart_id] === undefined ) {

        // Create the chart instance
        window.shortlinkspro.charts[chart_id] = new Chart(canvas[0].getContext('2d'), {
            type: 'line',
            data: config.data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 0,
                        suggestedMax: 6,
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    }
                }
            }
        });

    } else {

        // Update the chart
        window.shortlinkspro.charts[chart_id].data = config.data;

        window.shortlinkspro.charts[chart_id].update();

    }

}

function shortlinkspro_refresh_chart( chart ) {

    var $ = jQuery || $;

    shortlinkspro_chart_add_loader( chart );

    $.ajax({
        url: shortlinkspro_admin_clicks.ajaxurl,
        method: 'POST',
        cache: false,
        data: {
            action: chart.data('action'),
            nonce: shortlinkspro_admin_clicks.nonce,
            period: chart.prev('.shortlinkspro-clicks-chart-controls').find('.shortlinkspro-period.button-primary').val(),
            period_start: chart.prev('.shortlinkspro-clicks-chart-controls').find('#period_start').val(),
            period_end: chart.prev('.shortlinkspro-clicks-chart-controls').find('#period_end').val(),
            ip: chart.data('ip'),
            country: chart.data('country'),
            browser: chart.data('browser'),
            os: chart.data('os'),
            device: chart.data('device'),
            link_id: chart.data('link_id'),
        },
        success: function( response ) {

            shortlinkspro_chart_init( chart, response );

            shortlinkspro_chart_remove_loader( chart );

        }
    });

}

function shortlinkspro_chart_add_loader( chart ) {
    chart.append('<div class="shortlinkspro-chart-loader"><span class="spinner is-active shortlinkspro-chart-spinner"></span></div>')
}

function shortlinkspro_chart_remove_loader( chart ) {
    chart.find('.shortlinkspro-chart-loader').remove();
}