function EventCtrl($scope) {

  $scope.events = [];

  $scope.clearEvents = function() {
    $scope.events = [];
  };

  $scope.showEvents = function() {
    server.getAllEvents(
      {success: function(response) {
         console.log(response);
         $scope.$apply(function() {
           var events   = response.payload.events;
           var ngEvents = [];
           events.forEach(function(e) {
             ngEvents.push({thumbnail: server.getFileUrl(e.imageThumbnail), description: e.description});
           });
           $scope.events = ngEvents;
         })
       },
       error: function(response) {console.log(response);}});
  }
}
