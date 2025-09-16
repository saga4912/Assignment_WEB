document.addEventListener('DOMContentLoaded', function() {
    function thePreloaderDisplayLocations() {
        // Elements
        const entireSite = document.querySelector('input[value="entire"]');
        const entireSiteExWoo = document.querySelector('input[value="entire_ex_woo"]');
        const generalOptions = document.querySelectorAll('input[name="the_preloader_settings[display_locations][]"]:not([value="entire"]):not([value="entire_ex_woo"])');

        // Ensure elements exist
        if (!entireSite || !entireSiteExWoo) return;

        function disableOptions(options) {
            options.forEach(option => {
                option.checked = false;
                option.disabled = true;
            });
        }

        function enableOptions(options) {
            options.forEach(option => {
                option.disabled = false;
            });
        }

        function handleEntireSiteOptions() {
            if (entireSite.checked || entireSiteExWoo.checked) {
                // Disable all other options when any entire site option is selected
                disableOptions(generalOptions);
                
                // If one entire site option is checked, uncheck and disable the other
                if (entireSite.checked) {
                    entireSiteExWoo.checked = false;
                    entireSiteExWoo.disabled = true;
                } else {
                    entireSite.checked = false;
                    entireSite.disabled = true;
                }
            } else {
                // Enable all options when no entire site option is selected
                enableOptions(generalOptions);
                entireSite.disabled = false;
                entireSiteExWoo.disabled = false;
            }
        }

        // Set initial state
        handleEntireSiteOptions();

        // Add event listeners
        entireSite.addEventListener('change', handleEntireSiteOptions);
        entireSiteExWoo.addEventListener('change', handleEntireSiteOptions);
    }

    // Initialize
    thePreloaderDisplayLocations();
});