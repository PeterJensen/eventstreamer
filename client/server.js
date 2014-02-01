// ----------------------------------
// Author: Peter Jensen
// ----------------------------------

var server = function () {

  // configuration data

  var config = {
    serverUrl: "../server/eventstreamer.php"
  };

  // interface types
  
  function Position(lat, lon) {
    this.lat = lat;
    this.lon = lon;
  }

  function Event(name, description, position) {
    this.name        = name;     // String
    this.position    = position; // a Position object
    this.description = description;
    this.timestamp   = Date.now();
    this.createdBy   = state.userName;
    this.id          = null; // will be filled in by the server
    this.dir         = null; // will be filled in by the server
  }
  
  // state data

  var state = {
    event:         null,
    userName:      null,
    errorCallback: null
  };

  // enumeration of all server actions

  var actions = {
    uploadBase64:     "uploadBase64",
    getEventsCloseBy: "getEventsCloseBy",
    getAllEvents:     "getAllEvents",
    createEvent:      "createEvent"
  };

  // private functions

  function errorLog(msg) {
    console.log ("ERROR(server.js): " + msg);
  }
  setErrorCallback(errorLog);

  function checkEventState() {
    if (state.eventName === null) {
      state.errorCallback("No event name has been established.  Call server.setEvent() first");
      return false;
    }
    return true;
  }

  function checkUserState() {
    if (state.userName === null) {
      state.errorCallback("No user name has been established.  Call server.setUser() first");
      return false;
    }
    return true;
  }

  function makeExtras(extras) {
    var url = "";
    if (typeof extras !== "undefined") {
      for (key in extras) {
        url += "&" + key + "=" + extras[key];
      }
    }
    return url;
  }
    
  function makeBaseUrl(action, extras) {
    return config.serverUrl + 
             "?action=" + action +
              makeExtras(extras);
  }

  function makeUserUrl(action, extras) {
    return config.serverUrl + 
             "?action=" + action +
             "&userName=" + state.userName +
             makeExtras(extras);
  }
  
  function makeEventUserUrl(action, extras) {
    return config.serverUrl + 
             "?action=" + action +
             "&eventName=" + state.eventName +
             "&userName=" + state.userName +
             makeExtras(extras);
  }

  // exported functions

  function setEvent(event) {
    state.event = event;
  }

  function setUser(userName) {
    state.userName = userName;
  }

  function setErrorCallback(error) {
    state.errorCallback = error;
  }
  
  function uploadBase64(fileName, base64, callbacks) {
    if (!checkEventState() || !checkUserState()) {
      return;
    }
    $.ajax({
      url: makeEventUserUrl(actions.uploadBase64),
      type: "POST",
      data: {payload: JSON.stringify({filename: fileName, filedata: base64})},
      success: callbacks.success,
      error: callbacks.error,
      xhr: function() {
        myXhr = $.ajaxSettings.xhr();
        if(myXhr.upload){
          myXhr.upload.addEventListener('progress', callbacks.progress, false);
        } else {
          state.errorCallback("Upload progress is not supported.");
        }
        return myXhr;
      }
    });
  }

  function getEventsCloseBy(position, callbacks) {
    $.ajax({
      url: makeBaseUrl(actions.getEventsCloseBy),
      type: "POST",
      data: {payload: JSON.stringify(position)},
      success: callbacks.success,
      error:   callbacks.error
    });
  }

  function getAllEvents(callbacks) {
    $.ajax({
      url:     makeBaseUrl(actions.getAllEvents),
      type:    "GET",
      success: callbacks.success,
      error:   callbacks.error
    });
  }

  function createEvent(event, callbacks) {
    if (!checkUserState()) {
      return;
    }
    $.ajax({
      url:     makeUserUrl(actions.createEvent),
      type:    "POST",
      data:    {payload: JSON.stringify(event)},
      dataType: "json",
      success: callbacks.success,
      error:   callbacks.error
    });
  }

  return {
    Position:         Position,
    Event:            Event,
    setEvent:         setEvent,
    setUser:          setUser,
    setErrorCallback: setErrorCallback,
    getEventsCloseBy: getEventsCloseBy,
    getAllEvents:     getAllEvents,
    createEvent:      createEvent,
    uploadBase64:     uploadBase64
  }
  
}();
