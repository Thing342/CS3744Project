/**
 * Handles AJAX operations specific to companyEdit and companyPage.
 */

var SUBDIRECTORY = "";
var LEVEL = 0;

/**
 * Wraps URL to make it work when site is hosted in subdirectory
 * @param internal - site-internal url, e.g. '/companies/5'
 * @returns {string} - qualified relative url
 */
function url(internal) {
    console.log(SUBDIRECTORY + internal);
    return SUBDIRECTORY + internal;
}

/**
 * Turns an array of key-value pairs into a JS dict
 */
function jsonifyArray(array) {
    dict = {};
    for(i=0;i<array.length;i++){
        record=array[i];
        dict[record.name] = record.value;
    }

    return dict;
}

//-----------------------

/**
 * List to keep tract of events and people data, as well as their corresponding nodes.
 */
var data_state = {
    "events": {}
};

//-------------------------

/**
 * Returns a pretty version of the location info
 */
function location_str(event) {
    return event.locationName + " (" + event.latitude + ", " + event.longitude + ")";
}

/**
 * Shortcut function to generate an IMG tag for event table
 * @param event event to generate tag for
 * @returns {string} html string
 */
function img_tag(event) {
    return "<img id='image-event-" + event.id + "' alt='" + event.locationName + "' class='mr-3 image-thumbnail' width='200px'>"
}

function event_html(event, edit) {

    var edit_panel = "";
    if (LEVEL >= 2) {
        edit_panel = "<div class=\"float-sm-right\">\n" +
            "     <div class=\"dropdown show\">\n" +
            "         <button class=\"btn btn-dark dropdown-toggle\" role=\"button\"\n" +
            "            id=\"dropdownMenuLink\" data-toggle=\"dropdown\" aria-haspopup=\"true\"\n" +
            "            aria-expanded=\"false\">\n" +
            "             Actions\n" +
            "         </button>\n" +
            "         <div class=\"dropdown-menu\" aria-labelledby=\"dropdownMenuLink\">\n" +
            "             <button class=\"dropdown-item event-edit-button\" data-target='" + event.id + "'>Edit Event</button>\n" +
            "             <button class=\"dropdown-item event-delete-button\" data-target='" + event.id + "'>Delete Event</button>" +
            "         </div>\n" +
            "     </div>\n" +
            " </div>"
    }

    if (!edit) return "<li class=\"media my-4\">" +
        img_tag(event) +
            "<div class=\"media-body\">" +
                "<h5 class=\"mt-0\">" + event.eventName + "</h5>" +
                "<p>" + event.date +"</p>" +
                "<p>" + event.description + "</p>" +
                "<p><i>" + location_str(event) +"</i></p>" +
            "</div>" +
            edit_panel +
        "</li>";
    else return "<tr style='display: none'><td>" + img_tag(event) + "</td><td>" + event.eventName +  "</td><td>" + event.date + "</td><td>" + event.description +
        "</td><td>" + location_str(event) + "</td><td><button onclick='remove_event(" + event.id + ", " + event.unitID + ")'>X</button></td></tr>"
}

//-------------------------

/**
 * Asynchronously queries the Wikipedia API to find images with the given query string, and puts them into an existing tag.
 * @param queryString - string to query Wikipedia API with
 * @param selector - jQuery selector of the img tag you want to put the returned image into.
 */
function getWikipediaImageAJAX(queryString, selector) {
    $.ajax({
        url: "https://en.wikipedia.org/w/api.php?action=query&titles=" + queryString + "&prop=pageimages&format=json&pithumbsize=200",
        dataType: 'jsonp',
        success: function(response) {
            var pageId = Object.keys(response['query']['pages']);
            var picObj = response['query']['pages'][pageId];

            var picUrl = url('/public/img/null');
            if('thumbnail' in picObj) {
                console.log(picObj);
                picUrl = picObj.thumbnail.source;
            }

            //var pic = $('<img id="titleThumb" src="' + picUrl + '" alt="">');
            $(selector).attr("src", picUrl);
        },
        error: function(err) {
            alert("ERROR");
        }
    });
}

//--------------------------

/**
 * Adds an event to the page
 * @param event - data object to add
 * @param edit - true if in edit mode
 */
