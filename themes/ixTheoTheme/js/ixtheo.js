// helper function to set focus on a specificed input field id, also sets cursor position to end of field content
// using combined JS and JQuery... hat problems setting cursor position with plain js / getting value length with jquery
function ixtheoSetFocus(input_id) {
    input = document.getElementById(input_id);
    if(input !== null) {
        input.focus();
        if(input.value.length > 0) {
            var input_jq = $('#' + input_id);
            input_jq[0].setSelectionRange(input.value.length, input.value.length);
        }
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