jQuery(function () {
    jQuery("div.scroll-wrapper").each(function () {
        var container = jQuery(this);
        var parent = container.parent();
        var photosTable = container.find("table").first();
        var photosRow = photosTable.find("tr.sr-photos > td");

        var photosCount = photosRow.length;
        var visibleWidth = container.innerWidth();
        var photoWidth = photosRow.first().outerWidth();
        var photosInContainer = Math.round(visibleWidth / photoWidth);

        var sayContainers = parent.find("div.sr-what-say");

        var teacherSayContainer = jQuery(sayContainers[0]);
        var familySayContainer = jQuery(sayContainers[1]);

        container.scroll(function (event) {
            var currentScroll = container.scrollLeft();

            var startingPhoto = Math.round(currentScroll / photoWidth);
            var endingPhoto = Math.min(photosCount, startingPhoto + photosInContainer - 1);

            console.log("Showing photos: [" + startingPhoto + " - " + endingPhoto + "]");

            //Hide all captions except the ones that presented
            sayContainers.find("p.sr-content").css("display", "none");

            teacherSayContainer.find("p.sr-content").slice(startingPhoto, endingPhoto).filter(function (i, element) {
                var htmlstring = element.innerHTML;

                // use the native .trim() if it exists
                //   otherwise use a regular expression
                htmlstring = (htmlstring.trim) ? htmlstring.trim() : htmlstring.replace(/^\s+/, '');
                return (htmlstring != "");
            }).css("display", "block");

            familySayContainer.find("p.sr-content").slice(startingPhoto, endingPhoto).filter(function (i, element) {
                var htmlstring = element.innerHTML;

                // use the native .trim() if it exists
                //   otherwise use a regular expression
                htmlstring = (htmlstring.trim) ? htmlstring.trim() : htmlstring.replace(/^\s+/, '');
                return (htmlstring != "");
            }).css("display", "block");
        });

        //Scroll to the end
        container.scrollLeft(photosCount * photoWidth);
    });
});