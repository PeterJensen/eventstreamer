// ----------------------------------
// Author: Peter Jensen
// ----------------------------------

var server = function () {
  
  var uploadUrl = "../server/upload.php";
  
  function uploadBlob (fileName, blob, callbacks) {
    $.ajax({
      url: uploadUrl + "?filename=" + fileName,
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
          console.log("Upload progress is not supported.");
        }
        return myXhr;
      }
    });
  }
  
  function uploadBase64 (fileName, base64, callbacks) {
    $.ajax({
      url: uploadUrl,
      type: "POST",
      data: {filename: fileName, filedata: base64},
      success: callbacks.success,
      error: callbacks.error,
      xhr: function() {
        myXhr = $.ajaxSettings.xhr();
        if(myXhr.upload){
          myXhr.upload.addEventListener('progress', callbacks.progress, false);
        } else {
          console.log("Upload progress is not supported.");
        }
        return myXhr;
      }
    });
  }

  return {
    uploadBlob:   uploadBlob,
    uploadBase64: uploadBase64
  }
  
}();
