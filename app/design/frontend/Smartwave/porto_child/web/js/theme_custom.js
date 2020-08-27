/*
 * Custom theme js file
 */

require([
    'jquery'
], function ($) {
    $(document).ready(function(){


        // qty increase in search result > add to cart section
        $(document).on("click", ".qty-inc-search", function(e) {
            e.preventDefault();
            if($(this).parents('.field.qty').find("input.input-text.qty").is(':enabled')){
                $(this).parents('.field.qty').find("input.input-text.qty").val((+$(this).parents('.field.qty').find("input.input-text.qty").val() + 1) || 0);
                $(this).parents('.field.qty').find("input.input-text.qty").trigger('change');
                $(this).focus();
            }
        });
        
        
        // qty decrease in search result > add to cart section
        $(document).on("click", ".qty-dec-search", function(e) {
            e.preventDefault();
            if($(this).parents('.field.qty').find("input.input-text.qty").is(':enabled')){
                $(this).parents('.field.qty').find("input.input-text.qty").val(($(this).parents('.field.qty').find("input.input-text.qty").val() - 1 > 0) ? ($(this).parents('.field.qty').find("input.input-text.qty").val() - 1) : 0);
                $(this).parents('.field.qty').find("input.input-text.qty").trigger('change');
                $(this).focus();
            }
        });
        
        

        
    });
});

