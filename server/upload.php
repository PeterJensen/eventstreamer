<?php

class CResponse {
  public $post;
  public $get;
  public $files;
  public $length;
  public function toJson() {
    return json_encode ($this);
  }
}

function main() {
  $response = new CResponse ();
  if (isset ($_GET["filename"])) {
    $filename = $_GET["filename"];
    $filedata = file_get_contents("php://input");
  }
  else {
    $filename = $_POST["filename"];
    $filedata   = base64_decode ($_POST["filedata"]);
  }
  file_put_contents("data/" . $filename, $filedata);
  $response->length = strlen ($filedata);
    
  echo $response->toJson();
}

main();
?>