$(document).ready(function() {
    // Your code in here  	
    // console.log("XXXX");
    // if(jQuery('#nav-wrap .calus a').text().includes("519-852-8600")){
    //     console.log("String Found");
    //     jQuery('#nav-wrap .calus a').attr("href", "tel:5198528900");
    //     jQuery('#nav-wrap .calus a').text("519-852-8900");
    // }

    jQuery('#nav-wrap .calus a').first().replaceWith(function() {
        return '<a href="tel:5198528900">519-852-8900</a>';
    });


    $('#nav-wrap .calus').find('a').each(function() {
        console.log($(this).attr('href'));
    });

});