/**
 * Created by dell on 2016-06-15.
 */

function settopnavMenu(nowid) {
    var container = $(".topnav_list"),
        scrollTo = $('#t' + nowid);
    if(scrollTo.length > 0) {
        var leftnumb = scrollTo.offset().left - container.offset().left + container.scrollLeft() - 40;
        container.scrollLeft(
            leftnumb
        );
        container.animate({
            scrollLeft: leftnumb
        });
    }
}