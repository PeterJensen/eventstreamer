'use strict';

var app = angular.module('eventStream', [ 'ionic', 'ngRoute' ]);

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

app.controller('EventsListController', [ '$scope', '$location', function ($scope, $location) {
    $scope.events = [
        { id: 'Event1', name: 'Foodies', description: 'Food anonymous', logo: 'images/food.png', location: 'SF' },
        { id: 'Event1', name: 'Choo choo', description: 'We love trains', logo: 'images/train.png', location: 'SC' },
        { id: 'Event1', name: 'Baby', description: 'Cribbing together since 1990', logo: 'images/babysitter.png', location: 'LA' },
        { id: 'Event1', name: 'Bronto club', description: 'It\'s a lie, they aren\'t extinct', logo: 'images/brontosaurus.png' }
    ];

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

app.controller('EventStreamController', [ '$scope', '$routeParams', function ($scope, $routeParams) {
    console.log('will show the event id: ', $routeParams.eventId);
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

