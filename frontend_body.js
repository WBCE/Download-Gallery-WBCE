document.addEventListener("DOMContentLoaded", function(e) {
    var filter = document.querySelectorAll(".filter");
    var elems  = document.querySelectorAll(".dlg3item");
    if(filter.length) {
        filter.forEach(function(item) {
            item.addEventListener("keyup", function(f) {
                var target = f.target || f.srcElement;
                var filter = target.value;
                elems.forEach( function(elem) {
                    elem.classList.remove("dlg3hide");
                });
                if(filter.length) {
                    elems.forEach( function(elem) {
                        if(elem.textContent.indexOf(filter)==-1) {
                            elem.classList.add("dlg3hide");
                        }
                    });
                }
            });
        });
    }
});

    