jQuery(document).ready(function () {
    jQuery('.chosen-select').chosen();

    if (jQuery('.select-loaded')) {
        var student_id = jQuery('.select-loaded').val();
        jQuery('.select-loaded').prop('disabled', true).trigger("chosen:updated");
        jQuery('.select-loaded').after("<input type='hidden' name='select-child' value='" + student_id + "'></input>");
    }

    jQuery('form#post').submit(function (event) {
        if (jQuery('#student_id').length) {
            if (!jQuery('#student_id').val()) {
                event.preventDefault();
                alert('Please choose a student!');
            }
        }

        if (jQuery('#select-child').length) {
            if (!jQuery('#select-child').val()) {
                event.preventDefault();
                alert('Please choose a student!');
            }
        }
    });


    jQuery('#sr-notify').click(function () {
        jQuery('#send_notify').val(1);
        jQuery('form#post input[type=submit]').click();
    });


    // Hide Essential Grid
    jQuery('#eg-meta-box').hide();

    //Scroll all the sliders to the left
    jQuery("div.sr-data-block").each(function () {
        jQuery(this).scrollLeft(99999);
    });

    //Enable dynamic photo adding for teacher initiated group reports
    // jQuery('.tgroup-one-more').click(function () {
    jQuery(document).on('click', '.tgroup-one-more', function () {
        var fieldset = jQuery(this).parent();
        var dummyElement = fieldset.find('.tgroup-photo-block[dummy]');
        var newId = fieldset.find('.tgroup-photo-block').size();

        dummyElement.clone().insertAfter(dummyElement);
        dummyElement.removeAttr("dummy");

        removeDisabled(dummyElement, '.t-photo-input');

        replaceMarkupWithinElement(dummyElement, "%id%", newId);

        dummyElement.show();
    });

    //Enable dynamic photo adding for parent reports
    jQuery('fieldset > input[type="button"]').click(function () {
        var fieldset = jQuery(this).parent();
        var photosTable = fieldset.find("div.sr-data-block");
        var scope = fieldset.attr("key");
        var currentDate = new Date();

        var currentDay = currentDate.getDate();
        var currentMonth = (currentDate.getMonth() + 1);
        var currentYear = currentDate.getFullYear();

        if (currentDay < 10) {
            currentDay = "0".concat(currentDay.toString());
        }

        if (currentMonth < 10) {
            currentMonth = "0".concat(currentMonth.toString());
        }

        var currentDateFormatted = currentDay + "." + currentMonth + "." + currentYear;
        var photosCount = photosTable.find("div.sr-photo-block").length;

        photosTable.append("<div class=\"sr-photo-block\">\
			<p class=\"sr-thumb\">\
			</p>\
			<p class=\"sr-upload\">\
			<input type=\"file\" name=\"photos[" + scope + "][" + photosCount + "][photo]\" />\
		</p>\
		<p class=\"sr-date\">\
		<input type=\"text\" class=\"my-datepicker\" name=\"photos[" + scope + "][" + photosCount + "][date]\" value=\"" + currentDateFormatted + "\" size=\"10\" maxlength=\"10\" />\
		</p>\
		<p class=\"sr-teachers-say\">\
		<label for=\"photos[" + scope + "][" + photosCount + "][teachers-say]\" >What my teachers say</label>\
		<textarea name=\"photos[" + scope + "][" + photosCount + "][teachers-say]\" rows=\"2\" cols=\"60\"></textarea>\
		</p>\
		<p class=\"sr-family-say\">\
			<label for=\"photos[" + scope + "][" + photosCount + "][family-say]\" >What my family say</label>\
		<textarea name=\"photos[" + scope + "][" + photosCount + "][family-say]\" rows=\"2\" cols=\"60\"></textarea>\
		</p>\
		</div>");
    });

    jQuery("select#student_id").change(function () {
        var name = jQuery(this).find("option:selected")[0].innerHTML;

        name = name.replace(/\[[a-zA-Z\s]+\]/, "").replace("*", "").trim();

        jQuery("input#portfolio-name").val(name);
    });

     jQuery('body').on('focus',".my-datepicker", function(){
        jQuery(this).datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
        // beforeShowDay: only_mondays
    });
    })

    jQuery("input.portfolio-button-insert-parent").click(function () {
        var insertTo = jQuery(this).parent().parent().find("td:nth-child(2) > input")[0];
        var parentName = jQuery("select#student_id option:selected").parent().attr("label");

        jQuery(insertTo).val(parentName);
    });

    var dataURLToBlob = function (dataURL) {
        var BASE64_MARKER = ';base64,';
        if (dataURL.indexOf(BASE64_MARKER) == -1) {
            var parts = dataURL.split(',');
            var contentType = parts[0].split(':')[1];
            var raw = parts[1];

            return new Blob([raw], {type: contentType});
        }

        var parts = dataURL.split(BASE64_MARKER);
        var contentType = parts[0].split(':')[1];
        var raw = window.atob(parts[1]);
        var rawLength = raw.length;

        var uInt8Array = new Uint8Array(rawLength);

        for (var i = 0; i < rawLength; ++i) {
            uInt8Array[i] = raw.charCodeAt(i);
        }

        return new Blob([uInt8Array], {type: contentType});
    }

    jQuery(document).on('change', '#SR_custom_meta_box input[type="file"]', function () {
        jQuery('body').append('<div id="hidden_image"></div>');

        if (jQuery(this)[0].files.length) {
            var f = {};
            f.name = jQuery(this).attr('name');

            var filesToUpload = jQuery(this)[0].files;
            var file = filesToUpload[0];
            f.type = file.type;

            var image = document.createElement("img");
            var reader = new FileReader();
            reader.onload = function (e) {
                var i = Math.round(Math.random() * 10000);
                image.src = e.target.result;
                image.className = 'image' + i;
                jQuery('#hidden_image').append(image);

                var width = jQuery('#hidden_image img.image' + i).width();
                var height = jQuery('#hidden_image img.image' + i).height();

                if (width < 30) {
                    var width_int = setInterval(function () {
                        if (jQuery('#hidden_image img.image' + i).width() > 30) {
                            clearInterval(width_int);

                            var width = jQuery('#hidden_image img.image' + i).width();
                            var height = jQuery('#hidden_image img.image' + i).height();
                            image_resize(width, height, f, image);
                        }
                    }, 50);
                } else {
                    image_resize(width, height, f, image);
                }
            }
            reader.readAsDataURL(file);
        }
    });

    function image_resize(width, height, f, image) {
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext("2d");
        ctx.drawImage(image, 0, 0);

        var MAX_WIDTH = 500;
        var MAX_HEIGHT = 500;

        if (width > height) {
            if (width > MAX_WIDTH) {
                height *= MAX_WIDTH / width;
                width = MAX_WIDTH;
            }
        } else {
            if (height > MAX_HEIGHT) {
                width *= MAX_HEIGHT / height;
                height = MAX_HEIGHT;
            }
        }

        canvas.width = width;
        canvas.height = height;
        var ctx = canvas.getContext("2d");
        ctx.drawImage(image, 0, 0, width, height);

        var dataurl = canvas.toDataURL(f.type);
        f.image = dataURLToBlob(dataurl);

        jQuery('input[name="' + f.name + '"]').parents('.sr-photo-block').find('.sr-thumb').html('<img alt="photo" src="' + dataurl + '" width="80" height="90" style="margin-bottom:50px">');
        jQuery('input[name="' + f.name + '"]').remove();

        image_send(f);
    }

    function image_send(file) {
        var post_id = jQuery('input[name="post_ID"]').val();

        var data = new FormData();
        data.append('action', 'save_report_images');
        data.append(file.name, file.image);
        data.append('post_id', post_id);

        jQuery.ajax({
            url: 'admin-ajax.php',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function (data) {
                //jQuery('form[name="post"]').append(data);
            }
        });
    }

    function get(name) {
        if (name = (new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)')).exec(location.search))
            return decodeURIComponent(name[1]);
    }

    var $ = jQuery;

    var plugin_root = "/wp-content/plugins/student-reports/";


    populate_experience_dates(true);
    populate_focus_group();

    var t_discoveries = $('#t-discoveries');

    var t_journal_entries = $('#t-journal-entries');

    //moso: should I change this to "display: hidden" in css file.
    t_discoveries.find('[dummy]').hide();

    t_journal_entries.find('[dummy]').hide();

    var journal = $('#entries');

    var entry_empty = '' +
        '<div class="entry" data-id="%id%">' +
        '<fieldset><legend>Journal entry %id%</legend>' +
        '<img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">' +
        '<div class="form">' +
        '<div><a href="javascript:void(0)" class="add_observation">Add observation</a></div>' +
        '</div>' +
        '</fieldset>' +
        '</div>';

    var observation_empty = '' +
        '<div class="observation" data-id="%id%">' +
        '<fieldset>' +
        '<img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">' +
        '<legend>Observation %id%</legend>' +
        '<div class="form">' +
        '<p>' +
        '<div>' +
        '<label>Attachment:</label>' +
        '<select class="att_type" name="entries[%entry%][observations][%id%][attachment_type]" id="">' +
        '<option value="photo">Photo</option>' +
        '<option value="video">Video</option>' +
        '<option value="sample">Sample of work</option>' +
        '</select>' +
        '</div>' +
        '<div class="attachment_type" data-type="photo">' +
        'Photo: <input type="file" name="entries[%entry%][observations][%id%][photo]">' +
        '</div>' +
        '<div class="attachment_type" data-type="video">' +
        'Video: <input type="text" name="entries[%entry%][observations][%id%][video]">' +
        '</div>' +
        '<div class="attachment_type" data-type="sample">' +
        'Sample of work: <input type="file" name="entries[%entry%][observations][%id%][sample]">' +
        '</div>' +
        '</p>' +
        '<p>' +
        '<label>Observation or Collaborative Link:</label>' +
        '<div>' +
        '<span>Date: </span>' +
        '<input class="my-datepicker" type="text" name="entries[%entry%][observations][%id%][collaborative_date]" size="10" maxlength="10" />' +
        '</div>' +
        '<div>' +
        '<span>Time: </span>' +
        '<input type="text" name="entries[%entry%][observations][%id%][collaborative_time]" size="5" maxlength="5" />' +
        '<select name="entries[%entry%][observations][%id%][collaborative_time_sign]">' +
        '<option value="am">am</option>' +
        '<option value="pm">pm</option>' +
        '</select>' +
        '</div>' +
        '<div>' +
        '<span>Place: </span>' +
        '<div><input type="radio" name="entries[%entry%][observations][%id%][place]" value="indoor" checked="checked"> Indoor</div>' +
        '<div><input type="radio" name="entries[%entry%][observations][%id%][place]" value="outdoor"> Outdoor</div>' +
        '</div>' +
        '<div>' +
        '<span>Text: </span>' +
        '<textarea name="entries[%entry%][observations][%id%][collaborative_text]" rows="10" cols="100"></textarea>' +
        '</div>' +
        '</p>' +
        '<p>' +
        '<label>Links to VEYLDF:</label>' +
        '<div><input type="checkbox" name="entries[%entry%][observations][%id%][veyldf][]" id="veyldf1" value="1" > Outcome 1: Identity</div>' +
        '<div><input type="checkbox" name="entries[%entry%][observations][%id%][veyldf][]" id="veyldf2" value="2" > Outcome 2: Community</div>' +
        '<div><input type="checkbox" name="entries[%entry%][observations][%id%][veyldf][]" id="veyldf3" value="3" > Outcome 3: Wellbeing</div>' +
        '<div><input type="checkbox" name="entries[%entry%][observations][%id%][veyldf][]" id="veyldf4" value="4" > Outcome 4: Learning</div>' +
        '<div><input type="checkbox" name="entries[%entry%][observations][%id%][veyldf][]" id="veyldf5" value="5" > Outcome 5: Communication</div>' +
        '</p>' +
        '<p>' +
        '<label>Interpretation:</label>' +
        '<textarea name="entries[%entry%][observations][%id%][interpretation]" rows="10" cols="100"></textarea>' +
        '</p>' +
        '<div><a href="javascript:void(0)" class="add_goal">Add goal</a></div>' +
        '</div>' +
        '</fieldset>' +
        '</div>';

    var goal_empty = '' +
        '<div class="goal" data-id="%id%">' +
        '<fieldset>' +
        '<img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">' +
        '<legend>Goal %id%</legend>' +
        '<div class="form">' +
        '<p>' +
        '<div>' +
        '<span>Start date: </span>' +
        '<input class="my-datepicker" type="text" name="entries[%entry%][observations][%observation%][goals][%id%][start_date]" size="10" maxlength="10" />' +
        '</div>' +
        '<div>' +
        '<span>Text: </span>' +
        '<textarea name="entries[%entry%][observations][%observation%][goals][%id%][text]" rows="10" cols="100"></textarea>' +
        '</div>' +
        '<div>' +
        '<span>Evaluation of Learning & Development: </span>' +
        '<textarea name="entries[%entry%][observations][%observation%][goals][%id%][evaluation]" rows="10" cols="100"></textarea>' +
        '</div>' +
        '<div>' +
        '<span>Date goal achieved: </span>' +
        '<input class="my-datepicker" type="text" name="entries[%entry%][observations][%observation%][goals][%id%][achieved_date]" size="10" maxlength="10" />' +
        '</div>' +
        '</p>' +
        '<div><a href="javascript:void(0)" class="add_experience">Add learning experience</a></div>' +
        '</div>' +
        '</fieldset>' +
        '</div>';

    var exp_empty = '' +
        '<div class="exp" data-id="%id%">' +
        '<fieldset>' +
        '<img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">' +
        '<legend>Learning Experience %id%</legend>' +
        '<div class="form">' +
        '<p>' +
        '<div>' +
        '<span>Program date: </span>' +
        '<input class="my-datepicker" type="text" name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][program_date]" size="10" maxlength="10" />' +
        '</div>' +
        '<div>' +
        '<span>Program text: </span>' +
        '<textarea name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][program_text]" rows="10" cols="100"></textarea>' +
        '</div>' +
        '<div>' +
        '<span>Learning/Content/Behavioural Objective: </span>' +
        '<textarea name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][objective]" rows="10" cols="100"></textarea>' +
        '</div>' +
        '<label>Follow-up Observation:</label>' +
        '<div>' +
        '<span>Date: </span>' +
        '<input class="my-datepicker" type="text" name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][observation_date]" size="10" maxlength="10" />' +
        '</div>' +
        '<div>' +
        '<span>Time: </span>' +
        '<input type="text" name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][observation_time]" size="5" maxlength="5" />' +
        '<select name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][observation_time_sign]">' +
        '<option value="am">am</option>' +
        '<option value="pm">pm</option>' +
        '</select>' +
        '</div>' +
        '<div>' +
        '<span>Place: </span>' +
        '<div><input type="radio" name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][observation_place]" value="indoor" checked="checked"> Indoor</div>' +
        '<div><input type="radio" name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][observation_place]" value="outdoor"> Outdoor</div>' +
        '</div>' +
        '<div>' +
        '<span>Text: </span>' +
        '<textarea name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][observation_text]" rows="10" cols="100"></textarea>' +
        '</div>' +
        '</p>' +
        '<p>' +
        '<div>' +
        '<span>Interpretation: </span>' +
        '<textarea name="entries[%entry%][observations][%observation%][goals][%goal%][exp][%id%][interpretation]" rows="10" cols="100"></textarea>' +
        '</div>' +
        '</p>' +
        '</div>' +
        '</fieldset>' +
        '</div>';

    // Changes all the markup within the dummy element with actual data.
    function replaceMarkupWithinElement(element, markup, id) {
        var data_att = element;
        var name_atts = element.find('[name*="' + markup + '"]');
        var legend_element = element.find('legend').first();
        data_att.attr("data-id", id);
        name_atts.each(function (index) {
            $(this).attr("name", $(this).attr("name").replace(new RegExp(markup, 'g'), id));
        });
        legend_element.text(legend_element.text().replace(new RegExp(markup, 'g'), id));
    }

    // Clones a new dummy element, and turns the old dummy element into a real
    // element, then shows it. It returns the id of the new element.
    function generateNewElement(currentElement, targetElement, classString) {

        /* Clone the dummy element and add it right after the current dummy element,
         then make the current element non-dummy, as it has just been added to the page.
         This way we ensure there is always one and only one dummy element that is the
         last element in the list in the DOM. */
        var id = parseInt(targetElement.find(classString + ':nth-last-of-type(2)').attr('data-id')) + 1;
        if (isNaN(id)) {
            id = 1;
        }
        currentElement.clone().insertAfter(currentElement);
        currentElement.removeAttr("dummy");

        // Return the ID of the new element.
        return id;
    }

    // When new elements are cloned, the jQuery UI datepicker is not yet bound
    // to the datepicker elements. The dummy 'my-datepicker' class is added
    // to all the relevant elements and the datepicker is initialized on them.
    function applyDatepicker(newElement, tempDatepickerClass) {
        var myDatepickerElements = newElement.find(tempDatepickerClass);
        myDatepickerElements.addClass("my-datepicker");
        myDatepickerElements.datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        });
    }

    // Dummy form input fields are disabled so they do not populate the _POST
    // variable. When we generate a new element from a dummy, we have to
    // re-enable them.
    function removeDisabled(newElement, classString) {
        formFields = newElement.find(classString + '[disabled]')
        formFields.each(function () {
            $(this).removeAttr('disabled');
        })
    }

    $(document).on('click', '.add_t_journal_entry', function () {
        // Find the hidden dummy element
        var dummy_t_entry = t_journal_entries.find('.t-journal-entry[dummy]');

        var id = generateNewElement(dummy_t_entry, t_journal_entries, '.t-journal-entry');

        replaceMarkupWithinElement(dummy_t_entry, "%t_entry_id%", id);

        removeDisabled(dummy_t_entry, '.t-entry-input');

        applyDatepicker(dummy_t_entry, '.add-datepicker-t-entry');

        dummy_t_entry.show();
    })

    $(document).on('click', '.add_t_discovery', function () {
        // Find the hidden dummy element
        var dummy_t_discovery = t_discoveries.find('.t-discovery[dummy]');

        var id = generateNewElement(dummy_t_discovery, t_discoveries, '.t-discovery');

        replaceMarkupWithinElement(dummy_t_discovery, "%t_discovery_id%", id);

        removeDisabled(dummy_t_discovery, '.t-discovery-input');

        applyDatepicker(dummy_t_discovery, '.add-datepicker-discovery');

        dummy_t_discovery.show();
    });

    $(document).on('click', '.add_t_goal', function (event) {
        var targetElement = $(this).parents('.t-discovery');
        var dummy_t_goal = targetElement.find('.t-goal[dummy]').first();

        var id = generateNewElement(dummy_t_goal, targetElement, '.t-goal');

        replaceMarkupWithinElement(dummy_t_goal, "%t_goal_id%", id);

        removeDisabled(dummy_t_goal, '.t-goal-input');

        applyDatepicker(dummy_t_goal, '.add-datepicker-goal');

        dummy_t_goal.show();

    });

    journal.find('.add_entry').click(function () {
        var id = parseInt(journal.find('.entry:last-of-type').attr('data-id')) + 1;
        if (isNaN(id)) {
            id = 1;
        }

        $('#entries').append(entry_empty.replace(new RegExp('%id%', 'g'), id));
    });

    $(document).on('click', '.add_observation', function () {
        var entry = $(this).parents('.entry');
        var id = parseInt($(this).parents('.entry').find('.observation').last().data('id')) + 1;
        if (isNaN(id)) {
            id = 1;
        }

        entry.children('fieldset').children('.form').append(observation_empty.replace(new RegExp('%id%', 'g'), id).replace(new RegExp('%entry%', 'g'), entry.data('id')));
        $('.my-datepicker').datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        });
    });

    $(document).on('click', '.add_goal', function () {
        var observation = $(this).parents('.observation');
        var entry = observation.parents('.entry');

        var id = parseInt($(this).parents('.observation').find('.goal:last-of-type').attr('data-id')) + 1;
        if (isNaN(id)) {
            id = 1;
        }

        observation.children('fieldset').children('.form').append(goal_empty.replace(new RegExp('%id%', 'g'), id)
            .replace(new RegExp('%observation%', 'g'), observation.data('id'))
            .replace(new RegExp('%entry%', 'g'), entry.data('id')
            ));

        $('.my-datepicker').datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        });
    });

    $(document).on('click', '.add_experience', function () {
        var goal = $(this).parents('.goal');
        var observation = goal.parents('.observation');
        var entry = observation.parents('.entry');

        var id = parseInt(goal.find('.exp:last-of-type').attr('data-id')) + 1;
        if (isNaN(id)) {
            id = 1;
        }

        goal.children('fieldset').children('.form').append(exp_empty.replace(new RegExp('%id%', 'g'), id)
            .replace(new RegExp('%observation%', 'g'), observation.data('id'))
            .replace(new RegExp('%entry%', 'g'), entry.data('id'))
            .replace(new RegExp('%goal%', 'g'), goal.data('id'))
        );

        $('.my-datepicker').datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        });
    });

    $(document).on('click', '#entries .close, #t-discoveries .close, #t-journal-entries .close', function () {
        if (confirm('Do you really want to delete this item?')) {
            if ($(this).parent().parent().hasClass('entry')) {
                name = 'entries[' + $(this).parents('.entry').data('id') + '][deleted]';
            } else {
                var name = $(this).parent('fieldset').find('input').attr('name').split('][');
                name[name.length - 1] = 'deleted]';
                name = name.join('][')
            }

            $(this).parent().find('.form').html('<input type="hidden" name="' + name + '" value="deleted"><div class="deleted">Deleted</div>');
            $(this).remove();
        }
    });

    function attachment_show() {
        $('select.att_type').each(function () {
            // console.log($(this).val());
            $(this).parents('.observation').find('.attachment_type').hide();
            $(this).parents('.observation').find('.attachment_type[data-type="' + $(this).val() + '"]').show();
        });
    }

    attachment_show();

    $(document).on('change', 'select.att_type', function () {
        attachment_show();
    });

    $(document).on('change', '#individual_portfolio .image_field, #teacher_group_custom_metabox .image_field', function () {
        var input = $(this)[0];
        var image = $(this);
        // console.log(input.files);
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                image.parent().find('img').remove();
                image.parent().prepend('<img style="height:50px" src="' + e.target.result + '" />');
            }

            reader.readAsDataURL(input.files[0]);
        }
    });

    $('#add_pdf_file').click(function () {
        $(this).parent().find('.pdf_files').append('<div><input type="file" name="pdf[]"></div>');
    });

    $('.pdf_files .close').click(function () {
        if (confirm('Do you really want to delete this document?')) {
            $(this).parent().remove();
        }
    });

    $(document).on('change', '#select-child', function () {
        // console.log("Do we get here at all??");
        populate_experience_dates();
        populate_focus_group();
    });

    $(document).on('change', '#select-experience', function () {
        populate_experience_text();
    });

    function only_mondays(date) {
        var day = date.getDay();

        if (day == 1) {
            return [true];
        } else {
            return [false];
        }
    }

    $("#week-starting-date").datepicker("destroy");
    $("#week-starting-date").datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
        // beforeShowDay: only_mondays
    });

    $("#select-child").click(function () {
        $("#target").select();
    });

    var current_child_data = null;

    function populate_focus_group() {

        if (!$('#select-child').length && !$('#student_id').length) {
            return;
        }

        if ($('#select-child').length) {
            var c_select_child = $('#select-child');
            var c_focus_group = $('#focus-group');
        } else {
            var c_select_child = $('#student_id');
            var c_focus_group = $('#portfolio-group');
        }

        var selected_child_id = c_select_child.val();
        if (selected_child_id) {
            // Show 'loading...' text
            c_focus_group.val('loading...');
        } else {
            c_focus_group.empty();
        }

        $.ajax({
            url: plugin_root + "getGroup.php",
            type: "get",
            data: {
                childID: selected_child_id
            },
            success: function (response) {

                // console.log(response);
                // console.log(decoded_response);
                // console.log(selected_child_id);
                // console.log(null);

                if (response === "" && selected_child_id !== "") {

                    // If child has been selected but no data returned
                    c_focus_group.val('No group assigned...');

                } else {

                    // If child has been selected and data returned
                    c_focus_group.val(response);
                }
            },
            error: function (xhr) {
                //Do Something to handle error
                c_focus_group.val('Something went wrong...');
            }
        });
    }

    function populate_experience_dates(is_initial) {

        is_initial = is_initial || false;

        if (!$('#select-experience').length) {
            return;
        }

        var c_select_child = $('#select-child');
        var c_select_experience = $('#select-experience').first();
        if (c_select_child !== null) {
            var selected_child_id = c_select_child.val();
        }

        console.log("selected_child_id: " + selected_child_id);

        // Show 'loading...' text
        c_select_experience.empty();
        c_select_experience.attr("data-placeholder", 'loading...');
        c_select_experience.trigger("chosen:updated");

        $.ajax({
            url: plugin_root + "getExperiences.php",
            type: "get",
            data: {
                childID: selected_child_id
            },
            success: function (response) {

                var decoded_response = JSON.parse(response);
                c_select_experience.empty();

                // console.log(response);
                // console.log(decoded_response);
                // console.log(selected_child_id);
                // console.log(null);

                if (selected_child_id === "") {

                    // If no child has yet been selected
                    c_select_experience.attr("data-placeholder", 'Choose an experience...');

                } else if (decoded_response.length === 0 && selected_child_id !== "") {

                    // If child has been selected but no data returned
                    c_select_experience.empty();
                    c_select_experience.attr("data-placeholder", 'No experiences found...');

                } else {

                    c_select_experience.attr("data-placeholder", 'Choose an experience...');
                    c_select_experience.append("<option></option>");

                    // If child has been selected and data returned
                    for (var i = 0, len = decoded_response.length; i < len; i++) {
                        var p_date = decoded_response[i].program_date;
                        var p_entry_id = decoded_response[i].entry_id;
                        var p_obs_id = decoded_response[i].observation_id;
                        var p_goal_id = decoded_response[i].goal_id;
                        var p_exp_id = decoded_response[i].experience_id;
                        var value_text = selected_child_id + "/" + p_entry_id + "/" + p_obs_id + "/" + p_goal_id + "/" + p_exp_id;
                        var selected_attr = "";
                        console.log('c_select_experience.attr: ' + c_select_experience.attr('loaded-value'));
                        if (value_text === c_select_experience.attr('loaded-value')) {
                            selected_attr = "selected";
                        }
                        c_select_experience.append("<option value=\"" + value_text + "\" " + selected_attr + ">" + p_date + "</option>");
                        // console.log(value_text);
                    }

                }
                c_select_experience.trigger("chosen:updated");
            },
            error: function (xhr) {
                //Do Something to handle error
                c_select_experience.empty();
                c_select_experience.attr("data-placeholder", 'Something went wrong...');
                c_select_experience.trigger("chosen:updated");
            }
        });

        populate_experience_text(is_initial);
    }

    function populate_experience_text(is_initial) {

        is_initial = is_initial || false;

        var c_select_experience = $('#select-experience');
        var c_goal_text = $('#goal_text');
        var c_objectives_text = $('#objectives_text');
        var c_experience_text = $('#experience_text');
        var experience_selected = "";

        if (is_initial && c_select_experience.attr('loaded-value') != "") {
            experience_selected = c_select_experience.attr('loaded-value');
        } else {
            experience_selected = c_select_experience.val();
        }

        console.log('experience_selected: ' + experience_selected);
        if (experience_selected === "" || experience_selected === null) {
            // If no experience has yet been selected
            var c_text = 'Data will be pulled in when experience is selected...';
            c_goal_text.text(c_text);
            c_objectives_text.text(c_text);
            c_experience_text.text(c_text);
            return;
        }

        var value_array = experience_selected.split("/");
        var selected_child_id = value_array[0];
        var selected_entry_id = value_array[1];
        var selected_observation_id = value_array[2];
        var selected_goal_id = value_array[3];
        var selected_experience_id = value_array[4];

        // Show 'loading...' text
        c_goal_text.text('loading goal...');
        c_objectives_text.text('loading objectives...');
        c_experience_text.text('loading experiences...');

        // Get data based on selected child and experience

        $.ajax({
            url: plugin_root + "getExperiences.php",
            type: "get",
            data: {
                childID: selected_child_id,
                entryID: selected_entry_id,
                observationID: selected_observation_id,
                goalID: selected_goal_id,
                experienceID: selected_experience_id
            },
            success: function (response) {

                var decoded_response = JSON.parse(response);

                if (decoded_response[0].error_code === 0) {

                    if (decoded_response.length !== 1) {

                        // If experience has been selected and no errors occurred but data
                        // array is the wrong length, something went wrong

                        var c_text = 'Data invalid...';
                        c_goal_text.text(c_text);
                        c_objectives_text.text(c_text);
                        c_experience_text.text(c_text);
                    }
                    else {

                        // Success!
                        // If experience has been selected and no errors occurred

                        c_goal_text.text(decoded_response[0].goal_text);
                        c_objectives_text.text(decoded_response[0].objective);
                        c_experience_text.text(decoded_response[0].program_text);

                    }

                } else if (decoded_response[0].error_code === 1) {

                    // If experience has been selected but data is missing (deleted?)

                    var c_text = 'Experience no longer exists in individual portfolio...';
                    c_goal_text.text(c_text);
                    c_objectives_text.text(c_text);
                    c_experience_text.text(c_text);

                } else {
                    // Some other error occurred
                    var c_text = 'Some error has occurred...';
                    c_goal_text.text(c_text);
                    c_objectives_text.text(c_text);
                    c_experience_text.text(c_text);
                }
            },
            error: function (xhr) {
                //Do Something to handle error
                c_select_experience.empty();
                c_select_experience.attr("data-placeholder", 'Something went wrong...');
                c_select_experience.trigger("chosen:updated");
            }
        });
    }
});