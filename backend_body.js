if(typeof jQuery != 'undefined')
{
    jQuery(document).ready(function($)
    {
        if(typeof $.ui.sortable !== 'undefined') {
            $('tbody.ui-sortable').sortable({
                items: 	'tr.draggable',
                update: function(event, ui) {
                    if(ui.item.hasClass('drag_group')) {
                        var data = {
                            group_id: ui.item.data('group-id'),
                            prev_id : ui.item.prev().data('group-id')
                        };
                    }
                    else {
                        var data  = {
                            item_id : ui.item.data('item-id'),
                            group_id: ui.item.prev().data('group-id'),
                            prev_id : ui.item.prev().data('item-id')
                        };
                    }
                    // for debugging you may activate this:
                    //console.log(data);
                    $.post(
                        DLGDRAGDROP,
                        data
                    );
    			}
    		});
        } else {
            $('.dragdrop_handle').removeClass('dragdrop_handle');
        }
        if( typeof $('input#title').val() != 'undefined') {
            if($('input#title').val().length) {
                $('span#use_filename_span').hide();
            }
            $('input#use_filename').change(function () {
                 $('input#title').toggle(!this.checked);
            }).change(); //ensure visible state matches initially
        }
    });
}