/**
 * Handles AJAX operations specific to companyEdit and companyPage.
 */

var SUBDIRECTORY = "/cs3744/project4/fantasticfour";

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
    "people": {},
    "events": {}
};

//-------------------------

/**
 * Returns the full name of the person
 */
function fullname(person) {
    return person.firstname + " " + person.lastname;
}

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
    return "<img id='image-event-" + event.id + "' alt='" + event.locationName + "'>"
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
    var html_str = "<tr style='display: none'><td>" + img_tag(event) + "</td><td>" +
        event.eventName +  "</td><td>" + event.date + "</td><td>" + event.description + "</td><td>" + location_str(event) + "</td></tr>";

    if(edit) {
        html_str = "<tr style='display: none'><td>" + img_tag(event) + "</td><td>" + event.eventName +  "</td><td>" + event.date + "</td><td>" + event.description +
            "</td><td>" + location_str(event) + "</td><td><button onclick='remove_event(" + event.id + ", " + event.unitID + ")'>X</button></td></tr>"
    }

    // create new DOM element
    var node = $(html_str);

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
 * Adds a person to the page
 * @param person - data object to add
 * @param edit - true if in edit mode
 */
function add_person(person, edit) {
    var html_str = "<tr style='display: none'><td>" + person.rank + "</td><td>" + fullname(person) + "</td></tr>";

    if(edit) {
        html_str = "<tr style='display: none'><td>" + person.rank  + "</td><td>" + fullname(person) + "</td><td><button onclick='remove_person(" + person.id + ", " + person.unitID + ")'>X</button></td></tr>"
    }

    // create new DOM element
    var node = $(html_str);

    // add to global register
    data_state.people[person.id] = {
        "node": node,
        "value": person
    };

    // fade in
    $('#members-tbody').append(node);
    node.show("fast");
}

/**
 * AJAX
 * Removes the person with the given ID from the server database
 * @param personId - id of the person to remove.
 * @param unitId - unit id of the person to remove
 */
function remove_person(personId, unitId) {
    function failure(reason) {
        console.log(reason)
    }

    var _this = this; // capture external context
    if(!confirm("Really delete this person?")) return; // show confirm dialog, abort if no

    // send ajax request
    $.ajax({
        'url': url('/companies/' + unitId + '/personDelete/' + personId),
        'type': 'POST',
        'success': function (result) {
            if ('result' in result && result.result === 'success') {
                // iff successful, remove from local record and DOM
                var person = data_state.people[personId];
                person.node.fadeOut('normal', function () {
                    $(_this).remove();
                    delete data_state.people[personId];
                });
            } else {
                // else, log result
                failure(result);
            }
        },
        'error': failure
    });
}

/**
 * AJAX
 * Removes the event with given ID from the server database
 * @param eventId - id of event to remove
 * @param unitId - unit id of event to remove
 */
function remove_event(eventId, unitId) {
    function failure(reason) {
        console.log(reason)
    }

    var _this = this; // capture external context
    if(!confirm("Really delete this event?")) return; // show confirm dialog, abort if no

    // send ajax request
    $.ajax({
        'url': url('/companies/' + unitId + '/eventDelete/' + eventId),
        'type': 'POST',
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
 * @param company_id - id of company for this page (set by PHP)
 * @param edit - true if in edit mode (set by PHP)
 */
function init_ajax(company_id, edit) {
    function failure(reason) {
        console.log(reason)
    }

    // fetch people and add them
    $.getJSON(url('/companies/' + company_id + '/people'), function (results) {
        results.forEach(function (person) {
            return add_person(person, edit)
        });
    });

    // fetch events and add them
    $.getJSON(url('/companies/' + company_id + '/events'), function (results) {
        results.forEach(function (event) {
            return add_event(event, edit)
        });
    });

    // Configure add person form
    var memberAdd = $('#memberAdd');
    memberAdd.on("submit", function (event) {
        // called when add button is clicked
        event.preventDefault(); // prevent page redirect
        data = jsonifyArray(memberAdd.serializeArray()); // fix form data
        memberAdd.find(":input").val(""); // reset form
        memberAdd.find("input[type=submit]").val("Add"); // because jquery keeps clearing out the button as well
        console.log(data);

        // send AJAX request
        $.post(url('/companies/' + company_id + '/personAdd'), JSON.stringify(data), function (result) {
            console.log(result);
            // add person iff successful, otherwise log result
            if ('result' in result && result.result === 'success') {
                return add_person(result.value, edit);
            } else {
                failure(result);
            }
        })
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

        // send AJAX request
        $.post(url('/companies/' + company_id + '/eventAdd'), JSON.stringify(data), function (result) {
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