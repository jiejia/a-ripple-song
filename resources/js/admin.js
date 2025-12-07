/**
 * Admin JavaScript for theme options
 */

(function($) {
    'use strict';

    // Theme color definitions
    const themeColors = {
        'light': { primary: '#570df8', secondary: '#f000b8', accent: '#37cdbe', neutral: '#3d4451' },
        'dark': { primary: '#661AE6', secondary: '#D926AA', accent: '#1FB2A5', neutral: '#191D24' },
        'cupcake': { primary: '#65c3c8', secondary: '#ef9fbc', accent: '#eeaf3a', neutral: '#291334' },
        'bumblebee': { primary: '#e0a82e', secondary: '#f9d72f', accent: '#181830', neutral: '#181830' },
        'emerald': { primary: '#66cc8a', secondary: '#377cfb', accent: '#ea5234', neutral: '#333c4d' },
        'corporate': { primary: '#4b6bfb', secondary: '#7b92b2', accent: '#67cba0', neutral: '#181a2a' },
        'synthwave': { primary: '#e779c1', secondary: '#58c7f3', accent: '#f9cb28', neutral: '#221551' },
        'retro': { primary: '#ef9995', secondary: '#a4cbb4', accent: '#ebdc99', neutral: '#2e282a' },
        'cyberpunk': { primary: '#ff7598', secondary: '#75d1f0', accent: '#c07eec', neutral: '#181830' },
        'valentine': { primary: '#e96d7b', secondary: '#a991f7', accent: '#88dbdd', neutral: '#af4670' },
        'halloween': { primary: '#f28c18', secondary: '#6d3a9c', accent: '#51a800', neutral: '#1b1d1d' },
        'garden': { primary: '#5c7f67', secondary: '#ecf4e7', accent: '#5c7f67', neutral: '#5c7f67' },
        'forest': { primary: '#1eb854', secondary: '#1db88e', accent: '#1db8ab', neutral: '#19362d' },
        'aqua': { primary: '#09ecf3', secondary: '#966fb3', accent: '#ffe999', neutral: '#3b8ac4' },
        'lofi': { primary: '#0d0d0d', secondary: '#1a1a1a', accent: '#262626', neutral: '#0d0d0d' },
        'pastel': { primary: '#d1c1d7', secondary: '#f6cbd1', accent: '#b4e9d6', neutral: '#70acc7' },
        'fantasy': { primary: '#6e0b75', secondary: '#007ebd', accent: '#f28c18', neutral: '#1f2937' },
        'wireframe': { primary: '#b8b8b8', secondary: '#b8b8b8', accent: '#b8b8b8', neutral: '#b8b8b8' },
        'black': { primary: '#373737', secondary: '#373737', accent: '#373737', neutral: '#373737' },
        'luxury': { primary: '#ffffff', secondary: '#152747', accent: '#513448', neutral: '#331800' },
        'dracula': { primary: '#ff79c6', secondary: '#bd93f9', accent: '#ffb86c', neutral: '#414558' },
        'cmyk': { primary: '#45AEEE', secondary: '#E8488A', accent: '#FFF232', neutral: '#1a1a1a' },
        'autumn': { primary: '#8C0327', secondary: '#D85251', accent: '#D59B6A', neutral: '#826A5C' },
        'business': { primary: '#1C4E80', secondary: '#7C909A', accent: '#EA6947', neutral: '#23282E' },
        'acid': { primary: '#FF00F4', secondary: '#FF7400', accent: '#FFED00', neutral: '#FF0090' },
        'lemonade': { primary: '#519903', secondary: '#E9E92E', accent: '#E9E92E', neutral: '#191A3E' },
        'night': { primary: '#38bdf8', secondary: '#818CF8', accent: '#F471B5', neutral: '#1E293B' },
        'coffee': { primary: '#DB924B', secondary: '#6F4C3E', accent: '#263E3F', neutral: '#120C12' },
        'winter': { primary: '#047AFF', secondary: '#463AA2', accent: '#C148AC', neutral: '#394E6A' },
        'dim': { primary: '#9FE88D', secondary: '#FF7D6B', accent: '#C792E9', neutral: '#2A2E37' },
        'nord': { primary: '#5E81AC', secondary: '#81A1C1', accent: '#88C0D0', neutral: '#4C566A' },
        'sunset': { primary: '#FF865B', secondary: '#FD6585', accent: '#B387FA', neutral: '#1C1917' },
        'caramellatte': { primary: '#D2691E', secondary: '#8B4513', accent: '#CD853F', neutral: '#3E2723' },
        'silk': { primary: '#E0B0FF', secondary: '#DDA0DD', accent: '#DA70D6', neutral: '#4A4A4A' },
        'abyss': { primary: '#A6E22E', secondary: '#66D9EF', accent: '#F92672', neutral: '#0D1117' }
    };

    /**
     * Enhance theme radio fields with visual previews
     */
    function enhanceThemeFields() {
        $('.carbon-field-radio[data-field-name*="theme"]').each(function() {
            const $field = $(this);

            // Skip if already enhanced
            if ($field.hasClass('theme-enhanced')) {
                return;
            }

            $field.addClass('theme-enhanced');

            // Process each radio item
            $field.find('.carbon-radio-item').each(function() {
                const $item = $(this);
                const $label = $item.find('label');
                const themeName = $item.find('input[type="radio"]').val();
                const colors = themeColors[themeName] || {
                    primary: '#666',
                    secondary: '#888',
                    accent: '#aaa',
                    neutral: '#333'
                };

                // Create theme preview HTML
                const previewHtml = `
                    <div class="theme-preview-name">${themeName}</div>
                    <div class="theme-preview-colors">
                        <div class="theme-preview-color" style="background-color: ${colors.primary}">A</div>
                        <div class="theme-preview-color" style="background-color: ${colors.secondary}">A</div>
                        <div class="theme-preview-color" style="background-color: ${colors.accent}">A</div>
                        <div class="theme-preview-color" style="background-color: ${colors.neutral}">A</div>
                    </div>
                `;

                // Replace label content with preview
                $label.html(previewHtml);

                // Make entire card clickable
                $item.on('click', function(e) {
                    if (!$(e.target).is('input[type="radio"]')) {
                        $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
                    }
                });
            });
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        enhanceThemeFields();

        // Re-enhance when Carbon Fields updates the DOM
        $(document).on('carbon-fields.field-added', function() {
            enhanceThemeFields();
        });
    });

})(jQuery);
