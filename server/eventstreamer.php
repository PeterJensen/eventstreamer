<?php
// --------------------
// Author: Peter Jensen
// --------------------

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------

class CConfig {
  const eventsDir           = "./events";
  const eventJsonFileName   = "event.json";
  const eventImageOriginal  = "event.jpg";
  const eventImageThumbnail = "event-thumb.jpg";
  const thumbnailWidth      = 120;
}

// ---------------------------------------------------------------------------
// Client REST API types and constants
// ---------------------------------------------------------------------------

class CGetParamKeys {
  const action    = "action";
  const eventName = "eventName";
  const userName  = "userName";
  const fileName  = "fileName";
}

// ?action values

class CActions {
  const uploadBlob       = "uploadBlob";
  const uploadBase64     = "uploadBase64";
  const getEventsCloseBy = "getEventsCloseBy";
  const getAllEvents     = "getAllEvents";
  const createEvent      = "createEvent";
}

// ---------------------------------------------------------------------------
// Request types.  Matches JS types defined in server.js
// ---------------------------------------------------------------------------

class CJsPosition {
  public $lat;
  public $lon;
}

class CJsEvent {
  public $name;
  public $description;
  public $position;
  public $base64Image;
  public $timestamp;
  public $createdBy;
}

// ---------------------------------------------------------------------------
// Response types.  There are returned as JSON payload data
// ---------------------------------------------------------------------------

class CCreateEventResponse {
  public $id;
  public $dir;
  
  public static function newFromEvent($event) {
    $response = new CCreateEventResponse();
    $response->id  = $event->id;
    $response->dir = $event->dir;
    return $response;
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

// ---------------------------------------------------------------------------
// Utilities for creating the proper JSON response
// ---------------------------------------------------------------------------

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

class CEvents {
  public $events;
}

class CImage {
  public $smallImage;
  public $mediumImage;
  public $largeImage;
  public $originalImage;
  public $author;
  public $imageTimestamp;
  public $uploadTimestamp;
}

class CEventNames {
  public $eventNames;
}

// ---------------------------------------------------------------------------
// Image creation utilities
// ---------------------------------------------------------------------------

class CImageUtil {

  private static function stripPrefix($base64) {
    $pos = strpos($base64, ",");
    if ($pos !== false) {
      return substr($base64, $pos+1);
    }
    else {
      return $base64;
    }
  }

  public static function saveBase64Image($base64, $filename) {
    file_put_contents($filename, base64_decode(self::stripPrefix($base64)));
  }
  
  public static function saveBase64ImageResized($base64, $filename, $width) {
    $img  = imagecreatefromstring(base64_decode(self::stripPrefix($base64)));
    $orgWidth  = imagesx($img);
    $orgHeight = imagesy($img);
    $height = $orgHeight * $width/$orgWidth; // scale the height;
    $newImg = imagecreatetruecolor($width, $height);
    imagecopyresampled($newImg, $img, 0, 0, 0, 0, $width, $height, $orgWidth, $orgHeight);
    imagejpeg($newImg, $filename, 90);
  }
  
}
  
// ---------------------------------------------------------------------------
// Event Database
// ---------------------------------------------------------------------------

// Database Types

class CStatus {
  public $success;
  public $errorMessage;
}

class CEvent {
  public $name                = null;
  public $id                  = null;
  public $dir                 = null;
  public $timestamp           = null;
  public $createdBy           = null;
  public $position            = null;
  public $eventImageOriginal  = null;
  public $eventImageThumbnail = null;
  public $images              = array();
  
  public static function newFromJsEvent($jsEvent) {
    $event = new CEvent();
    $event->name        = $jsEvent->name;
    $event->description = $jsEvent->description;
    $event->position    = $jsEvent->position;
    $event->timestamp   = $jsEvent->timestamp;
    $event->createdBy   = $jsEvent->createdBy;
    return $event;
  }
  
}

// Database Operations

class CEventDb {

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
  
  public static function createEvent(&$event, &$status) {
    $status->success = true;  // assume event creation will succeed
    
    // Create an event id
    $event->id = self::createEventId($event);

    // Create the event dir
    $event->dir = self::createEventDir($event, $status);
    if (!$status->success) {
      return null;
    }

    // Create the event JSON file
    $eventJsonFile = $event->dir . "/" . CConfig::eventJsonFileName;
    $ret = @file_put_contents($eventJsonFile, json_encode($event));
    if ($ret === false) {
      $status->success = false;
      $status->errorMessage = "Cannot create event json file: $eventJsonFile";
    }
  }
}

// ---------------------------------------------------------------------------
// Handlers for the various actions
// ---------------------------------------------------------------------------

class CActionHandlers {

  public static function uploadBlob($request, &$response) {
    $response->fail("saveBlob not implemented yet");
  }
  
  public static function uploadBase64($request, &$response) {
    $response->fail("saveBase64 not implemented yet");
  }
  
  public static function getEventsCloseBy($request, &$response) {
    $response->fail("getEventsCloseBy not implemented yet");
  }

  public static function getAllEvents($request, &$response) {
    $response->fail("getAllEvents not implemented yet");
  }

  public static function createEvent($request, &$response) {
    // userName must be specified
    if ($request->get->userName === null) {
      $response->fail("userName not specified");
      return;
    }
    if ($request->post->payload === null) {
      $response->fail("payload not specified");
      return;
    }
    
    $payload = json_decode($request->post->payload);
    // Transfer the event data from the request and create the DB entry
    $event  = CEvent::newFromJsEvent($payload);
    
    // Check if an event image is attached
    if ($payload->base64Image !== null) {
      $event->eventImageOriginal  = CConfig::eventImageOriginal;
      $event->eventImageThumbnail = CConfig::eventImageThumbnail;
    }
    
    // Add the event to the database
    $status = new CStatus();
    CEventDb::createEvent($event, $status);
    if (!$status->success) {
      $response->fail($status->errorMessage);
      return;
    }

    // Create the images
    if ($payload->base64Image !== null) {
      CImageUtil::saveBase64Image($payload->base64Image, $event->dir . "/" . $event->eventImageOriginal);
      CImageUtil::saveBase64ImageResized($payload->base64Image, $event->dir . "/" . $event->eventImageThumbnail, CConfig::thumbnailWidth);
    }
      
    // Create the response object
    $createEventResponse = CCreateEventResponse::newFromEvent($event);
    $response->success($createEventResponse);
  }
}

function main() {
  $request   = new CRequestParams();
  $response  = new CResponse();
  
  switch ($request->get->action) {
    case CActions::uploadBlob:
      CActionHandlers::uploadBlob($request, $response);
      break;
    case CActions::uploadBase64:
      CActionHandlers::uploadBase64($request, $response);
      break;
    case CActions::getEventsCloseBy:
      CActionHandlers::getEventsCloseBy($request, $response);
      break;
    case CActions::getAllEvents:
      CActionHandlers::getAllEvents($request, $response);
      break;
    case CActions::createEvent:
      CActionHandlers::createEvent($request, $response);
      break;
    default:
      $response->fail("Unknown action: $request->get->action");
      break;
  }
  echo $response->toJson();
}

main();
?>
