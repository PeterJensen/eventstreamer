// ----------------------------------
// Author: Peter Jensen
// ----------------------------------

var server = function () {

  // configuration data

  var config = {
    serverUrl: "../server/eventstreamer.php"
  };

  // state data

  var state = {
    eventName:     null,
    eventLocation: null,
    userName:      null,
    errorCallback: null
  };

  // private functions

  function errorLog(msg) {
    console.log ("server.js ERROR: " + msg);
  }
  state.errorCallback = errorLog;

  function checkEventState() {
    if (state.eventName === null) {
      state.errorCallback("No event name has been established.  Call server.setEvent() first");
      return false;
    }
  }

  function checkUserState() {
    if (state.userName === null) {
      state.errorCallback("No user name has been established.  Call server.setUser() first");
      return false;
    }
  }

  function makeEventUserUrl(action, extras) {
    var url = config.serverUrl + 
                "?action=" + action +
                "&eventName=" + state.eventName +
                "&userName=" + state.userName;
    for (key in extras) {
      url += "&" + key + "=" + extras[key];
    }
    return url;
  }

  function makeBaseUrl(action, extras) {
    var url = config.serverUrl + 
                "?action=" + action +
    for (key in extras) {
      url += "&" + key + "=" + extras[key];
    }
    return url;
  }

  // exported functions

  function setEvent(eventName, eventLocation) {
    state.eventName     = eventName;
    state.eventLocation = eventLocation;
  }

  function setUser(userName) {
    state.userName = userName;
  }

  function setErrorCallback(error) {
    state.errorCallback = error;
  }
  
  function uploadBlob(fileName, blob, callbacks) {
    if (!checkEventState() || !checkUserState()) {
      return;
    }
    $.ajax({
      url: makeEventUserUrl("uploadBlob", {fileName: fileName}),
      type: "POST",
      data: blob,
      success: callbacks.success,
      processData: false,
      contentType: "image/jpeg",
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
  
  function uploadBase64(fileName, base64, callbacks) {
    if (!checkEventState() || !checkUserState()) {
      return;
    }
    $.ajax({
      url: makeEventUserUrl("uploadBase64", {fileName: fileName}),
      type: "POST",
      data: {filename: fileName, filedata: base64},
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

  function getEventsCloseBy(location, callbacks) {
    $.ajax({
      url: makeBaseUrl("getEventsCloseBy", {location: location}),
      type: "GET",
      success: callbacks.success,
      error:   callbacks.error
    });
  }

  function getAllEvents(callbacks) {
    $.ajax({
      url: makeBaseUrl("getAllEvent", {});
      type: "GET",
      success: callbacks.success,
      error:   callbacks.error
    });
  }

  function createEvent(event, callbacks) {
    if (!checkuserState()) {
      return;
    }
//
//    $.ajax({
//      url: makeBaseUrl("createEvent", {userName: state.userName, event: event}
//  }
  }

  return {
    setEvent:         setEvent,
    setUser:          setUser,
    setErrorCallback: setErrorCallback,
    getEventsCloseBy: getEventsCloseBy,
    getAllEvents:     getAllEvents,
    createEvent:      createEvent,
    uploadBlob:       uploadBlob,
    uploadBase64:     uploadBase64
  }
  
}();
