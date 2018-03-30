var SUBDIRECTORY = "/Project4/CS3744Project";

function url(internal) {
    console.log(SUBDIRECTORY + internal);
    return SUBDIRECTORY + internal;
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


function add_event(event) {
    var html_str = "<tr style='display: none'><td></td><td>" + event.eventName +  "</td><td>" + event.date + "</td><td>" + event.description +
        "</td><td>" + location_str(event) + "</td></tr>";
    var node = $(html_str);

    data_state.events[event.id] = {
        "node": node,
        "value": event
    };

    $('#events-tbody').append(node);
    node.show("fast");
}

function add_person(person) {
    var html_str = "<tr style='display: none'><td>" + person.rank + "</td><td>" + fullname(person) + "</td></tr>";
    var node = $(html_str);

    data_state.people[person.id] = {
        "node": node,
        "value": person
    };

    $('#members-tbody').append(node);
    node.show("fast");
}

function remove_person(event) {

}

function remove_event(person) {

}


function init_ajax(company_id) {
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
            return add_person(person)
        });
    });

    $.getJSON(url('/companies/' + company_id + '/events'), function (results) {
        results.forEach(function (event) {
            return add_event(event)
        });
    });
}