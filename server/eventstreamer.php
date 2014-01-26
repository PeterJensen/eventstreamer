<?php
// --------------------
// Author: Peter Jensen
// --------------------

class CConfig {
  const EVENTS_DIR = "events";
}

// Client JavaScript data types

class CGetParamKeys {
  const action    = "action";
  const eventName = "eventName";
  const userName  = "userName";
  const fileName  = "fileName";
}

class CActions {
  const uploadBlob       = "uploadBlob";
  const uploadBase64     = "uploadBase64";
  const getEventsCloseBy = "getEventsCloseBy";
  const getAllEvents     = "getAllEvents";
  const createEvent      = "createEvent";
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

class CEventDb {

  public static function uploadBlob(&$response, $getParams) {
    $response->fail("saveBlob not implemented yet");
  }
  
  public static function uploadBase64(&$response, $getParams) {
    $response->fail("saveBase64 not implemented yet");
  }
  
  public static function getEventsCloseBy(&$response, $getParams) {
    $response->fail("getEventsCloseBy not implemented yet");
  }

  public static function getAllEvents(&$response, $getParams) {
    $response->fail("getAllEvents not implemented yet");
  }

  public static function createEvent(&$response, $getParams) {
    if ($getParams->userName === null) {
      $response->fail("userName not specified");
      return;
    }
    $postParams = new CPostParamValues();
    if ($postParams->payload === null) {
      $response->fail("payload not specified");
      return;
    }
    $requestPayload  = json_decode($postParams->payload);
    $responsePayload = $requestPayload;
    $response->success($responsePayload);
  }
}

function main() {
  $getParams = new CGetParamValues();
  $response  = new CResponse();
  
  switch ($getParams->action) {
    case CActions::uploadBlob:
      CEventDb::uploadBlob($response, $getParams);
      break;
    case CActions::uploadBase64:
      CEventDb::uploadBase64($response, $getParams);
      break;
    case CActions::getEventsCloseBy:
      CEventDb::getEventsCloseBy($response, $getParams);
      break;
    case CActions::getAllEvents:
      CEventDb::getAllEvents($response, $getParams);
      break;
    case CActions::createEvent:
      CEventDb::createEvent($response, $getParams);
      break;
    default:
      $response->fail("Unknown action: $getParams->action");
      break;
  }
  echo $response->toJson();
}

main();
?>
