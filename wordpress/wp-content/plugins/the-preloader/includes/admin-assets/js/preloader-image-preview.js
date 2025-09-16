document.addEventListener('DOMContentLoaded', function() {

    function thePreloaderImagePreview(){
        // Elements
        const previewImage = document.getElementById('preloader_preview_image');
        const previewWrap = document.getElementById('preloader_preview_wrap');
        const imageUrlInput = document.getElementById('preloader_image_url');
        const bgColorInput = document.getElementById('preloader_bg_color');
        const widthInput = document.getElementById('preloader_width');
        const heightInput = document.getElementById('preloader_height');
        const removeButton = document.getElementById('preloader_remove_btn');

        // Default values
        const defaults = {
            backgroundColor: '#f8f9fa',
            width: '64',
            height: '64'
        };

        // Apply initial values
        function applyInitialValues() {
            // Apply background color
            previewWrap.style.backgroundColor = bgColorInput?.value || defaults.backgroundColor;
            if (!bgColorInput.value) {
                bgColorInput.value = defaults.backgroundColor;
            }

            // Apply image and dimensions if exists
            if (imageUrlInput?.value) {
                previewImage.src = imageUrlInput.value;
                previewWrap.style.display = 'block';
                removeButton.style.display = 'inline-block';
                
                // Apply dimensions with defaults
                previewImage.style.width = (widthInput?.value || defaults.width) + 'px';
                previewImage.style.height = (heightInput?.value || defaults.height) + 'px';
                
                // Set default values if empty
                if (!widthInput.value) widthInput.value = defaults.width;
                if (!heightInput.value) heightInput.value = defaults.height;
            } else {
                previewWrap.style.display = 'none';
                removeButton.style.display = 'none';
            }
        }

        // Update image URL
        imageUrlInput?.addEventListener('input', function() {
            if (this.value) {
                previewImage.src = this.value;
                previewWrap.style.display = 'block';
                removeButton.style.display = 'inline-block';
                // Apply default dimensions for new images
                if (!widthInput.value) {
                    widthInput.value = defaults.width;
                    previewImage.style.width = defaults.width + 'px';
                }else{
                    previewImage.style.width = widthInput.value + 'px';
                }

                if (!heightInput.value) {
                    heightInput.value = defaults.height;
                    previewImage.style.height = defaults.height + 'px';
                }else{
                    previewImage.style.height = heightInput.value + 'px';
                }
            } else {
                previewWrap.style.display = 'none';
                removeButton.style.display = 'none';
            }
        });

        // Update background color
        bgColorInput?.addEventListener('input', function() {
            previewWrap.style.backgroundColor = this.value || defaults.backgroundColor;
        });

        // Update image width
        widthInput?.addEventListener('input', function() {
            previewImage.style.width = (this.value || defaults.width) + 'px';
        });

        // Update image height
        heightInput?.addEventListener('input', function() {
            previewImage.style.height = (this.value || defaults.height) + 'px';
        });

        // Remove image
        removeButton?.addEventListener('click', function() {
            imageUrlInput.value = '';
            previewImage.src = '';
            previewWrap.style.display = 'none';
            this.style.display = 'none';
        });

        // Apply initial values when page loads
        applyInitialValues();
    }

    thePreloaderImagePreview();
});