document.addEventListener('DOMContentLoaded', function() {
    function thePreloaderColorField() {
        // Color input fields
        const colorFields = [
            document.querySelector('#background-color-picker'),
            document.querySelector('#fill-color-picker'),
            document.querySelector('#preloader_bg_color')
        ];

        // Handle double click for each field
        colorFields.forEach(field => {
            if (!field) return;

            field.addEventListener('dblclick', function() {
                if (this.type === 'color') {
                    // Change to text input
                    this.type = 'text';
                    this.value = this.value.toUpperCase();
                } else {
                    // Change back to color input
                    if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                        this.type = 'color';
                    } else {
                        this.value = '#';
                        alert('Please enter a valid HEX color code (e.g., #FF0000)');
                    }
                }
            });

            // Validate input when typing in text mode
            field.addEventListener('input', function() {
                if (this.type === 'text') {
                    // Convert to uppercase
                    this.value = this.value.toUpperCase();
                }
            });
        });
    }

    thePreloaderColorField();
});