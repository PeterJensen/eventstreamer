<?php
// --------------------
// Author: Peter Jensen
// --------------------

class CConfig {
  const eventsDir           = "./events";
  const eventJsonFileName   = "event.json";
  const eventsJsonFileName  = "events.json";
  const eventDatFileName    = "event.txt";
  const eventsDatFileName   = "events.txt";
  const eventImageOriginal  = "event.jpg";
  const eventImageThumbnail = "event-thumb.jpg";
  const thumbnailWidth      = 120;
}

// Client REST API types and constants

class CGetParamKeys {
  const action    = "action";
  const eventName = "eventName";
  const userName  = "userName";
  const fileName  = "fileName";
}

class CActions {
  const uploadBase64     = "uploadBase64";
  const getEventsCloseBy = "getEventsCloseBy";
  const getAllEvents     = "getAllEvents";
  const createEvent      = "createEvent";
}

// Matches for JS object types

class CJsPosition {
  public $lat;
  public $lon;
}

class CJsEvent {
  public $name;
  public $position;
  public $description;
  public $timestamp;
  public $createdBy;
  public $id;
  public $dir;
}

class CEvents {
  public $events;
}

class CEvent {
  public $name;
  public $dir;
  public $timeStamp;
  public $images;
}

class CGetAllEventsResponse {
  public $events = array();
  
  public function addEvent($event) {
    $this->events[] = $event;
  }
}

// ---------------------------------------------------------------------------
// Utilities for getting GET and POST request parameters
// ---------------------------------------------------------------------------

class CGetParamValues {
  public $action    = null;
  public $eventName = null;
  public $userName  = null;
  public $fileName  = null;

  private static function paramValue($key) {
    if (!isset($_GET[$key])) {
      return null;
    }
    return $_GET[$key];
  }
  
  private function parseParams() {
    $this->action    = self::paramValue(CGetParamKeys::action);
    $this->eventName = self::paramValue(CGetParamKeys::eventName);
    $this->userName  = self::paramValue(CGetParamKeys::userName);
    $this->fileName  = self::paramValue(CGetParamKeys::fileName);
  }
  
  public function __construct() {
    $this->parseParams();
  }
}

class CPostParamValues {
  public $payload = null;
  
  private static function paramValue($key) {
    if (!isset($_POST[$key])) {
      return null;
    }
    return $_POST[$key];
  }
  
  private function parseParams() {
    $this->payload = self::paramValue("payload");
  }
  
  public function __construct() {
    $this->parseParams();
  }
}

class CRequestParams {
  public $get;
  public $post;
  
  public function __construct() {
    $this->get  = new CGetParamValues();
    $this->post = new CPostParamValues();
  }
}

class CResponse {
  public $status;
  public $errorMessage;
  public $payload;
  
  public function __construct() {
    $this->status       = "No response";
    $this->errorMessage = "";
    $this->payload      = null;
  }
  
  public function success($payload) {
    $this->status       = "Success";
    $this->errorMessage = "";
    $this->payload      = $payload;
  }
  
  public function fail($failReason) {
    $this->status       = "Fail";
    $this->errorMessage = $failReason;
  }
  
  public function toJson() {
    return json_encode($this);
  }
}

class CStatus {
  public $success;
  public $errorMessage;
}

class CEvent {
  public $name                = null;
  public $description         = null;
  public $position            = null;
  public $timestamp           = null;
  public $createdBy           = null;
  public $id                  = null;
  public $dir                 = null;
  public $eventImageOriginal  = null;
  public $eventImageThumbnail = null;
  
  public static function newFromJsEvent($jsEvent) {
    $event = new CEvent();
    $event->name        = $jsEvent->name;
    $event->description = $jsEvent->description;
    $event->position    = $jsEvent->position;
    $event->timestamp   = $jsEvent->timestamp;
    $event->createdBy   = $jsEvent->createdBy;
    return $event;
  }
  
