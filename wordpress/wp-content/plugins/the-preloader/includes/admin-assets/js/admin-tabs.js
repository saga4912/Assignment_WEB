document.addEventListener('DOMContentLoaded', function() {
    function thePreloaderSettingTabs() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        const submitButton = document.getElementById('tp-submit');

        function toggleSubmitButton(tabId) {
            if ( tabId === 'tab-faq' || tabId === 'tab-cookie' || tabId === 'tab-upgrade' ) {
                submitButton.style.display = 'none';
            } else {
                submitButton.style.display = 'block';
            }
        }

        function switchTab(tabId) {
            // Hide all tabs
            tabContents.forEach(content => {
                content.classList.remove('thp-tab-active');
            });
            tabButtons.forEach(btn => {
                btn.classList.remove('thp-tab-active');
            });

            // Show selected tab
            document.getElementById(tabId).classList.add('thp-tab-active');
            document.querySelector(`[data-tab="${tabId}"]`).classList.add('thp-tab-active');

            // Toggle submit button
            toggleSubmitButton(tabId);

            // Save active tab to localStorage
            localStorage.setItem('activePreloaderTab', tabId);
        }

        // Add click handlers
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                switchTab(button.dataset.tab);
            });
        });

        // Restore last active tab
        const tabList = ['tab-general', 'tab-display', 'tab-cookie', 'tab-templates', 'tab-integration', 'tab-faq', 'tab-upgrade'];
        const lastActiveTab = localStorage.getItem('activePreloaderTab');
        const activeTab = tabList.includes(lastActiveTab) ? lastActiveTab : 'tab-general';
        switchTab(activeTab);

        // Check initial state
        if (document.getElementById('tab-faq').classList.contains('thp-tab-active') 
            || document.getElementById('tab-cookie').classList.contains('thp-tab-active') 
            || document.getElementById('tab-upgrade').classList.contains('thp-tab-active') ) {
            submitButton.style.display = 'none';
        }
    }

    thePreloaderSettingTabs();
});