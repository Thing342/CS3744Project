var SUBDIRECTORY = ""; // TODO change this before commit

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

var data_state = {
    "people": {},
    "events": {}
};


function fullname(person) {
    return person.firstname + " " + person.lastname;
}

function location_str(event) {
    return event.locationName + " (" + event.latitude + ", " + event.longitude + ")";
}

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

function img_tag(event) {
    return "<img id='image-event-" + event.id + "' alt='" + event.locationName + "'>"
}

function add_event(event, edit) {
    var html_str = "<tr style='display: none'><td>" + img_tag(event) + "</td><td>" +
        event.eventName +  "</td><td>" + event.date + "</td><td>" + event.description + "</td><td>" + location_str(event) + "</td></tr>";

    if(edit) {
        html_str = "<tr style='display: none'><td>" + img_tag(event) + "</td><td>" + event.eventName +  "</td><td>" + event.date + "</td><td>" + event.description +
            "</td><td>" + location_str(event) + "</td><td><button onclick='remove_event(" + event.id + ", " + event.unitID + ")'>X</button></td></tr>"
    }
    
    var node = $(html_str);

    data_state.events[event.id] = {
        "node": node,
        "value": event
    };

    $('#events-tbody').append(node);
    node.show("fast");

    getWikipediaImageAJAX(event.locationName, '#image-event-' + event.id);
}

function add_person(person, edit) {
    var html_str = "<tr style='display: none'><td>" + person.rank + "</td><td>" + fullname(person) + "</td></tr>";

    if(edit) {
        html_str = "<tr style='display: none'><td>" + person.rank  + "</td><td>" + fullname(person) + "</td><td><button onclick='remove_person(" + person.id + ", " + person.unitID + ")'>X</button></td></tr>"
    }
    
    var node = $(html_str);

    data_state.people[person.id] = {
        "node": node,
        "value": person
    };

    $('#members-tbody').append(node);
    node.show("fast");
}

function remove_person(personId, unitId) {
    function failure(reason) {
        console.log(reason)
    }

    var _this = this;
    if(!confirm("Really delete this person?")) return;

    $.ajax({
        'url': url('/companies/' + unitId + '/personDelete/' + personId),
        'type': 'POST',
        'success': function (result) {
            if ('result' in result && result.result === 'success') {
                var person = data_state.people[personId];
                person.node.fadeOut('normal', function () {
                    $(_this).remove();
                    delete data_state.people[personId];
                });
            } else {
                failure(result);
            }
        },
        'error': failure
    });
}

function remove_event(eventId, unitId) {
    function failure(reason) {
        console.log(reason)
    }

    var _this = this;
    if(!confirm("Really delete this event?")) return;

    $.ajax({
        'url': url('/companies/' + unitId + '/eventDelete/' + eventId),
        'type': 'POST',
        'success': function (result) {
            if ('result' in result && result.result === 'success') {
                var event = data_state.events[eventId];
                event.node.fadeOut('normal', function () {
                    $(_this).remove();
                    delete data_state.events[eventId];
                });
            } else {
                failure(result);
            }
        },
        'error': failure
    });
}


function init_ajax(company_id, edit) {
    function failure(reason) {
        console.log(reason)
    }
    /*function init_person(logged_in, person_id) {
    //events.forEach((e) => addLifeEvent(e)); // seed our table with initial events
    // Fetch life events asynchronously
    $.getJSON(SUBDIR + '/people/' + person_id + '/events', function (results) {
        results.forEach(function (e) {
            return addLifeEvent(logged_in, e);
        });
    });
    // register submit callback
    var form = $("#lifeevents-add");
    form.on("submit", function (event) {
        event.preventDefault(); // Don't reload the page
        var kv = form.serializeArray(); // Comb through form data and build event object
        var ev = {
            date: kv[0].value,
            category: kv[1].value,
            location: kv[2].value,
            event: kv[3].value
        };
        form.find(":input").val("");
        form.find("input[type=submit]").val("Submit"); // because jquery keeps clearing out the button as well
        console.log(ev);
        // Commit new event to API
        $.post(SUBDIR + '/people/' + person_id + '/events', JSON.stringify(ev), function (result) {
            return addLifeEvent(logged_in, result);
        }, 'json');
    });
    // Init person-bite handlers
    if (logged_in)
        $("#relativelist").find(".person-bite").on("click", function () {
            return window.location.href = "person_loggedin.html";
        });
    else
        $("#relativelist").find(".person-bite").on("click", function () {
            return window.location.href = "person.html";
        });
}*/
    $.getJSON(url('/companies/' + company_id + '/people'), function (results) {
        results.forEach(function (person) {
            return add_person(person, edit)
        });
    });

    $.getJSON(url('/companies/' + company_id + '/events'), function (results) {
        results.forEach(function (event) {
            return add_event(event, edit)
        });
    });

    // Configure add person form
    var memberAdd = $('#memberAdd');
    memberAdd.on("submit", function (event) {
        event.preventDefault();
        data = jsonifyArray(memberAdd.serializeArray());
        memberAdd.find(":input").val("");
        memberAdd.find("input[type=submit]").val("Add"); // because jquery keeps clearing out the button as well
        console.log(data);

        $.post(url('/companies/' + company_id + '/personAdd'), JSON.stringify(data), function (result) {
            console.log(result);
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
        e.preventDefault();
        data = jsonifyArray(eventAdd.serializeArray());
        eventAdd.find(":input").val("");
        eventAdd.find("input[type=submit]").val("Add"); // because jquery keeps clearing out the button as well
        console.log(data);

        $.post(url('/companies/' + company_id + '/eventAdd'), JSON.stringify(data), function (result) {
            console.log(result);
            if ('result' in result && result.result === 'success') {
                return add_event(result.value, edit);
            } else {
                failure(result);
            }
        })
    });
}