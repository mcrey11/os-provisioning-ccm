{{-- Reload when qualified_model_class field changes --}}
{{-- Cannot be done by hide/show because the custom fields may change --}}
<script language="javascript">

    $('#qualified_model_class').change(function() {
        let selectfield = document.getElementById("qualified_model_class");
        let selectedValue = selectfield.options[selectfield.selectedIndex].value;

        // Skip if no value is selected
        if (!selectedValue) {
            return;
        }

        // Split by backslash and get the last part
        let parts = selectedValue.split('\\');
        let extractedString = parts[parts.length - 1];

        // Get current URL
        let currentUrl = new URL(location);
        let pathParts = currentUrl.pathname.split('/');

        // Find the index of 'admin' in the path
        let adminIndex = pathParts.indexOf('admin');
        if (adminIndex !== -1 && adminIndex + 1 < pathParts.length) {
            // Replace the part after 'admin' with the extracted string
            pathParts[adminIndex + 1] = extractedString;
            currentUrl.pathname = pathParts.join('/');
        }

        // Update the qualified_model_class parameter
        let urlParams = new URLSearchParams(currentUrl.search);
        urlParams.set('qualified_model_class', selectedValue);

        // Preserve all form field data
        preserveFormData(urlParams);

        currentUrl.search = urlParams.toString();

        console.log('Redirecting to:', currentUrl.toString());

        // Redirect to the new URL
        location.href = currentUrl.toString();
    });

    /**
     * Generic function to preserve all form field data in URL parameters
     * @param {URLSearchParams} urlParams - The URLSearchParams object to add form data to
     */
    function preserveFormData(urlParams) {
        // Fields to ignore when preserving form data
        const ignoredFields = ['configfile_id', 'qos_id'];

        // Get all form elements on the page
        const forms = document.querySelectorAll('form');

        forms.forEach(function(form) {
            // Get all form inputs, selects, and textareas
            const formElements = form.querySelectorAll('input, select, textarea');

            formElements.forEach(function(element) {
                // Skip elements without name attribute or disabled elements
                if (!element.name || element.disabled) {
                    return;
                }

                // Skip ignored fields
                if (ignoredFields.includes(element.name)) {
                    return;
                }

                let value = '';

                // Handle different input types
                if (element.type === 'checkbox') {
                    // Only include checked checkboxes
                    if (element.checked) {
                        value = element.value || 'on';
                    } else {
                        return; // Skip unchecked checkboxes
                    }
                } else if (element.type === 'radio') {
                    // Only include checked radio buttons
                    if (element.checked) {
                        value = element.value;
                    } else {
                        return; // Skip unchecked radio buttons
                    }
                } else if (element.type === 'file') {
                    // Skip file inputs for security reasons
                    return;
                } else if (element.tagName === 'SELECT') {
                    // Handle select elements
                    if (element.multiple) {
                        // For multiple select, get all selected options
                        const selectedOptions = Array.from(element.selectedOptions).map(option => option.value);
                        if (selectedOptions.length > 0) {
                            selectedOptions.forEach(optionValue => {
                                urlParams.append(element.name, optionValue);
                            });
                        }
                        return; // Skip the default handling below
                    } else {
                        value = element.value;
                    }
                } else {
                    // For text inputs, textareas, and other input types
                    value = element.value;
                }

                // Only add non-empty values (except for checkboxes which are handled above)
                if (value !== '' && value !== null && value !== undefined) {
                    urlParams.set(element.name, value);
                }
            });
        });
    }

</script>
