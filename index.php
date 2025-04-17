<?php
// smoking, drinking too much coffee, drinking alchohol mostly beer, doomscrolling, procrastination, not doing daily activities like excersing and walking

// swimming, eating max 1800 calories a day, walking at least 45 minutes a day

// Load saved user data from JSON (if it exists)
$userData = [
    "badHabits" => "",
    "goodHabits" => "",
    "age" => "",
    "weight" => "",
    "height" => "",
    "workType" => "",
    "level" => 1,
    "strikes" => 0,
    "startDate" => "",
    "currentDay" => 0,
    "lastCompletedDate" => ""
];

if (file_exists("userdata.json")) {
    $savedData = json_decode(file_get_contents("userdata.json"), true);
    foreach ($savedData as $key => $value) {
        if (array_key_exists($key, $userData)) {
            $userData[$key] = $value;
        }
    }
}

// If form is submitted, update user data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $now = date("Y-m-d");
    $isFirstTime = empty($userData["startDate"]);

    $userData = [
        "badHabits" => $_POST["badHabits"],
        "goodHabits" => $_POST["goodHabits"],
        "age" => $_POST["age"],
        "weight" => $_POST["weight"],
        "height" => $_POST["height"],
        "workType" => $_POST["workType"],
        "level" => $_POST["level"],
        "strikes" => $_POST["strikes"],
        "startDate" => $isFirstTime ? $now : $userData["startDate"],
        "currentDay" => $userData["currentDay"] ?? 0,
        "lastCompletedDate" => $userData["lastCompletedDate"] ?? ""
    ];

    file_put_contents("userdata.json", json_encode($userData));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Discipline Game</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    color: #fff;
    font-family: 'Arial', sans-serif;
}
.container {
    max-width: 600px;
    margin: auto;
    padding: 20px;
}
.card {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}
.btn-custom {
    background: #e94560;
    color: white;
    border: none;
    transition: 0.3s ease-in-out;
}
#notice-completion {
    margin-top: 20px;
    font-weight: 700;
    color: red;
}
.btn-custom:hover {
    background: #ff6363;
}
.btn-secondary {
    background: rgba(255, 255, 255, 0.3);
    border: none;
}
input, select {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
}
input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}
#challenge {
    color: white;
}
</style>
</head>
<body>
    <?php var_dump($userData); ?>
<div class="container text-center mt-5">
    <div class="card">
        <h2 class="mb-4">🔥 Discipline Game 🔥</h2>
        <p class="fw-bold">Level: <span id="level"><?php echo $userData["level"]; ?></span></p>
        <p class="fw-bold">Strikes: <span id="strikes"><?php echo $userData["strikes"]; ?></span></p>

        <?php if($userData["currentDay"] > 0) {
            echo "<p>Days passed: " . ($userData["currentDay"]) . "</p>";
        } ?>
        <form id="userForm" method="POST">
            <div class="mb-3">
                <input type="text"  class="form-control" id="username" name="username" placeholder="Your Username" required>
            </div>
            <div class="mb-3">
                <textarea class="form-control" id="badHabits" name="badHabits" placeholder="Bad Habits" value="<?php echo htmlspecialchars($userData["badHabits"]); ?>" required><?php echo htmlspecialchars($userData["badHabits"]); ?></textarea>
            </div>
            <div class="mb-3">
                <textarea class="form-control" id="goodHabits" name="goodHabits" placeholder="Good Habits" value="<?php echo htmlspecialchars($userData["goodHabits"]); ?>" required><?php echo htmlspecialchars($userData["goodHabits"]); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <input type="number" class="form-control" id="age" name="age" placeholder="Age" value="<?php echo htmlspecialchars($userData["age"]); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <input type="number" class="form-control" id="weight" name="weight" placeholder="Weight (kg)" value="<?php echo htmlspecialchars($userData["weight"]); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <input type="number" class="form-control" id="height" name="height" placeholder="Height (cm)" value="<?php echo htmlspecialchars($userData["height"]); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" id="workType" name="workType" placeholder="Work Type" value="<?php echo htmlspecialchars($userData["workType"]); ?>" required>
            </div>
            <input type="hidden" id="levelInput" name="level" value="<?php echo $userData["level"]; ?>">
            <input type="hidden" id="strikesInput" name="strikes" value="<?php echo $userData["strikes"]; ?>">
            <button type="submit" class="btn btn-custom w-100">Save & Get Challenge</button>
        </form>
        <p id="notice-completion"></p>
        <p id="challenge" class="mt-3"></p>
        <button id="completeChallenge" class="btn btn-success w-100 mt-2">✅ Complete Challenge</button>
        <button id="missedChallenge" class="btn btn-secondary w-100 mt-2">⚠ Missed Challenge</button>
    </div>
