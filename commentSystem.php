<?php

class CommentSystem {
  static function init() {
    // CSS
    print "
        <head>
            <link rel='stylesheet' href='commentSystem.css'>
        </head>
        ";


    // Figure out URL of this location
    $fileName = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$fileName";

    $r = isset($_POST['reply']) ? $_POST['reply'] : "";

    echo "<script>console.log('$r')</script>";

    // Prints comment input
    print "
            <h1>Comments</h1>
            <div id='comment-input-container'>
                <label for='comName'>Name:</label>
                <form method='POST' id='comment-form' action='addComment.php' accept-charset='UTF-8'>
                    <input name='comName' placeholder='Name' value='Anonymous'></input>
                    <input name='reply' placeholder='Reply to ID' value='$r'></input>
                    <br>
                    <textarea rows=7 name='comText' placeholder='Write your comment here...'></textarea>
                    <br>
                    <input name='returnURL' type='hidden' value =" . $url . "></input>
                    <input name='fileName' type='hidden' value =" . $fileName . "></input>
                    <input type='submit' value='send'></input>
                </form>
            </div>
        ";

    $filePath = './comments/' . str_replace("/", "-", $fileName) . '-comments.json';


    // If there is a comment JSON, print out all the comments from the JSON
    if (file_exists($filePath)) {
      $json = json_decode(file_get_contents($filePath));
      foreach ($json as $i) {
        $wroteOrReplied = $i->replyToID ? "replied to #" . $i->replyToID . ":" : "wrote:";
        if ($i->replyToID) {
          $originalComment = Comment::getItemByID($i->replyToID);
          print "
                    <div id='comment-input-container'>
                        <span id=comment-name>$originalComment->name </span><span id=comment-id>($originalComment->date) (#$originalComment->id)</span><span id=comment-classic> originally said:</span>
                        <p id=comment-text>$originalComment->comment</p>
                    </div>
                ";
        }
        print "
                    <div id='comment-input-container'>
                        <span id=comment-name>$i->name </span><span id=comment-id>($i->date) (#$i->id)</span><span id=comment-classic> $wroteOrReplied</span>
                        <p id=comment-text>$i->comment</p>
                        <form method='POST' id='comment-form' action='./'>
                            <input type='hidden' name='reply' value='$i->id'></input>
                            <input id='comment-reply-button' type='submit' value='Reply'></input>
                        </form>
                        <hr>
                    </div>
                ";
      }
    }
  }
}

class Comment {
  private int $id;
  private string $date;
  private ?int $replyToID;
  private string $name;
  private string $comment;

  function __construct(int $id, string $date, ?int $replyToID, string $name, string $comment) {
    $this->id = $id;
    $this->date = $date;
    $this->replyToID = $replyToID;
    $this->name = $name;
    $this->comment = $comment;
  }

  function makeJSON() {
    $arr = array(
      'id' => $this->id,
      'date' => $this->date,
      'replyToID' => $this->replyToID,
      'name' => $this->name,
      'comment' => $this->comment
    );
    $json = json_encode($arr, JSON_UNESCAPED_UNICODE);
    $json = stripslashes($json);
    return $json;
  }

  static function getItemByID(int $id) {
    $fileName = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $filePath = './comments/' . str_replace("/", "-", $fileName) . '-comments.json';
    $json = json_decode(file_get_contents($filePath));
    foreach ($json as $i) {
      if ($i->id == $id) {
        return $i;
      }
    }
    return null;
  }
}