  public static function newFromJson($json) {
    // as long as there are only static methods in this class this will work
    $event = json_decode($json);
    return $event;
  }
  
}

// Database Operations

class CEventDb {

  // Private functions
  
  private static function createEventId($event) {
    $id = "";
    $name = $event->name;
    for ($i = 0; $i < strlen($name); ++$i) {
      $c = $name{$i};
      if (($c >= 'A' && $c <= 'Z') ||
          ($c >= 'a' && $c <= 'z') ||
          ($c >= '0' && $c <= '9')) {
        $id .= $c;
      }
    }
    return $id;
  }

  private static function createEventDir($event, &$status) {
    // Check if directory already exists
    $eventDir = CConfig::eventsDir . "/" . $event->id;
    if (is_dir($eventDir)) {
      $status->success = false;
      $status->errorMessage = "$event->name already exists";
      return null;
    }
    if (!@mkdir($eventDir)) {
      $status->success = false;
      $status->errorMessage = "Cannot create event directory: $eventDir";
      return null;
    }
    return $eventDir;
  }

  private static function createFile($fileName, $contents, &$status) {
    $ret = @file_put_contents($fileName, $contents);
    if ($ret === false) {
      $status->success = false;
      $status->errorMessage = "Cannot create $filename";
      return;
    }
  }
  
  private static function addEvent($event) {
    
  }
  
  // Public functions
  
  public static function createEvent(&$event, &$status) {
    $status->success = true;  // assume event creation will succeed
    
    // Create an event id
    $event->id = self::createEventId($event);

    // Create the event dir
    $event->dir = self::createEventDir($event, $status);
    if (!$status->success) {
      return;
    }

    // Create the event.json file
    self::createFile($event->dir . "/" . CConfig::eventJsonFileName, json_encode($event), $status);
    if (!$status->success) {
      return;
    }
    
    // Create the event.dat file
    self::createFile($event->dir . "/" . CConfig::eventDatFileName, serialize($event), $status);
    if (!$status->success) {
      return;
    }

    self::addEvent($event);
  }
  
  public static function getAllEvents(&$events, &$status) {
    $status->success = true;
    $events = array();
    $eventJsonFiles = self::getAllEventJsonFiles();
    foreach ($eventJsonFiles as $file) {
      $events[] = CEvent::newFromJson(file_get_contents($file));
    }
  }
  
}

class CActionHandlers {

  public static function uploadBase64($request, &$response) {
    $response->fail("saveBase64 not implemented yet");
  }
  
  public static function getEventsCloseBy(&$response, $request) {
    $response->fail("getEventsCloseBy not implemented yet");
  }

  public static function getAllEvents(&$response, $request) {
    $response->fail("getAllEvents not implemented yet");
  }

  public static function createEvent(&$response, $request) {
    if ($request->get->userName === null) {
      $response->fail("userName not specified");
      return;
    }
    if ($request->post->payload === null) {
      $response->fail("payload not specified");
      return;
    }
    $event  = json_decode($request->post->payload);
    $status = new CStatus();
    CEventDb::createEvent($event, $status);
    if (!$status->success) {
      $response->fail($status->errorMessage);
      return;
    }
    $response->success($event);
  }
}

function main() {
  $request   = new CRequestParams();
  $response  = new CResponse();
  
  switch ($request->get->action) {
    case CActions::uploadBase64:
      CActionHandlers::uploadBase64($response, $request);
      break;
    case CActions::getEventsCloseBy:
      CActionHandlers::getEventsCloseBy($response, $request);
      break;
    case CActions::getAllEvents:
      CActionHandlers::getAllEvents($response, $request);
      break;
    case CActions::createEvent:
      CActionHandlers::createEvent($response, $request);
      break;
    default:
      $response->fail("Unknown action: $request->get->action");
      break;
  }
  echo $response->toJson();
}

main();
?>
