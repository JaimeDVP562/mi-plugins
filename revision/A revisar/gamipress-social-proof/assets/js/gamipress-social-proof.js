jQuery(document).ready(function($) {

    function loadFomoMessage() {

        // Random delay 4 - 10 seconds
        const delay = Math.floor(Math.random() * 9000) + 4000;

        setTimeout(() => {
            // Get message via AJAX
            $.ajax({
                url: fomo_ajax.url,
                type: "GET",
                data: { action: "gamipress_social_proof_fomo_send_message_AJAX" },
                dataType: "json",
                success: function(data) {
                
                    if (data.message) {

                        const container = $("#message-container");

                        // Empty the message
                        container.empty();

                        // We introduce the message to WordPress DOM
                        container.append(data.message);

                        // Find the element via ShortCode
                        const box = container.find(".fomo-message");

                        // If exist 'show' class
                        box.removeClass("show");

                        // We corrected the animation
                        setTimeout(() => {
                            box.addClass("show");
                        }, 50);

                        // Hide the message after 4 seconds
                        setTimeout(() => {
                            box.removeClass("show");
                        }, 4000);
                    }
                }
            });

        }, delay);
    }
    // First notification
    loadFomoMessage();

    // Each 8 seconds, generate new single message (With delay)
    setInterval(loadFomoMessage, 8000);
});
