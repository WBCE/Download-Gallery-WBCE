console.log(typeof jQuery);

if ( typeof jQuery == 'undefined' ) {
    var fileref=document.createElement("script")
    fileref.setAttribute("type", "text/javascript");
    fileref.setAttribute("src", WB_URL+"/include/jquery/jquery-min.js");
    if (typeof fileref!="undefined") {
        document.getElementsByTagName("head")[0].appendChild(fileref);
    }
}

var maxwait = 5000;
dlgJQueryDefer(0);

function dlgJQueryDefer(waittime) {
    waittime = waittime + 100;
    if(waittime>=maxwait) {
         alert('no jQuery in 5000 milliseconds...');
    }
    else {
console.log('Waiting for jQuery...');
        if (window.jQuery) {
            if ( typeof jQuery.ui == 'undefined' ) {
                var fileref=document.createElement("script")
                fileref.setAttribute("type", "text/javascript");
                fileref.setAttribute("src", WB_URL+"/include/jquery/jquery-ui-min.js");
                if (typeof fileref!="undefined") {
                    document.getElementsByTagName("head")[0].appendChild(fileref);
                }
            }
        }
        else {
            setTimeout(function() { dlgJQueryDefer(waittime) }, 100);
        }
    }
}