function add_event(event, edit) {
    var html_str = event_html(event, edit);

    // create new DOM element
    var node = $(html_str);
    node.find("button.event-delete-button").on('click', function (e) {
        var target = $(this).data('target');
        console.log("DELETE button pushed for ID=" + target);
        remove_event(target);
    });
    node.find("button.event-edit-button").on('click', function (e) {
        var target = $(this).data('target');
        console.log("EDIT button pushed for ID=" + target);
        show_edit_form(event);
    });

    // add to global register
    data_state.events[event.id] = {
        "node": node,
        "value": event
    };

    // fade in
    $('#events-tbody').append(node);
    node.show("fast");

    // load wiki image
    getWikipediaImageAJAX(event.locationName, '#image-event-' + event.id);
}

/**
 * Shows the 'edit event' box
 * @param event - event to edit
 *
 * TODO: always show events in chronological order
 */
function show_edit_form(event) {
    function failure(reason) {
        alert("Editing error. Please see console.");
        console.log(reason)
    }

    var editModal = $('#editModal');

    editModal.find("#editName").val(event.eventName);
    editModal.find("#editDate").val(event.date);
    editModal.find("#editLocationName").val(event.locationName);
    editModal.find("#editLatitude").val(event.latitude);
    editModal.find("#editLongitude").val(event.longitude);
    editModal.find("#editDescription").val(event.description);

    var form = editModal.find("#eventEdit");
    form.unbind(); // make sure we've unbound all our previous listeners

    form.on('submit', function (e) {
        e.preventDefault(); // prevent page redirect
        data = jsonifyArray($(this).serializeArray()); // fix form data
        editModal.modal('hide');
        console.log(data);

        // send AJAX request
        $.ajax({
            'url': url('/timeline/' + event.id),
            'type': 'PATCH',
            'data': JSON.stringify(data),
            'success': function (result) {
                if ('result' in result && result.result === 'success') {
                    console.log(result);
                    var entry = result.value;
                    var event = data_state.events[entry.id];
                    event.node.remove();
                    delete data_state.events[entry.id];

                    add_event(entry, false);
                } else {
                    failure(result);
                }
            },
            'error': failure
        });
    });

    editModal.modal();
}

/**
 * AJAX
 * Removes the event with given ID from the server database
 * @param eventId - id of event to remove
 * @param unitId - unit id of event to remove
 */
function remove_event(eventId) {
    function failure(reason) {
        console.log(reason)
    }

    var _this = this; // capture external context
    if(!confirm("Really delete this event?")) return; // show confirm dialog, abort if no

    // send ajax request
    $.ajax({
        'url': url('/timeline' + eventId),
        'type': 'DELETE',
        'success': function (result) {
            if ('result' in result && result.result === 'success') {
                // iff successful, remove from local record and DOM
                var event = data_state.events[eventId];
                event.node.fadeOut('normal', function () {
                    $(_this).remove();
                    delete data_state.events[eventId];
                });
            } else {
                // else, log result
                failure(result);
            }
        },
        'error': failure
    });
}


//--------------------


/**
 * Does inital fetch of data, and sets up callbacks for form data
 * this is called by body onLoad.
 * @param subdir Site subdirectory
 * @param edit - true if in edit mode (set by PHP)
 * @param user_level - int descibing the user's permissions
 */
function init_ajax(subdir, edit, user_level) {
    function failure(reason) {
        console.log(reason)
    }

    SUBDIRECTORY = subdir;
    LEVEL = user_level;

    // fetch events and add them
    $.getJSON(url('/timeline'), function (results) {
        results.forEach(function (event) {
            return add_event(event, edit)
        });
    });

    var addModal = $('#addModal');
    var eventAddBtn = $('#eventAddBtn');
    eventAddBtn.on('click', function (e) {
        console.log("button pressed!");
        addModal.modal();
    });

    // Configure add event form
    var eventAdd = $('#eventAdd');
    eventAdd.on("submit", function (e) {
        // called when add button is clicked
        e.preventDefault(); // prevent page redirect
        data = jsonifyArray(eventAdd.serializeArray()); // fix form data
        eventAdd.find(":input").val(""); // reset form
        eventAdd.find("input[type=submit]").val("Add"); // because jquery keeps clearing out the button as well
        console.log(data);

        addModal.modal('hide'); // Close add box

        // send AJAX request
        $.post(url('/timeline'), JSON.stringify(data), function (result) {
            console.log(result);
            // add event iff successful, otherwise log result
            if ('result' in result && result.result === 'success') {
                return add_event(result.value, edit);
            } else {
                failure(result);
            }
        })
    });


}