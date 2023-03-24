<?php
include_once "commentSystem.php";


// Saving $_POST vars to local variables
$name = $_POST['comName'];
$replyToID = $_POST['reply'];
$text = $_POST['comText'];
$returnURL = $_POST['returnURL'];
$fileName = $_POST['fileName'];


// creates config file
$configPath = './comments/config.json';
if (!file_exists($configPath)) {
  $json = json_encode(array(
    'id' => 0
  ));
  file_put_contents($configPath, $json);
}
$config = json_decode(file_get_contents($configPath));


// Adds 1 to ID in config
$config->id = $config->id + 1;
file_put_contents($configPath, json_encode($config));


// Constructs Comment object
$comment = new Comment($config->id, date("d.m.Y h:i:s"), $replyToID == "" ? null : $replyToID, $name, $text);
$json = $comment->makeJSON();

mkdir('./comments'); // Watch out, throws error if dir exists

$filePath = './comments/' . str_replace("/", "-", $fileName) . '-comments.json'; // Replaces all slashes with dashes


// appends new comment to a json file
$content = file_get_contents($filePath);
$temp = (array)json_decode($content);
array_push($temp, json_decode($json));
$jsonData = array(stripslashes(json_encode($temp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
file_put_contents($filePath, $jsonData);


// return back (also refreshes the page so the new comment shows up)
header('Location: ' . $returnURL);
