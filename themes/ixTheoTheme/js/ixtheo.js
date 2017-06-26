// helper function to set focus on a specificed input field id, also sets cursor position to end of field content
function ixtheoSetFocus(input_id) {
    var id = '#' + input_id;
    $(id).focus();
    if ($(id).length) {
       // now we are sure that element exists
       // don't assign input var earlier, JS might crash if element doesnt exist
       var input = $(id);
       input[0].setSelectionRange(input.val().length, input.val().length);
    }
}

// onload handler for ixtheo
function ixtheoOnLoad() {
    // keywordchainsearch: set focus on 2nd input field
    if (window.location.href.match(/\/Keywordchainsearch\//i)) {
        ixtheoSetFocus('kwc_input');
    }
}

// add ixtheo onload handler. make sure that current onload handler is called first, if existing
if(window.attachEvent) {
    window.attachEvent('onload', ixtheoOnLoad);
} else {
    if(window.onload) {
        var currentOnLoad = window.onload;
        var newOnLoad = function(evt) {
            currentOnLoad(evt);
            ixtheoOnLoad(evt);
        };
        window.onload = newOnLoad;
    } else {
        window.onload = ixtheoOnLoad;
    }
}
