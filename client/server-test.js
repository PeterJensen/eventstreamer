// ---------------------------------------------------------------------------
// Author: Peter Jensen
// ---------------------------------------------------------------------------

function error(msg) {
  alert(msg);
}

function log(msg) {
  console.log(msg);
}

function defaultSuccess(response) {
  log(response);
}

function defaultError(response) {
  log(response);
}

function defaultProgress(response) {
  log(response);
}

function setUserClick() {

  function setUserSuccess(response) {
    log(response);
    var id = response.payload.id;
    $("#userId").text(id);
  }
  
  var userName = $("#userName").val();
  if (userName === "") {
    error("user name cannot be blank");
  }
  else {
    var user = new server.User(userName);
    server.setUser(user, {success: setUserSuccess, error: defaultError});
  }
}

function setEventClick() {

  function setEventSuccess(response) {
    log(response);
    var id;
    if (response.payload.exists) {
      id = response.payload.event.id;
    }
    else {
      id = "## EVENT DOESN'T EXIST";
    }
    $("#eventId").text(id);
  }
    
  var eventName = $("#selectEventName").val();
  if (eventName === "") {
    error("event name cannot be blank");
  }
  else {
    var event = new server.SetEvent(eventName);
    server.setEvent(event, {success: setEventSuccess, error: defaultError});
  }
    
}

function createEventClick() {
  var lat       = parseFloat($("#eventPositionLat").val());
  var lon       = parseFloat($("#eventPositionLon").val());
  var pos       = new server.Position(lat, lon);
  var event     = new server.Event($("#eventName").val(), $("#eventDescription").val(), pos, $("#eventImage").attr("src"));
  var callbacks = {success: defaultSuccess, error: defaultError, progress: defaultProgress};
  server.createEvent(event, callbacks);
}

function selectEventImageClick() {
  var inputFiles = $("#selectEventImage")[0];
  var file = inputFiles.files[0];
  log ("File selected: " + file.name + ", size: " + file.size);
  var reader = new FileReader();
  reader.onload = function(event) {
    $("#eventImage").attr("src", event.target.result);
  };
  reader.readAsDataURL (file);
}

function getAllEventsClick() {

  function getAllEventsSuccess(response) {
    $("#eventList").empty();
    var columns = ["thumb", "Event Name", "Event Description", "Created By", "Created Time"];
    var $table = $("<table>").attr("border", 1);
    var $tr   = $("<tr>");
    for (var c in columns) {
      $tr.append($("<td>").text(columns[c]));
    }
    $table.append($tr);
    var events = response.payload.events;
    for (var i in events) {
      var event = events[i];
      var $tr = $("<tr>");
      var imgFile = event.imageThumbnail;
      if (imgFile !== null) {
        $tr.append($("<td>").append($("<img>").attr("src", server.getFileUrl(imgFile))));
      }
      else {
        $tr.append($("<td>").text("No Image"));
      }      
      $tr.append($("<td>").text(event.name));
      $tr.append($("<td>").text(event.description));
      $tr.append($("<td>").text(event.createdBy));
      $tr.append($("<td>").text((new Date(event.timestamp*1000)).toISOString()));
      $table.append($tr);
    }
    $("#eventList").append($table);
    
    var pre = $("<pre>").text(JSON.stringify(response, false, 2));
    $("#eventList").append(pre);
  }
  
  function getAllEventsError(response) {
    log(response);
  }

  var callbacks = {success: getAllEventsSuccess, error: getAllEventsError};
  server.getAllEvents(callbacks);
}

function selectUserImageClick() {
  var inputFiles = $("#selectUserImage")[0];
  var file = inputFiles.files[0];
  log ("File selected: " + file.name + ", size: " + file.size);
  var reader = new FileReader();
  reader.onload = function(event) {
    $("#userImage").attr("src", event.target.result);
  };
  reader.readAsDataURL (file);
}

function addUserImageClick() {
  var lat       = parseFloat($("#userPositionLat").val());
  var lon       = parseFloat($("#userPositionLon").val());
  var pos       = new server.Position(lat, lon);
  var caption   = $("#userCaption").val();
  var userImage = new server.UploadImage($("#userImage").attr("src"), pos, caption);
  var callbacks = {success: defaultSuccess, error: defaultError, progress: defaultProgress};
  server.uploadImage(userImage, callbacks);
}

function getAllEventImagesClick() {

  function getAllEventImagesSuccess(response) {
    $("#eventImagesList").empty();
    var pre = $("<pre>").text(JSON.stringify(response, false, 2));
    $("#eventImagesList").append(pre);
  }
  
  server.getAllEventImages({success: getAllEventImagesSuccess, error: defaultError});
}

$(function() {
  setUserClick();
  $("#setUser").click(setUserClick);
  $("#setEvent").click(setEventClick);
  $("#selectEventImage").on("change", selectEventImageClick);
  $("#createEvent").click(createEventClick);
  $("#getAllEvents").click(getAllEventsClick);
  $("#selectUserImage").on("change", selectUserImageClick);
  $("#addUserImage").click(addUserImageClick);
  $("#getAllEventImages").click(getAllEventImagesClick);
});
