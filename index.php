<?php
$host = "http://note-swiper.f5.si";
$port = "25500";
$ecode = "";
$emessage = "";
$timeout = 200;
$con = @fsockopen($host, $port,$ecode,$emessage,$timeout);

$isopen = 0;
if (is_resource($con)) {$isopen = 1;} elseif (!is_resource($con)) {$isopen = 2;} elseif ($con == false) {$isopen = 2;} else {$isopen = 0;}
function ServerStatus() {
    $res = "";
    switch($GLOBALS['isopen']) {
        case 0:
            $res = "Unknown";
            break;
        case 1:
            $res = "Online";
            break;
        case 2:
            $res = "Offline";
            break;
        case 3:
            $res = "Returned not processing";
            break;
    }
    return $res;
}

if (is_resource($con)) fclose($con);
if (isset($_POST["mcid"]) && isset($_POST["code"])){
    $mcauths_dir = "./mcauths/";
    $mcid = $_POST["mcid"];
    $code = $_POST["code"];
    $uuid = @file_get_contents("https://api.mojang.com/users/profiles/minecraft/".$mcid);
    if ($uuid === false){
        http_response_code(400);
        exit("Failed to get minecraft user's UUID");
    }
    $res = json_decode($uuid, true);
    $uuid = $res["id"];
    $mcid = $res["name"];
    if (!is_file($mcauths_dir.$uuid)){
        http_response_code(400);
        exit("No authentication card found in database!");
    }
    $auth = json_decode(file_get_contents($mcauths_dir.$uuid), true);
    if ($auth["code"] !== $code){
        http_response_code(400);
        exit("Failed to authentication!");
    }
    if ($auth["expire"] < time()){
        http_response_code(400);
        unlink($mcauths_dir.$uuid);
        exit("This authentication card already expired!");
    }
    unlink($mcauths_dir.$uuid);
    exit("Authentication success!<br>UUID: ".$uuid."<br>NAME: ".$mcid);
}
?>
<div>
    <p>Server status: <?=ServerStatus()?></p>
</div>
<form method="POST">
    MCID: <input name="mcid"><br>
    CODE: <input name="code"><br>
    <input type="submit">
</form>
