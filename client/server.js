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

  function Event(name, description, position, base64Image) {
    this.name        = name;     // String
    this.description = description;
    this.position    = position; // a Position object
    this.base64Image = base64Image;
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

  function makeProgressXhr(callback) {
    return function () {
      var myXhr = $.ajaxSettings.xhr();
      if (myXhr.upload) {
        myXhr.upload.addEventListener('progress', callback, false);
      }
      else {
        state.errorCallback("Upload progress is not supported.");
      }
      return myXhr;
    }
  }

  function makeAjaxPostRequest(url, payload, callbacks) {
    if (typeof callbacks.progress === "undefined") {
      $.ajax({
        url:       url,
        type:      "POST",
        data:      {payload: JSON.stringify(payload)},
        dataType:  "json",
        success:   callbacks.success,
        error:     callbacks.error
      });
    }
    else {
      $.ajax({
        url:       url,
        type:      "POST",
        data:      {payload: JSON.stringify(payload)},
        dataType:  "json",
        success:   callbacks.success,
        error:     callbacks.error,
        xhr:       makeProgressXhr (callbacks.progress)
      });
    }
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
    makeAjaxPostRequest(
      makeEventUserUrl(actions.uploadBase64),
      {fileName: fileName, fileData: base64},
      callbacks);
  }

  function getEventsCloseBy(position, callbacks) {
    makeAjaxPostRequest(makeBaseUrl(actions.getEventsCloseBy), position, callbacks);
  }

  function getAllEvents(callbacks) {
    makeAjaxPostRequest(makeBaseUrl(actions.getAllEvents), {}, callbacks);
  }

  function createEvent(event, callbacks) {
    if (checkUserState()) {
      makeAjaxPostRequest(makeUserUrl(actions.createEvent), event, callbacks);
    }
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
