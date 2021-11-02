$(document).ready(function() {
        // Your code in here  	
    // $('#nav_menu-2 h2').replaceWith(function() {
    //     return '<div class="widget-title">' + $(this).text() + '</div>';
    // });
    jQuery('#nav_menu-2 h2, #nav_menu-3 h2').replaceWith(function() {
        return '<div style="font-weight: bold; font-size: 20px; font-weight: bold; color: #ffffff; margin-bottom: 30px !important; margin-top: 5px !important;">' + jQuery(this).text() + '</div>';
    });
});