// Author: Peter Jensen
/*globals console: true */
/*globals $: true */
/*globals console: true */
/*globals localStorage: true */
(function () {

  // Create some UI elements
  var $userName   = $("#userName");
  var $eventName  = $("#eventName");
  var $userInput  = $("<input>").attr("type", "text").attr("placeholder", "UserName").addClass("form-control");
  var $eventInput = $("<input>").attr("type", "text").attr("placeholder", "EventName").addClass("form-control");

  var state = {
    data : {
      userName: null,
      eventName: null
    },
    save: function() {
      localStorage.setItem("state", JSON.stringify(state.data));
    },
    restore: function() {
      var savedState = localStorage.getItem("state");
      if (savedState !== null) {
        state.data = JSON.parse(savedState);
      }
    }
  };
  
  function navbarClick(e) {
    var $this = $(this);
    if (!$this.hasClass("active")) {
      var $newHref = $this.find("a").attr("href");
      var $oldHref = $(".navbar li.active a").attr("href");
      $(".navbar li.active").removeClass("active");
      $($oldHref).addClass("hidden");
      $($newHref).removeClass("hidden");
      $this.addClass("active");
    }
    if ($(".navbar-toggle").is(":visible")) {
      $(".navbar-collapse").removeClass("in");
    }
    e.preventDefault();
  }

  function setUserClick(e) {
    var $parent = $userName.parent();
    $parent.empty().append($userInput);
    $userInput.focus();
    $userInput.keyup(function(e) {
      if (e.keyCode === 13) {
        var userName = $userInput.val();
        console.log(userName);
        $userName.text(userName);
        $parent.empty().append($userName);
        state.data.userName = userName;
        state.save();
      }
      else if (e.keyCode === 27) {
        $parent.empty().append($userName);
      }
    });
    e.preventDefault();
  }

  function setEventClick(e) {
    var $parent = $eventName.parent();
    $parent.empty().append($eventInput);
    $eventInput.focus();
    $eventInput.keyup(function(e) {
      if (e.keyCode === 13) {
        var eventName = $eventInput.val();
        console.log(eventName);
        $eventName.text(eventName);
        $parent.empty().append($eventName);
        state.data.eventName = eventName;
        state.save();
      }
      else if (e.keyCode === 27) {
        $parent.empty().append($eventName);
      }
    });
    e.preventDefault();
  }

  function setFromState() {
    state.restore();
    if (state.data.userName !== null) {
      $userName.text(state.data.userName);
    }
    if (state.data.eventName !== null) {
      $eventName.text(state.data.eventName);
    }
  }
  
  function main() {
    setFromState();
    $(".navbar li").click(navbarClick);
    $("a[href=#setUser]").click(setUserClick);
    $("a[href=#setEvent]").click(setEventClick);
  }

  $(main);
}());