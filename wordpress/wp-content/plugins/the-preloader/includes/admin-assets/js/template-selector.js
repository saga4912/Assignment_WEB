document.addEventListener('DOMContentLoaded', function() {
    function thePreloaderTemplateSelector(){
        // Elements
        const templateItems = document.querySelectorAll('.template-item input[type="radio"]');
        const bgColorPicker = document.querySelector('.background-color-picker');
        const fillColorPicker = document.querySelector('.fill-color-picker');
        const scaleRange = document.querySelector('.scale-range');
        const scaleValue = document.querySelector('.scale-value');
        const resetButton = document.querySelector('.reset-colors');
        const typingTextField = document.querySelector('#typing-effect-text-field');
        
        // Create single style element
        const styleElement = document.createElement('style');
        document.head.appendChild(styleElement);

        // Default values
        const defaultValues = {
            background: '#f8f9fa',
            fill: '#3498db',
            scale: 1,
            typingText: 'Loading...'
        };

        // Store current values
        let currentValues = {
            background: bgColorPicker ? bgColorPicker.value : defaultValues.background,
            fill: fillColorPicker ? fillColorPicker.value : defaultValues.fill,
            scale: scaleRange ? parseFloat(scaleRange.value) : defaultValues.scale,
            typingText: typingTextField ? typingTextField.value : defaultValues.typingText
        };

        // Mode switching
        let isPreviewMode = false;

        // Update styles function
        function updateStyles() {
            const css = isPreviewMode ? `
                /* Preview mode - all templates */
                .template-preview {
                    background-color: ${currentValues.background};
                }
                .template-preview > div {
                    transform: scale(${currentValues.scale});
                }
                .template-preview * {
                    --fill-color: ${currentValues.fill};
                }
            ` : `
                /* Saved mode - active template only */
                .template-item.thp-tab-active .template-preview {
                    background-color: ${currentValues.background};
                }
                .template-item.thp-tab-active .template-preview > div{
                    transform: scale(${currentValues.scale});
                }
                .template-item.thp-tab-active .template-preview * {
                    --fill-color: ${currentValues.fill};
                }
            `;
            
            styleElement.textContent = css;
        }

        // Template selection handler
        templateItems.forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.template-item').forEach(item => {
                    item.classList.remove('thp-tab-active');
                });
                this.closest('.template-item').classList.add('thp-tab-active');
                isPreviewMode = false;
                updateStyles();
            });
        });

        // Color change handlers
        [bgColorPicker, fillColorPicker].forEach(picker => {
            if (picker) {
                picker.addEventListener('input', (e) => {
                    const type = e.target.classList.contains('background-color-picker') ? 'background' : 'fill';
                    currentValues[type] = e.target.value;
                    isPreviewMode = true;
                    updateStyles();
                });
            }
        });

        // Scale change handler
        if (scaleRange) {
            scaleRange.addEventListener('input', (e) => {
                currentValues.scale = parseFloat(e.target.value);
                scaleValue.textContent = currentValues.scale.toFixed(1);
                isPreviewMode = true;
                updateStyles();
            });
        }

        // Reset handler
        if (resetButton) {
            resetButton.addEventListener('click', () => {
                // Update inputs
                if (bgColorPicker) {
                    bgColorPicker.value = defaultValues.background;
                }
                if (fillColorPicker) {
                    fillColorPicker.value = defaultValues.fill;
                }
                if (scaleRange) {
                    scaleRange.value = defaultValues.scale;
                    scaleValue.textContent = defaultValues.scale.toFixed(1);
                }

                // Update stored values
                currentValues = { ...defaultValues };
                
                // Preview changes
                isPreviewMode = true;
                updateStyles();
            });
        }

        // Add typing text change handler
        if (typingTextField) {
            typingTextField.addEventListener('input', (e) => {
                currentValues.typingText = e.target.value || defaultValues.typingText;
                const typingEffectTemplate = document.querySelector('.template-preview .typing-effect_preloader-template');
                if (typingEffectTemplate) {
                    typingEffectTemplate.textContent = currentValues.typingText;
                }
            });
        }

        // Initial state
        updateStyles();
    }

    thePreloaderTemplateSelector();
});