(function($){

        // To simulate ajaxSuccess to check notifications
        function simulate_ajax_success(url) {
            $(document).trigger('ajaxSuccess', [
                { status: 200, readyState: 4 },
                { url: url }
            ]);
        }

        // To detect fluent_community_call
        function is_fluent_community_call(url) {
            return typeof url === 'string' && url.includes('/wp-json/fluent-community/');
        }

        (function() {
            const origOpen = XMLHttpRequest.prototype.open;
            const origSend = XMLHttpRequest.prototype.send;

            XMLHttpRequest.prototype.open = function(method, url) {
                this._gm_url = url;
                return origOpen.apply(this, arguments);
            };

            XMLHttpRequest.prototype.send = function() {
                const url = this._gm_url;
                this.addEventListener('loadend', () => {
                    if (is_fluent_community_call(url)) {
                        simulate_ajax_success(url);
                    }
                });
                return origSend.apply(this, arguments);
            };
        })();

    })(jQuery);