var NfProfilingShortcodeUI;

( function (nfShortCodeUI, $) {

    $(document).ready(function () {

        /**
         * Listen for the main button click action
         */
        $(document).on('click', '.nfprofiling_media_link', function () {
            
            tb_show("Insert Smart CTA", "#TB_inline?inlineId=select-smart-ctas&width=753&height=686", "");
        
        });

        /**
         * Listen for the form submit action
         */
        $(document).on('click', '#select-smart-ctas-submit', function () {
        
            ids_string = getSelections();

            clearSelections();
            
            if (ids_string) {
                wp.media.editor.insert('[smartcta id="' + ids_string + '"]');
            }

        });

        /**
         * Listen for the form cancel action
         */
        $(document).on('click', '#nfprofiling-cancel-shortcode', function () {
        
            tb_remove();
            clearSelections();
        
        });

    });

    /**
     * Uncheck all the options when the overlay closes
     */
    var clearSelections = function() {
        
        $('#select-smart-ctas-ui-container input[type="checkbox"]').each( function(){
            $(this).prop('checked', false);
        });
    
    };

    /**
     * Get a string of the selected options
     */
    var getSelections = function() {

        var ids = [];

        $('#select-smart-ctas-ui-container input[type="checkbox"]').each( function(){
    
            if ($(this).is(':checked')) {
    
                ids.push($(this).attr('data-id'));
            
            }
        });

        var ids_string = ids.join(',');

        return ids_string;
    };


}(window.nfShortcodeUI = window.nfShortcodeUI || {}, jQuery));