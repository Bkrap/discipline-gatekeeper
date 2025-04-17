<?php
$username = $_POST["username"] ?? "";
$usersFile = "users.json";
$response = [];

if (!$username) {
    echo json_encode(["error" => "Username is required"]);
    exit;
}

$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

if (!isset($users[$username])) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

$user = $users[$username];

// Time checks
$today = new DateTime();
$lastChallengeDate = new DateTime($user["lastChallengeDate"]);
$interval = $today->diff($lastChallengeDate)->days;

if ($interval === 1) {
    // ✅ Completed yesterday → move to next day
    $user["currentDay"] += 1;
    $user["lastChallengeDate"] = $today->format("Y-m-d");
    $user["level"] += 1;
} elseif ($interval > 1) {
    // ❌ Missed a day
    $user["currentDay"] = 0;
    $user["level"] = 0;
    $user["lastChallengeDate"] = $today->format("Y-m-d");
}

// Save updates
$users[$username] = $user;
file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

echo json_encode($user);
?>
