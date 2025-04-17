const express = require("express");
const cors = require("cors");
const fetch = require("node-fetch");

const app = express();
app.use(cors());
app.use(express.json());

const OPENAI_API_KEY = "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";

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
