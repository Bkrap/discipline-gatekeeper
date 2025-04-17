const express = require("express");
const cors = require("cors");
const fetch = require("node-fetch");

const app = express();
app.use(cors());
app.use(express.json());

const OPENAI_API_KEY = env.OPENAI_API_KEY;


app.post("/generate-challenge", async (req, res) => {
    const { prompt } = req.body;
    
    const response = await fetch("https://api.openai.com/v1/chat/completions", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${OPENAI_API_KEY}`
        },
        body: JSON.stringify({ model: "gpt-4", messages: [{ role: "system", content: prompt }] })
    });

    const data = await response.json();
    res.json({ challenge: data.choices[0].message.content.trim() });
});

app.listen(3300, () => console.log("Server running on port 3300"));
