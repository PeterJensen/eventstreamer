// ----------------------------------
// Author: Peter Jensen
// ----------------------------------

var server = function () {
  
  var uploadUrl = "../server/upload.php";
  
  function uploadBlob (fileName, blob, progress, success, error) {
    $.ajax({
      url: uploadUrl + "?filename=" + fileName,
      type: "POST",
      data: blob,
      success: success,
      processData: false,
      contentType: "image/jpeg",
      error: error,
      xhr: function() {
        myXhr = $.ajaxSettings.xhr();
        if(myXhr.upload){
          myXhr.upload.addEventListener('progress', progress, false);
        } else {
          console.log("Upload progress is not supported.");
        }
        return myXhr;
      }
    });
  }
  
  function uploadBase64 (fileName, base64, progress, success, error) {
    $.ajax({
      url: uploadUrl,
      type: "POST",
      data: {filename: fileName, filedata: base64},
      success: success,
      error: error,
      xhr: function() {
        myXhr = $.ajaxSettings.xhr();
        if(myXhr.upload){
          myXhr.upload.addEventListener('progress', progress, false);
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
