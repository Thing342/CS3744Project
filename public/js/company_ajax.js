/**
 * Handles AJAX operations specific to companyEdit and companyPage.
 */

var SUBDIRECTORY = "";

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

function isEmpty(str) {
    return (!str || 0 === str.length);
}

//-----------------------

/**
 * List to keep tract of notes and people data, as well as their corresponding nodes.
 */
var data_state = {
    "people": {},
    "notes": {}
};

//-------------------------

/**
 * Returns the full name of the person
 */
function fullname(person) {
    return person.firstname + " " + person.lastname;
}

/**
 * Shortcut function to generate an IMG tag for note table
 * @param note note to generate tag for
 * @param edit
 * @returns {string} html string
 */
function img_tag(note, edit) {
    if (edit) {
        if(isEmpty(note.imageURL)) {
            return "<p>No image.</p>"
        } else {
            return "<img id='image-note-" + note.id + "' alt='" + note.title + "' src='" + note.imageURL + "' class='mr-3 image-thumbnail' width='200px'>"
        }
    } else {
        if(isEmpty(note.imageURL)) { return "" }
        else {
            return "<div class='text-center'><img class='image-fluid mw-75 mb-4' src='" + note.imageURL + "' alt='" + note.title + "'></div>"
        }
    }
}

function note_html(note, edit) {
    if (!edit) return "<li class=\"media my-2\">" +
                "<div class=\"media-body\">" +
                     img_tag(note, edit) +
                    "<h3 class=\"mt-0\">" + note.eventName + "</h3>" +
                    "<p>" + note.description + "</p>" +
                "</div>" +
            "</li>";
    else return "<tr style='display: none'><td>" + img_tag(note, edit) + "</td><td>" + note.eventName +  "</td><td>" + note.description +
        "</td><td><button class='btn btn-secondary' onclick='remove_note(" + note.id + ", " + note.unitID + ")'>X</button></td></tr>"
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
 * Adds an note to the page
 * @param note - data object to add
 * @param edit - true if in edit mode
 */
function add_note(note, edit) {
    var html_str = note_html(note, edit);

    // create new DOM element
    var node = $(html_str);

    // add to global register
    data_state.notes[note.id] = {
        "node": node,
        "value": note
    };

    // fade in
    $('#notes-tbody').append(node);
    node.show("fast");
}

/**
 * Adds a person to the page
 * @param person - data object to add
 * @param edit - true if in edit mode
 */
function add_person(person, edit) {
    var html_str = "<tr style='display: none'><td scope='col'><b>" + person.rank + "</b></td><td>" + fullname(person) + "</td></tr>";

    if(edit) {
        html_str = "<tr style='display: none'><td scope='col'>" + person.rank  + "</td><td>" + fullname(person) + "</td><td><button onclick='remove_person(" + person.id + ", " + person.unitID + ")'>X</button></td></tr>"
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
 * Removes the note with given ID from the server database
 * @param noteId - id of note to remove
 * @param unitId - unit id of note to remove
 */
function remove_note(noteId, unitId) {
    function failure(reason) {
        console.log(reason)
    }

    var _this = this; // capture external context
    if(!confirm("Really delete this note?")) return; // show confirm dialog, abort if no

    // send ajax request
    $.ajax({
        'url': url('/companies/' + unitId + '/noteDelete/' + noteId),
        'type': 'POST',
        'success': function (result) {
            if ('result' in result && result.result === 'success') {
                // iff successful, remove from local record and DOM
                var note = data_state.notes[noteId];
                note.node.fadeOut('normal', function () {
                    $(_this).remove();
                    delete data_state.notes[noteId];
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
 * @param subdir - subdirectory of site
 * @param company_id - id of company for this page (set by PHP)
 * @param edit - true if in edit mode (set by PHP)
 */
function init_ajax(subdir, company_id, edit) {
    function failure(reason) {
        console.log(reason)
    }

    SUBDIRECTORY = subdir

    // fetch people and add them
    $.getJSON(url('/companies/' + company_id + '/people'), function (results) {
        results.forEach(function (person) {
            return add_person(person, edit)
        });
    });

    // fetch notes and add them
    $.getJSON(url('/companies/' + company_id + '/notes'), function (results) {
        results.forEach(function (note) {
            console.log(note);
            return add_note(note, edit)
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
    var noteAdd = $('#noteAdd');
    noteAdd.on("submit", function (e) {
        // called when add button is clicked
        e.preventDefault(); // prevent page redirect
        data = jsonifyArray(noteAdd.serializeArray()); // fix form data
        noteAdd.find(":input").val(""); // reset form
        noteAdd.find("input[type=submit]").val("Add"); // because jquery keeps clearing out the button as well
        console.log(data);

        // send AJAX request
        $.post(url('/companies/' + company_id + '/noteAdd'), JSON.stringify(data), function (result) {
            console.log(result);
            // add note iff successful, otherwise log result
            if ('result' in result && result.result === 'success') {
                return add_note(result.value, edit);
            } else {
                failure(result);
            }
        })
    });

    $('.platoon-item').on('click', function (e) {
        var id = $(this).data('unitid');
        window.document.location.href = url('/companies/' + id);
    })
}