</div>
<script>
const OPENAI_API_KEY = "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";

const todayStr = new Date().toISOString().split('T')[0];

window.onload = function () {
    let savedData = JSON.parse(localStorage.getItem("userData"));
    let savedChallenge = localStorage.getItem("dailyChallenge");
    let lastCompleted = localStorage.getItem("lastCompletedDate");
console.log(todayStr);

    if(lastCompleted == todayStr) {
        document.getElementById("completeChallenge").classList.add("d-none");
        document.getElementById("missedChallenge").classList.add("d-none");

        document.getElementById("notice-completion").innerText = "Ready to be completed tomorrow:";
    }

    if (savedData) {
        document.getElementById("level").innerText = savedData.level;
    }
    if (savedChallenge && lastCompleted == todayStr) {
        document.getElementById("challenge").innerText = savedChallenge;
    } else {
        document.getElementById("completeChallenge").classList.remove("d-none");
    }
};

async function generateAIChallenge(user) {
    const prompt = `
    I am a discipline gatekeeper game. The user is:
    - Age: ${user.age}
    - Weight: ${user.weight}kg
    - Height: ${user.height}cm
    - Work type: ${user.workType}
    - Wants to remove bad habits: ${user.badHabits.join(", ")}
    - Wants to build good habits: ${user.goodHabits.join(", ")}

    Based on this information, give the user a **single** daily challenge that will help them improve. The challenge should be practical and achievable within a day. Combine bad habits to every day remove at least two of them at daily challange and slide in one-two good habits on daily challange. For the rest be creative!
    `;

    try {
        const response = await fetch("https://api.openai.com/v1/chat/completions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${OPENAI_API_KEY}`
            },
            body: JSON.stringify({
                model: "gpt-4o-mini",
                messages: [{ role: "system", content: prompt }]
            })
        });

        const data = await response.json();
        const challenge = data.choices[0].message.content.trim();
        document.getElementById("challenge").innerText = challenge;
        localStorage.setItem("dailyChallenge", challenge);
        localStorage.setItem("lastCompletedDate", todayStr);
    } catch (error) {
        console.error("Error fetching AI challenge:", error);
    }
}

document.getElementById("userForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    let userData = {
        username: document.getElementById("username").value,
        badHabits: document.getElementById("badHabits").value.split(","),
        goodHabits: document.getElementById("goodHabits").value.split(","),
        age: document.getElementById("age").value,
        weight: document.getElementById("weight").value,
        height: document.getElementById("height").value,
        workType: document.getElementById("workType").value,
        level: localStorage.getItem("level") || 1,
        strikes: localStorage.getItem("strikes") || 0
    };
    localStorage.setItem("userData", JSON.stringify(userData));
    await saveUserData(userData);
    await generateAIChallenge(userData);
});

document.getElementById("completeChallenge").addEventListener("click", async function () {
    let userData = JSON.parse(localStorage.getItem("userData"));
    if (userData.strikes > 0) userData.strikes--;
    userData.level++;
    localStorage.setItem("userData", JSON.stringify(userData));
    localStorage.setItem("lastCompletedDate", todayStr);
    localStorage.setItem("level", userData.level);
    localStorage.setItem("level", userData.level);
    document.getElementById("level").innerText = userData.level;
    this.classList.add("d-none");
    await generateAIChallenge(userData);
    await saveUserData(userData);
});

async function saveUserData(user) {
    try {
        await fetch("save_user.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(user)
        });
    } catch (error) {
        console.error("Error saving user data:", error);
    }
}
</script>
</body>
</html>
