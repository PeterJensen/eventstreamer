<?php
// --------------------
// Author: Peter Jensen
// --------------------

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------

class CConfig {
  const usersDir            = "users";
  const usersJsonFileName   = "users.json";
  const usersDatFileName    = "users.ser";
  const eventsDir           = "events";
  const eventJsonFileName   = "event.json";
  const eventDatFileName    = "event.ser";
  const eventsJsonFileName  = "events.json";
  const eventsDatFileName   = "events.ser";
  const eventImageOriginal  = "event.jpg";
  const eventImageThumbnail = "event-thumb.jpg";
  const imagesJsonFileName  = "images.json";
  const imagesDatFileName   = "images.ser";
  const imageJsonFileName   = "image.json";
  const imageDatFileName    = "image.ser";
  const lastIdFileName      = "last-id.txt";
  const thumbnailWidth      = 120;
  const smallWidth          = 320;
  const mediumWidth         = 640;
  const largeWidth          = 1280;
}

// ---------------------------------------------------------------------------
// Client REST API types and constants
// ---------------------------------------------------------------------------

class CGetParamKeys {
  const action    = "action";
  const eventName = "eventName";
  const userName  = "userName";
}

// ?action values

class CActions {
  const setUser           = "setUser";
  const setEvent          = "setEvent";
  const getEventsCloseBy  = "getEventsCloseBy";
  const getAllEvents      = "getAllEvents";
  const createEvent       = "createEvent";
  const uploadImage       = "uploadImage";
  const getAllEventImages = "getAllEventImages";
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

class CJsUser {
  public $name;
}

class CJsSetEvent {
  public $name;
}

class CJsUploadImage {
  public $base64Image;
  public $position;
  public $caption;
}

// ---------------------------------------------------------------------------
// Response types.  These are returned as JSON payload data
// ---------------------------------------------------------------------------

class CCreateEventResponse {
  public $id;
  public static function newFromEvent($event) {
    $response = new CCreateEventResponse();
    $response->id  = $event->id;
    return $response;
  }
}

class CGetAllEventsResponse {
  public static function newFromEvents($events) {
    return $events;
  }
}

class CSetUserResponse {
  public $id;
  public $isNew;
  public $timestamp;
  public static function newFromUser($user) {
    $response = new CSetUserResponse();
    $response->id = $user->id;
    $response->isNew = true;
    $response->timestamp = $user->timestamp;
    return $response;
  }
}

class CSetEventResponse {
  public $event;
  public static function newFromEvent($event) {
    $response = new CSetEventResponse();
    $response->exists = ($event !== null);
    $response->event = $event;
    return $response;
  }    
}

class CUploadImageResponse {
  public $id;
  public $timestamp;
  public $imageOriginal;
  public static function newFromImage($image) {
    $response = new CUploadImageResponse();
    $response->timestamp = $image->timestamp;
    $response->id = $image->id;
    $response->imageOriginal = $image->imageOriginal;
    return $response;
  }
}

class CGetAllEventImagesResponse {
  public static function newFromImages($images) {
    return $images;
  }
}

// ---------------------------------------------------------------------------
// Utilities for getting GET and POST request parameters
// ---------------------------------------------------------------------------

class CGetParamValues {
  public $action    = null;
  public $eventName = null;
  public $userName  = null;

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
// File system utilities
// ---------------------------------------------------------------------------

class CFileUtil {

  public static function createFile($fileName, $contents, &$status) {
    $status->success = true;
    $ret = @file_put_contents($fileName, $contents);
    if ($ret === false) {
      $status->success = false;
      $status->errorMessage = "Cannot create $filename";
      return false;
    }
    else {
      return true;
    }
  }

