if(typeof jQuery != 'undefined')
{
    jQuery(document).ready(function($)
    {
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
                    WB_URL+"/modules/download_gallery/dragdrop.php",
                    data,
                    function(acknowledgement){
						$("#downloadGalleryResult").html(acknowledgement).fadeIn("slow");
						$("#downloadGalleryResult").fadeOut(5500);
					});
			}
		});
    });
}