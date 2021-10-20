// jQuery(document).ready(function() {
//     jQuery(".woocommerce-additional-fields").prepend('<input type="checkbox" id="noTip" value="No Tip" name="tipBox">');
//     jQuery("#noTip").append('<label for="noTip">No Tip</label>');
    
//     // (`<label for="${value}">${value}</label></div>`)
//     // $('#btnSave').click(function() {
//     //     addCheckbox($('#txtName').val());
//     // });
// });

// function addCheckbox(name) {
//    var container = $('#cblist');
//    var inputs = container.find('input');
//    var id = inputs.length+1;

//    $('<input />', { type: 'checkbox', id: 'cb'+id, value: name }).appendTo(container);
//    $('<label />', { 'for': 'cb'+id, text: name }).appendTo(container);
// }


$(document).ready(function() {
    // $('#submit').click(function() {
        // $('#container')
        //     .append(`<input type="checkbox" id="XXX" name="interest" value="XXX">`)
        //     .append(`<label for="XXX">XXXXXXXXXX</label></div>`)
        //     .append(`<br>`);
        $('.woocommerce-additional-fields').prepend('<input type="checkbox" id="noTip" value="No Tip" name="tipBox" value="AAA">').prepend('<label for="noTip">No Tip</label></div>');
        $('#noTip').click(function() {
            $('#wooot_order_tip_form').toggle();
        });

        // var list = ['Car', 'Bike', 'Scooter'];
        // for (var value of list) {
        //   $('#container')
        //     .append(`<input type="checkbox" id="${value}" name="interest" value="${value}">`)
        //     .append(`<label for="${value}">${value}</label></div>`)
        //     .append(`<br>`);
        // }
    // })
});