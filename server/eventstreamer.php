<?php
// --------------------
// Author: Peter Jensen
// --------------------

class CConfig {
  const ROOT       = "http://www.danishdude.com/eventstreamer";
  const EVENTS_DIR = "events";
  const FORM_NAME  = "eventImage";
}

class CEvents {
  public $events;
}

class CEvent {
  public $name;
  public $dir;
  public $dateCreated;
  public $images;
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

class CParams {
  public $action; // upload, showEvents, getEvent
  public $uploadFile;
  public $event;
  public $success;
  public $message;

  private function parseParams () {
    if (!isset($_GET["action"])) {
      $this->message = "?action not specified";
      $this->success = false;
      return;
    }
    $this->action = $_GET["action"];
    switch ($this->action) {
      case "upload":
        if (!isset($_GET["event"])) {
          $this->message = "?event not specified";
          $this->success = false;
          return;
        }
        $this->event = $_GET["event"];
        if (!isset($_FILES[CConfig::FORM_NAME])) {
          $this->message = "upload file not available";
          $this->success = false;
          return;
        }
        $this->uploadFile = $_FILES[CConfig::FORM_NAME];
        break;
          
      case "showEvents":
      case "getEvent":
      default:
        $this->message = "Unknown action '$this->action' specified";
        $this->success = false;
        return;
    }
    $this->success = true;
  }
  
  public function __construct () {
    $this->parseParams();
  }
  
}

class CResponse {
  public $status;
  public $message;
  public $payload;
  
  public function __construct() {
    $this->status  = "No response";
    $this->message = "";
    $this->payload = null;
  }
  
  public function success($payload) {
    $this->status  = "Success";
    $this->message = "";
    $this->payload = $payload;
  }
  
  public function fail($failReason) {
    $this->status = "Fail";
    $this->message = $failReason;
  }
  
  public function toJson() {
    return json_encode($this);
  }
  
}

class CEventDb {

  public static function saveImage(&$response, $params) {
    $response->fail ("Uploading not implemented yet");
  }
  
  public static function eventNames(&$response, $params) {
    $response->fail ("Showing events not implemented yet");
  }
  
  public static function getEvent(&$response, $params) {
    $response->fail ("Retrieving event not implemented yet");
  }
  
}

function main() {
  $params   = new CParams();
  $response = new CResponse();
  
  if (!$params->success) {
    $response->fail($params->message);
  }
  else {
    switch ($params->action) {
      case "upload":
        CEventDb::saveImage($response, $params);
        break;
      case "showEvents":
        CEventDb::eventNames($response, $params);
        break;
      case "getEvent":
        CEventDb::getEvent($response, $params);
        break;
    }
  }
  echo $response->toJson();
}

main();
?>