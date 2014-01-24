'use strict';

var app = angular.module('eventStream', [ 'ionic', 'ngRoute' ]);

app.service('eventService', function () {
    console.log('eventService initialized');
    this.events = [
        { id: 'Event1', name: 'Foodies', description: 'Food anonymous', logo: 'images/food.png', location: 'SF' },
        { id: 'Event2', name: 'Choo choo', description: 'We love trains', logo: 'images/train.png', location: 'SC' },
        { id: 'Event3', name: 'Baby', description: 'Cribbing together since 1990', logo: 'images/babysitter.png', location: 'LA' },
        { id: 'Event:', name: 'Bronto club', description: 'It\'s a lie, they aren\'t extinct', logo: 'images/brontosaurus.png' }
    ];

    var that = this;
    this.eventById = function (id) {
        for (var i = 0; i < that.events.length; i++) {
            if (that.events[i].id === id) {
                return that.events[i];
            }
        }
    };
});

app.config(['$routeProvider', function ($routeProvider) {
    $routeProvider
        .when('/welcome', {
            templateUrl: 'partials/welcome.html',
            controller: 'WelcomeController'
        })
        .when('/events', {
            templateUrl: 'partials/events.html',
            controller: 'EventsListController'
        })
        .when('/events/:eventId', {
            templateUrl: 'partials/event-stream.html',
            controller: 'EventStreamController'
        })
        .otherwise({
            redirectTo: '/welcome'
        });
}]);

app.controller('EventsListController', [ '$scope', '$location', 'eventService', function ($scope, $location, eventService) {
    $scope.events = eventService.events;

    $scope.onRefresh = function () {
        console.log('refreshing. will fake complete in 3 secs');
        setTimeout(function () {
            $scope.$broadcast('scroll.refreshComplete');
            console.log('refresh complete');
        }, 3000);
    };

    $scope.showEvent = function (eventId) {
        console.log('will show event', eventId);
        $location.path('/events/' + eventId);
    };
}]);

app.controller('EventStreamController', [ '$scope', '$routeParams', 'eventService', '$window', function ($scope, $routeParams, eventService, $window) {
    console.log('will show the event id: ', $routeParams.eventId);
    $scope.event = eventService.eventById($routeParams.eventId);

    $scope.items = [
        { authorName: 'Girish',
          postDate: 'Sometime back',
          authorAvatar: 'images/avatar-anon.png',
          content: 'http://lorempixel.com/400/200?dummy=' + Math.random(),
          description: 'Insane!',
          likeCount: 4,
          commentCount: 2
        },
        { authorName: 'Peter',
          postDate: '1 hour ago',
          authorAvatar: 'images/avatar-king.png',
          content: 'http://lorempixel.com/400/200?dummy=' + Math.random(),
          description: 'This is so cool!',
          likeCount: 2,
          commentCount: 4
        },
        { authorName: 'Stephan',
          postDate: 'Yesterday',
          authorAvatar: 'images/avatar-horse.png',
          content: 'http://lorempixel.com/400/200?dummy=' + Math.random(),
          description: 'Boring...',
          likeCount: 99,
          commentCount: 12
        }
    ];

    $scope.showEvents = function () {
         $window.history.back();
    };
}]);

app.controller('WelcomeController', [ '$scope', '$location', function ($scope, $location) {
    var user = JSON.parse(localStorage.getItem('user'));
    if (!user) user = { name: 'Anonymous' };

    $scope.user = user;

    $scope.setUsername = function () {
        console.log('will set the username to ', $scope.user.name);
        localStorage.setItem('user', JSON.stringify(user));
        $location.path('/events');
    };
}]);

