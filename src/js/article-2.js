(function($) {
    "use strict";

    let reviewBox;
    let lastChild;

    $(document).ready(function() {
        reviewBox = $('#review-box');
        lastChild = $(reviewBox).find('.review-entry:last-child');

        $('#add-rev-book').click(function(e) {
            e.stopPropagation();
            addNewReviewBox();
        });

        setDeleteListeners();
    });


    function addNewReviewBox() {
        const patt = /\-(\d+$)/;
        let newIdx = parseInt(patt.exec($(lastChild).attr('id'))) + 1;

        const fields = [
            {
                name: 'auth-rev',
                label: 'Author of Reviewed Work'
            },
            {
                name: 'title-rev',
                label: 'Title of Reviewed Work'
            },
            {
                name: 'url-rev',
                label: 'URL for Reviewed Work',
                type: 'url'
            }
        ];

        const newDiv = $('<div></div>').attr({class: 'review-entry'});

        $(newDiv).append(
            $('<button></button>')
                .attr({type: 'button', class: 'button button-danger rev-delete', id: 'delete-rev-book-' + newIdx})
            .append(
                $('<span></span>')
                    .attr({class: 'book-del-icon dashicons dashicons-trash'})
            )
        );

        const newTable = $('<table></table>');

        fields.forEach(item => {
            const tRow = $('<tr></tr>')
                .append(
                    $('<td></td>').append(
                        $('<label></label>')
                            .attr({for: item.name + newIdx})
                            .html(item.label + ': ')
                    )
                )
                .append(
                    $('<td></td>').append(
                        $('<input></input>')
                            .attr({type: (item.type == 'url' ? item.type : 'text' ), name: item.name + '[]', id: item.name + newIdx, size: 50})
                    )
                );
            
            newTable.append(tRow);
        });

        newDiv.append(newTable);

        $('#add-rev-book').before(newDiv);

        setDeleteListeners();
        lastChild = newDiv;
    }


    function setDeleteListeners() {
        $('.rev-delete').click(function(e) {
            e.stopPropagation();
            removeReviewBox($(this).parent());
        });
    }


    function removeReviewBox(elem) {
        if( $(elem).siblings().size() > 1 ) {
            $(elem).remove();
        }
        else {
            $(elem).find('input').each(function() {
                $(this).val('');
            });
        }
    }
})(jQuery);