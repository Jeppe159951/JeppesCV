import fs from 'fs';

const tickers = ['DANSKE.CO', 'JYSK.CO', 'SYDB.CO'];

/**
 * Hent 100 dages chart-data, med 30-minutters intervaller.
 */
async function hentChart(ticker) {
  // Parametre:
  //  - range=60d => seneste 60 dages data
  //  - interval=30m => datapunkt hver 30. minut
  const url = `https://query1.finance.yahoo.com/v8/finance/chart/${ticker}?range=60d&interval=30m`;

  try {
    const response = await fetch(url, {
      headers: { 'User-Agent': 'Mozilla/5.0' } // Nogle gange kræves en User-Agent
    });
    if (!response.ok) {
      throw new Error(`HTTP-fejl for ${ticker}: ${response.status}`);
    }

    const data = await response.json();
    if (!data.chart || !data.chart.result?.length) {
      throw new Error(`Mangler chart-data for ${ticker}`);
    }

    const chartData = data.chart.result[0];
    const timestamps = chartData.timestamp;  // array af Unix-sekunder
    const quotes = chartData.indicators?.quote?.[0];

    return {
      ticker,
      timestamps,
      close: quotes?.close ?? []
    };
  } catch (error) {
    console.error(`Fejl ved hentning af data for ${ticker}:`, error);
    return null;
  }
}

/**
 * Hent data for alle bankers tickers i parallel.
 */
async function hentDataForBanker() {
  const results = await Promise.all(tickers.map(hentChart));
  return results.filter(Boolean);
}

/**
 * Helper-funktion:
 * Returnerer true, hvis timestamp (Unix sek.) ligger mellem kl. 09:00 og 17:00
 * dansk tid (Europe/Copenhagen). Ellers false.
 */
function erMellem9og17DanskTid(ts) {
  // Lav Date-objekt i JavaScript (UTC-baseret)
  const dateObj = new Date(ts * 1000);

  // Hent lokal time i 'Europe/Copenhagen'
  const hourStr = dateObj.toLocaleString('da-DK', {
    timeZone: 'Europe/Copenhagen',
    hour12: false,
    hour: '2-digit'
  });
  const hour = parseInt(hourStr, 10);

  return (hour >= 9 && hour < 17);
}

// Kør og skriv til CSV
hentDataForBanker().then((bankData) => {
  if (!bankData || !bankData.length) {
    console.log("Ingen data modtaget, skriver ingen CSV.");
    return;
  }

  const linjer = ["ticker,datetime,close"];

  // Gennemløb al modtaget data
  bankData.forEach(({ ticker, timestamps, close }) => {
    for (let i = 0; i < timestamps.length; i++) {
      const ts = timestamps[i];
      const luk = close[i];

      // Filtrér væk hvis kl er uden for 09:00-17:00
      if (!erMellem9og17DanskTid(ts)) {
        continue; // Spring dette datapunkt over
      }

      // Lav ISO-dato (UTC)
      const isoString = new Date(ts * 1000).toISOString();
      // Eller dansk lokal tid, hvis du foretrækker det:
      // const localDK = new Date(ts * 1000).toLocaleString('da-DK', { timeZone: 'Europe/Copenhagen' });

      linjer.push(`${ticker},${isoString},${luk}`);
    }
  });

  fs.writeFileSync("bankaktier.csv", linjer.join("\n"), "utf8");
  console.log("CSV gemt som bankaktier.csv");
});
