<?php

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Cerca Titolo Azionario</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .search-box {
            margin-bottom: 30px;
        }
        input[type="text"] {
            padding: 10px;
            width: 300px;
            font-size: 18px;
        }
        button {
            padding: 10px 15px;
            font-size: 18px;
            cursor: pointer;
        }
        #chart-container {
            width: 90%;
            max-width: 900px;
        }
        #loading {
            margin-top: 20px;
            font-style: italic;
        }
        .welcome {
            font-size: 24px;
            margin-bottom: 20px;
            opacity: 0;
            animation: fadeIn 2s ease-in-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

    </style>
</head>
<body>
<div class="welcome">Benvenuto nel Beniamietor</div>

<div class="search-box">
    <input type="text" id="stockInput" placeholder="Cerca un titolo azionario">
    <button onclick="fetchStock()">Cerca</button>
</div>

<div id="loading"></div>

<div id="chart-container">
    <canvas id="stockChart"></canvas>
</div>

<script>
    async function fetchStock() {
        const stock = document.getElementById("stockInput").value.trim().toUpperCase();
        const loadingEl = document.getElementById("loading");
        loadingEl.innerText = "Caricamento in corso...";

        if (!stock) {
            loadingEl.innerText = "";
            return;
        }

        const apiKey = 'WGCKU0LSHKEIHUJG';
        const url = `https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=${stock}&apikey=${apiKey}`;

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.Note) {
                loadingEl.innerText = "Limite di richieste superato. Riprova più tardi.";
                return;
            }

            const timeSeries = data["Time Series (Daily)"];
            if (!timeSeries) {
                loadingEl.innerText = "Simbolo non valido o errore nel recupero dati.";
                return;
            }

            const labels = Object.keys(timeSeries).slice(0, 30).reverse();
            const prices = labels.map(date => parseFloat(timeSeries[date]["4. close"]));

            const ctx = document.getElementById("stockChart").getContext("2d");
            if (window.stockChartInstance) {
                window.stockChartInstance.destroy();
            }
            window.stockChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: `Prezzo ${stock}`,
                        data: prices,
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
            loadingEl.innerText = "";
        } catch (error) {
            loadingEl.innerText = "Errore nella richiesta API.";
            console.error(error);
        }
    }
</script>
</body>
</html>
