<?php
$data = json_decode(file_get_contents("php://input"), true);
$username = $data["username"];
$usersFile = "users.json";

$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

if (!isset($users[$username])) {
    // New user — set start date
    $data["startDate"] = date("Y-m-d");
    $data["currentDay"] = 1;
    $data["lastChallengeDate"] = date("Y-m-d");
    $data["level"] = 1;
    $data["strikes"] = 0;
} else {
    // Returning user — keep original start date
    $existing = $users[$username];
    $data["startDate"] = $existing["startDate"];
    $data["currentDay"] = $existing["currentDay"];
    $data["lastChallengeDate"] = $existing["lastChallengeDate"];
    $data["level"] = $existing["level"];
    $data["strikes"] = $existing["strikes"];
}

// Save updated data
$users[$username] = $data;
file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
?>
