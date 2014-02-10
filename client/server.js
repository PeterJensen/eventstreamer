// ---------------------------------------------------------------------------
// Author: Peter Jensen
// ---------------------------------------------------------------------------

var server = function () {

  // -------------------------------------------------------------------------
  // configuration data
  // -------------------------------------------------------------------------

  var config = {
    serverUrl:    "../server/eventstreamer.php",
    serverPrefix: "../server/"
  };

  // -------------------------------------------------------------------------
  // interface types
  // -------------------------------------------------------------------------
  
  function Position(lat, lon) {
    this.lat = lat;
    this.lon = lon;
  }

  function Event(name, description, position, base64Image) {
    this.name        = name;     // String
    this.description = description;
    this.position    = position; // a Position object
    this.base64Image = (typeof base64Image === "undefined") ? null : base64Image;
    this.createdBy   = state.userName;
  }
  
  function User(name) {
    this.name = name;
  }
  
  function SetEvent(name) {
    this.name = name;
  }
  
  function UploadImage(base64Image, position, caption) {
    this.base64Image = base64Image;
    this.position    = position;
    this.caption     = caption;
  }
  
  // -------------------------------------------------------------------------
  // state data
  // -------------------------------------------------------------------------

  var state = {
    eventName:     null,
    eventId:       null,
    userName:      null,
    userId:        null,
    errorCallback: null
  };

  // enumeration of all server actions

  var actions = {
    setUser:           "setUser",
    setEvent:          "setEvent",
    getEventsCloseBy:  "getEventsCloseBy",
    getAllEvents:      "getAllEvents",
    createEvent:       "createEvent",
    uploadImage:       "uploadImage",
    getAllEventImages: "getAllEventImages"
  };

  // -------------------------------------------------------------------------
  // private functions
  // -------------------------------------------------------------------------

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
  
  function makeEventUrl(action, extras) {
    return config.serverUrl + 
             "?action=" + action +
             "&eventName=" + state.eventName +
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

  // -------------------------------------------------------------------------
  // exported functions
  // -------------------------------------------------------------------------

  function setEvent(event, callbacks) {
    function success(response) {
      log(response);
      if (response.payload.exists) {
        state.eventId = response.payload.event.id;
      }
      callbacks.success(response);
    }
    state.eventName = event.name;
    makeAjaxPostRequest(makeBaseUrl(actions.setEvent), event, {success: success, error: callbacks.error});
  }

  function setUser(user, callbacks) {
    function success(response) {
      state.userId = response.payload.id;
      callbacks.success(response);
    }
    state.userName = user.name;
    makeAjaxPostRequest(makeBaseUrl(actions.setUser), user, {success: success, error: callbacks.error});
  }

  function setErrorCallback(error) {
    state.errorCallback = error;
  }
  
  function getFileUrl(path) {
    return config.serverPrefix + path;
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

  function uploadImage(uploadImage, callbacks) {
    if (!checkEventState() || !checkUserState()) {
      return;
    }
    makeAjaxPostRequest(makeEventUserUrl(actions.uploadImage), uploadImage, callbacks);
  }

  function getAllEventImages(callbacks) {
    if (!checkEventState()) {
      return;
    }
    makeAjaxPostRequest(makeEventUrl(actions.getAllEventImages), {}, callbacks);
  }
  
  return {
    // types
    Position:          Position,
    Event:             Event,
    User:              User,
    SetEvent:          SetEvent,
    UploadImage:       UploadImage,
    
    // operations
    setEvent:          setEvent,
    setUser:           setUser,
    setErrorCallback:  setErrorCallback,
    getFileUrl:        getFileUrl,
    getEventsCloseBy:  getEventsCloseBy,
    getAllEvents:      getAllEvents,
    createEvent:       createEvent,
    uploadImage:       uploadImage,
    getAllEventImages: getAllEventImages
  }
  
}();
