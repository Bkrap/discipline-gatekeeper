const OPENAI_API_KEY = "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";

document.getElementById("userForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    let userData = {
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

    // Send data to PHP for saving
    saveUserData(userData);

    // Fetch AI-generated challenge
    await generateAIChallenge(userData);
});

async function saveUserData(user) {
    try {
        await fetch("save_user.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(user)
        });
    } catch (error) {
        console.error("Error saving user data:", error);
    }
}

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
        document.getElementById("completeChallenge").style.display = "block";

        localStorage.setItem("dailyChallenge", challenge);
        localStorage.setItem("lastChallengeDate", new Date().toISOString().split("T")[0]); // Store challenge date

    } catch (error) {
        console.error("Error fetching AI challenge:", error);
        document.getElementById("challenge").innerText = "Error fetching challenge. Try again.";
    }
}

document.getElementById("completeChallenge").addEventListener("click", function () {
    let userData = JSON.parse(localStorage.getItem("userData"));
    
    // Completing a challenge removes a strike
    if (userData.strikes > 0) {
        userData.strikes--;
    }

    userData.level++;
    localStorage.setItem("userData", JSON.stringify(userData));
    localStorage.setItem("strikes", userData.strikes);

    document.getElementById("level").innerText = `Level: ${userData.level}`;

    alert("Challenge completed! A new challenge will be available tomorrow.");
    this.style.display = "none";

    // Save updated data to PHP
    saveUserData(userData);
});

function checkForMissedChallenge() {
    let lastChallengeDate = localStorage.getItem("lastChallengeDate");
    let today = new Date().toISOString().split("T")[0];

    if (lastChallengeDate && lastChallengeDate !== today) {
        let userData = JSON.parse(localStorage.getItem("userData"));

        userData.strikes++;

        if (userData.strikes >= 3) {
            userData.level = 1;
            userData.strikes = 0;
            alert("You missed too many challenges! Level reset to 1.");
        }

        localStorage.setItem("userData", JSON.stringify(userData));
        localStorage.setItem("strikes", userData.strikes);
        
        saveUserData(userData);
    }
}

window.onload = function () {
    checkForMissedChallenge();

    let savedData = JSON.parse(localStorage.getItem("userData"));
    let savedChallenge = localStorage.getItem("dailyChallenge");

    if (savedData) {
        document.getElementById("level").innerText = `Level: ${savedData.level}`;
    }

    if (savedChallenge) {
        document.getElementById("challenge").innerText = savedChallenge;
        document.getElementById("completeChallenge").style.display = "block";
    }
};