  public static function createDir($dir, &$status) {
    $status->success = true;
    // Check if directory already exists
    if (is_dir($dir)) {
      $status->success = false;
      $status->errorMessage = "$dir already exists";
      return false;
    }
    if (!@mkdir($dir)) {
      $status->success = false;
      $status->errorMessage = "Cannot create directory: $dir";
      return false;
    }
    return true;
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

class CUser {
  public $id        = null;
  public $name      = null;
  public $timestamp = null;
  
  public static function newFromJsUser($jsUser) {
    $user = new CUser();
    $user->name = $jsUser->name;
    return $user;
  }
}

class CEvent {
  public $id             = null;
  public $name           = null;
  public $description    = null;
  public $position       = null;
  public $timestamp      = null;
  public $createdBy      = null;
  public $imageOriginal  = null;
  public $imageThumbnail = null;
  
  public static function newFromJsEvent($jsEvent) {
    $event = new CEvent();
    $event->name        = $jsEvent->name;
    $event->description = $jsEvent->description;
    $event->position    = $jsEvent->position;
    $event->createdBy   = $jsEvent->createdBy;
    if ($jsEvent->base64Image !== null) {
      $event->imageOriginal  = CConfig::eventImageOriginal;
      $event->imageThumbnail = CConfig::eventImageThumbnail;
    }
    return $event;
  }
  
}

class CEvents {
  public $events = array();
  
  public static function newFromDatFile($datFileName) {
    if (is_file($datFileName)) {
      return unserialize(file_get_contents($datFileName));
    }
    else {
      return new CEvents();
    }
  }

  public function addEvent($event) {
    array_unshift($this->events, $event);
  }
}

class CImage {
  public $id;
  public $imageSmall;
  public $imageMedium;
  public $imageLarge;
  public $imageOriginal;
  public $author;
  public $timestamp;
  public $position;
  public $caption;
  
  public static function newFromJsImage($jsImage) {
    $image = new CImage();
    $image->position = $jsImage->position;
    $image->caption  = $jsImage->caption;
    return $image;
  }
}

class CImages {
  public $images = array();
  
  public static function newFromDatFile($datFileName) {
    if (is_file($datFileName)) {
      return unserialize(file_get_contents($datFileName));
    }
    else {
      return new CImages();
    }
  }
  
  public function addImage($image) {
    array_unshift($this->images, $image);
  }
}


// Database Operations

class CEventDb {

  // Private functions
  
  private static function removeFunnyChars($str) {
    $newStr = "";
    for ($i = 0; $i < strlen($str); ++$i) {
      $c = $str{$i};
      if (($c >= 'A' && $c <= 'Z') ||
          ($c >= 'a' && $c <= 'z') ||
          ($c >= '0' && $c <= '9')) {
        $newStr .= $c;
      }
    }
    return $newStr;
  }
  
  private static function createEventId($event) {
    return self::removeFunnyChars($event->name);
  }
  
  private static function createUserId($user) {
    return self::removeFunnyChars($user->name);
  }

  private static function eventDir($event) {
    return CConfig::eventsDir ."/" . $event->id;
  }
  
  private static function imageDir($event, $image) {
    return CConfig::eventsDir . "/" . $event->id . "/" . $image->id;
  }
    
  private static function createEventDir($event, &$status) {
    CFileUtil::createDir(self::eventDir($event), $status);
  }

  private static function createDataFiles($data, $jsonFileName, $datFileName, &$status) {
    CFileUtil::createFile($jsonFileName, json_encode($data), $status);
    if (!$status->success) {
      return false;
    }
    CFileUtil::createFile($datFileName, serialize($data), $status);
    if (!$status->success) {
      return false;
    }
    return true;
  }
  
  private static function updateAllEvents($event, &$status) {
    $jsonFileName = CConfig::eventsDir . "/" . CConfig::eventsJsonFileName;
    $datFileName = CConfig::eventsDir . "/" . CConfig::eventsDatFileName;
    $events = CEvents::newFromDatFile($datFileName);
    $events->addEvent($event);
    self::createDataFiles($events, $jsonFileName, $datFileName, $status);
  }
  
  private static function readEventsFromFile() {
    return CEvents::newFromDatFile(CConfig::eventsDir . "/" . CConfig::eventsDatFileName);
  }

  private static function readEventImagesFromFile($event) {
    return CImages::newFromDatFile(self::eventDir($event) . "/" . CConfig::imagesDatFileName);
  }
  
  private static function createNextImageId($event) {
    $dir = self::eventDir($event);
    $lastIdFileName = $dir . "/" . CConfig::lastIdFileName;
    if (!is_file($lastIdFileName)) {
      $lastId = 0;
    }
    else {
      $lastId = file_get_contents($lastIdFileName);
    }
    $nextId = sprintf("%06d", $lastId + 1);
    file_put_contents($lastIdFileName, $nextId);
    return $nextId;
  }
  
  private static function updateAllImages($event, $image, &$status) {
    $jsonFileName = self::eventDir($event) . "/" . CConfig::imagesJsonFileName;
    $datFileName =  self::eventDir($event) . "/" . CConfig::imagesDatFileName;
    $images = CImages::newFromDatFile($datFileName);
    $images->addImage($image);
    self::createDataFiles($images, $jsonFileName, $datFileName, $status);
  }

  // Public functions
  
  public static function setUser(&$user, $status) {
    $status->success = true;
    $user->id = self::createuserId($user);
    $user->timestamp = time();
    return true;
  }
  
  public static function lookupEventByName($eventName, &$event, &$status) {
    $status->success = true;
    $events = self::readEventsFromFile();
    foreach ($events->events as $e) {
      if ($eventName === $e->name) {
        $event = $e;
        return true;
      }
    }
    $event = null;
    $status->success = false;
    $status->errorMessage = "$eventName not found";
    return false;
  }

  public static function createEvent(&$event, &$status) {
    $status->success = true;  // assume event creation will succeed
    
    // Create an event id
    $event->id = self::createEventId($event);
    
    // Set the timestamp
    $event->timestamp = time();

    // Create the event dir
    self::createEventDir($event, $status);
    if (!$status->success) {
      return false;
    }
    $dir = self::eventDir($event);
    
    // add prefix to image filenames
    if ($event->imageOriginal !== null) {
      $event->imageOriginal  = $dir . "/" . $event->imageOriginal;
      $event->imageThumbnail = $dir . "/" . $event->imageThumbnail;
    }

    // Create the event data files
    self::createDataFiles(
      $event,
      $dir . "/" . CConfig::eventJsonFileName,
      $dir . "/" . CConfig::eventDatFileName,
      $status);
    if (!$status->success) {
      return false;
    }

    // Update the global event data
    self::updateAllEvents($event, $status);
    if (!$status->success) {
      return false;
    }
    
    return true;
  }
  
  public static function getAllEvents(&$events, &$status) {
    $events = self::readEventsFromFile();
  }
  
  public static function addImageToEvent(&$image, $eventName, &$status) {
    $status->success = true;
    self::lookupEventByName($eventName, $event, $status);
    if (!$status->success) {
      return false;
    }
    
    // Get the next available image id
    $image->id = self::createNextImageId($event);
    
    // Set the timestamp
    $image->timestamp = time();

    // Create the dir to hold all image sizes    
    $dir = self::eventDir($event);
    $imageDir = $dir . "/" . $image->id;
    CFileUtil::createDir($imageDir, $status);
    if (!$status->success) {
      $status->success = false;
      return false;
    }
    
    // Set up all the image filename variants
    $image->imageThumbnail = $imageDir . "/t.jpg";
    $image->imageSmall     = $imageDir . "/s.jpg";
    $image->imageMedium    = $imageDir . "/m.jpg";
    $image->imageLarge     = $imageDir . "/l.jpg";
    $image->imageOriginal  = $imageDir . "/o.jpg";
    
    // Create the image data files
    self::createDataFiles(
      $image,
      $imageDir . "/" . CConfig::imageJsonFileName,
      $imageDir . "/" . CConfig::imageDatFileName,
      $status);
    if (!$status->success) {
      return false;
    }
    
    // Update the image data for the event
    self::updateAllImages($event, $image, $status);
    if (!$status->success) {
      return false;
    }

    return true;    
  }

  public static function getAllEventImages($eventName, &$images, &$status) {
    self::lookupEventByName($eventName, $event, $status);
    if (!$status->success) {
      return false;
    }
    $images = self::readEventImagesFromFile($event);
  }
  
}

// ---------------------------------------------------------------------------
// Handlers for the various actions
// ---------------------------------------------------------------------------

class CActionHandlers {

  public static function setUser($request, &$response) {
    $status = new CStatus();
    $payload = json_decode($request->post->payload);
    $user = CUser::newFromJsUser($payload);
    CEventDb::setUser($user, $status);
    if (!$status->success) {
      $response->fail($status->errorMessage);
      return;
    }
    $setUserResponse = CSetUserResponse::newFromUser($user);
    $response->success($setUserResponse);
  }
  
  public static function setEvent($request, &$response) {
    $status  = new CStatus();
    $payload = json_decode($request->post->payload);
    CEventDb::lookupEventByName($payload->name, $event, $status);
    $setEventResponse = CSetEventResponse::newFromEvent($event);
    $response->success($setEventResponse);
  }

  public static function getEventsCloseBy($request, &$response) {
    $response->fail("getEventsCloseBy not implemented yet");
  }

  public static function getAllEvents($request, &$response) {
    $status = new CStatus();
    CEventDb::getAllEvents($events, $status);
    $getAllEventsResponse = CGetAllEventsResponse::newFromEvents($events);
    $response->success($getAllEventsResponse);
  }

  public static function createEvent($request, &$response) {
    $status = new CStatus();
    // userName must be specified
    if ($request->get->userName === null) {
      $response->fail("userName not specified");
      return;
    }
    if ($request->post->payload === null) {
      $response->fail("payload not specified");
      return;
    }
    
    // Transfer the event data from the request and create the DB entry
    $payload = json_decode($request->post->payload);
    $event   = CEvent::newFromJsEvent($payload);
    
    // Add the event to the database
    CEventDb::createEvent($event, $status);
    if (!$status->success) {
      $response->fail($status->errorMessage);
      return;
    }

    // Event was successfully created, so it's OK to create the images
    if ($event->imageOriginal !== null) {
      CImageUtil::saveBase64Image($payload->base64Image, $event->imageOriginal);
      CImageUtil::saveBase64ImageResized($payload->base64Image, $event->imageThumbnail, CConfig::thumbnailWidth);
    }

    // Create the response object
    $createEventResponse = CCreateEventResponse::newFromEvent($event);
    $response->success($createEventResponse);
  }
  
  public static function uploadImage($request, $response) {
    $status = new CStatus();
    $user          = $request->get->userName;
    $eventName     = $request->get->eventName;
    $payload       = json_decode($request->post->payload);
    $image         = CImage::newFromJsImage($payload);
    $image->author = $user;
    
    // Add the image to the event in the database
    CEventDb::addImageToEvent($image, $eventName, $status);
    if (!$status->success) {
      $response->fail($status->errorMessage);
      return;
    }
    
    // Image was successfully added to the database, so create the images
    CImageUtil::saveBase64Image($payload->base64Image, $image->imageOriginal);
    CImageUtil::saveBase64ImageResized($payload->base64Image, $image->imageThumbnail, CConfig::thumbnailWidth);
    CImageUtil::saveBase64ImageResized($payload->base64Image, $image->imageSmall, CConfig::smallWidth);
    CImageUtil::saveBase64ImageResized($payload->base64Image, $image->imageMedium, CConfig::mediumWidth);
    CImageUtil::saveBase64ImageResized($payload->base64Image, $image->imageLarge, CConfig::largeWidth);
    
    // Create the response object
    $uploadImageResponse = CUploadImageResponse::newFromImage($image);
    $response->success($uploadImageResponse);
  }
  
  public static function getAllEventImages($request, $response) {
    $status = new CStatus();
    CEventDb::getAllEventImages($request->get->eventName, $images, $status);
    $getAllEventImagesResponse = CGetAllEventImagesResponse::newFromImages($images);
    $response->success($getAllEventImagesResponse);
  }
}

function main() {
  $request   = new CRequestParams();
  $response  = new CResponse();
  
  switch ($request->get->action) {
    case CActions::setUser:
      CActionHandlers::setUser($request, $response);
      break;
    case CActions::setEvent:
      CActionHandlers::setEvent($request, $response);
      break;
    case CActions::uploadImage:
      CActionHandlers::uploadImage($request, $response);
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
    case CActions::uploadImage:
      CActionHandlers::uploadImage($request, $response);
      break;
    case CActions::getAllEventImages:
      CActionHandlers::getAllEventImages($request, $response);
      break;
    default:
      $response->fail("Unknown action: $request->get->action");
      break;
  }
  echo $response->toJson();
}

main();
?>
