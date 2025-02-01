<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <title>Mit CV</title>
    <link rel="stylesheet" href="Style.css">
    <link rel="stylesheet" href="all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="print-area">
    <div class="header">
        <div class="header-text">
            <h1>Jeppe Nielsen</h1>
            <p>Automation Developer</p>
        </div>
    </div>
    <div class="content">
        <div class="left-area">
            <img src="img/pic.jpg">

            <?php
            // 1) Læs CSV-filen
            $filnavn = __DIR__ . '/bankaktier.csv';
            if (!file_exists($filnavn)) {
                die("Filen '$filnavn' findes ikke. Kør først dit Node-script med minut-data.");
            }

            $linjer = file($filnavn);
            // Forventet CSV-header: ["ticker","datetime","close"]
            $csvLinjer = array_map(
                fn($linje) => str_getcsv($linje, ',', '"', '\\'),
                $linjer
            );
            ?>

            &nbsp;
            <h2>Bankaktier dagligt hentet med Javascript</h2>

            <div id="chart-container">
                <div id="aktieChart"></div>
            </div>

            <!-- Highcharts Stock scripts -->
            <script src="https://code.highcharts.com/stock/highstock.js"></script>
            <script src="https://code.highcharts.com/stock/modules/data.js"></script>
            <script src="https://code.highcharts.com/stock/modules/exporting.js"></script>

            <script>
            /**
             * Læs CSV til JavaScript-array (minus overskrift).
             * CSV er f.eks.: ticker, datetime, close
             */
            const csvData = <?php
                $udenHeader = array_slice($csvLinjer, 1);

                // Helper-funktion til at tjekke om "kl. 09 <= time < 17" i DK
                function erMellem9og17($isoDatetime) {
                    // Lav en DateTime i Europe/Copenhagen for at tjekke timen
                    $dt = new DateTime($isoDatetime, new DateTimeZone('UTC'));
                    $dt->setTimeZone(new DateTimeZone('Europe/Copenhagen'));
                    $hour = (int)$dt->format('G'); // timen i 0-23
                    return ($hour >= 9 && $hour < 17);
                }

                // Byg array af [timestampMs, close, ticker]
                // "datetime" antages at være ISO8601-lignende streng, fx "2025-02-01T09:15:00Z".
                $arrData = [];
                foreach ($udenHeader as $row) {
                    if (count($row) < 3) continue;
                    [$ticker, $datetime, $close] = $row;
                    if (!$ticker || !$datetime) continue;

                    if ($close === 'null' || $close === '') {
                        continue;
                    }
                    // Filtrér væk hvis kl. IKKE 09–17
                    if (!erMellem9og17($datetime)) {
                        continue;
                    }
                    // Konvertér close (str -> float)
                    $c = (float)$close;

                    // Lav Unix-timestamp i millisekunder (Highcharts Stock bruger ms)
                    $t = strtotime($datetime) * 1000;

                    // Tilføj i array
                    $arrData[] = [$t, $c, $ticker];
                }

                echo json_encode($arrData, JSON_NUMERIC_CHECK);
            ?>;

            console.log("csvData (raw):", csvData);

            /**
             * csvData er nu fx: [ [timestampMs, 217.6, "DANSKE.CO"], [ts, close, "SYDB.CO"], ... ]
             * Vi splitter pr. ticker for at få en serie for hver aktie.
             */
            const dataPerTicker = {};
            csvData.forEach(point => {
              const [ts, kurs, ticker] = point;
              if (!dataPerTicker[ticker]) {
                dataPerTicker[ticker] = [];
              }
              dataPerTicker[ticker].push([ts, kurs]);
            });

            // Brug dette map-objekt til at vise pænere navne:
            const tickerMap = {
              "DANSKE.CO": "Danske Bank",
              "JYSK.CO":   "Jyske Bank",
              "SYDB.CO":   "Sydbank"
            };

            // Lav en Highcharts-serie pr. ticker, men brug pæne navne
            const series = Object.keys(dataPerTicker).map(ticker => {
              const niceName = tickerMap[ticker] || ticker; // fallback
              return {
                name: niceName,
                data: dataPerTicker[ticker],
                tooltip: { valueDecimals: 2 },
                marker: {
                  symbol: 'circle'
                }
              };
            });

            console.log("series:", series);

            /**
             * 3) Initialiser Highcharts Stock
             */
            Highcharts.stockChart('aktieChart', {
              rangeSelector: {
                selected: 1 // zoom-niveau
              },

              title: {
                text: 'Aktiekurser'
              },


              // xAxis -> ordinal: true => skip perioder uden data (fx aften/nat)
              xAxis: {
                type: 'datetime',
                ordinal: true
              },

                // Tilføj yAxis med min og max
  yAxis: {
    min: 120,
    max: 600,
    // Du kan evt. tilføje tickInterval for at styre step
    tickInterval: 50,
    title: {
      text: 'Kurs'
    }
  },

              // Aktiver legend med farveprik i bunden
              legend: {
                enabled: true,
                align: 'center',
                verticalAlign: 'bottom',
                layout: 'horizontal'
              },

              // Sæt standard symbol til cirkel (både i graf og legend)
              plotOptions: {
                series: {
                  marker: {
                    symbol: 'circle'
                  }
                }
              },

              series: series
            });
            </script>

            <div class="contact">
                <h4>KONTAKT</h4>
                <h5>Telefon</h5>
                <p>30123790</p>
                <h5>E-mail</h5>
                <p>Jeppe-nielsen-10@hotmail.com</p>
                <h5>Adresse</h5>
                <p>Ordrupvej 14, st. tv. 2920 Charlottenlund</p>
            </div>

            <div class="Kompetencer">
                <h2>Kompetencer</h2>
                <div class="bars">
                    <div class="bar">
                        <p>Investering</p>
                        <span></span>
                    </div>
                    <div class="bar">
                        <p>IT</p>
                        <span></span>
                    </div>
                    <div class="bar">
                        <p>Administration</p>
                        <span></span>
                    </div>
                    <div class="bar">
                        <p>Kommunikation</p>
                        <span></span>
                    </div>
                    <div class="bar">
                        <p>Finans</p>
                        <span></span>
                    </div>
                    <div class="bar">
                        <p>Vindermentalitet</p>
                        <span></span>
                    </div>
                    <div class="bar">
                        <p>Bolig</p>
                        <span></span>
                    </div>
                    <div class="bar">
                        <p>Realkredit</p>
                        <span></span>
                    </div>
                </div>
                <div class="fritid">
                    <h4>Fritid</h4>
                    <h5>Sport og konkurrence</h5>
                    <h5>Økonomi og investering</h5>
                    <h5>Træning</h5>
                </div>
                <div class="follow">
                    <h2>Find mig på LinkedIn</h2>
                    <h4>LinkedIn</h4>
                    <a target="_blank" href="https://www.linkedin.com/in/jeppe-nielsen-8899b6115/"><img class="linkedin" src="https://store-images.s-microsoft.com/image/apps.1719.9007199266245564.44dc7699-748d-4c34-ba5e-d04eb48f7960.abf46174-2d32-4f53-a6cd-644d5b2be452"></img></a>
                </div>
            </div>
        </div> <!-- /left-area -->

        <div class="right-area">
            <div class="Om mig">
                <div class="linje">
                    <i class="fa-solid fa-address-card"></i>
                    <h2>Om mig</h2>
                </div>
                <p>Jeg har arbejdet i den finansielle branche i over 6 år med rådgivning, salg og realkredit. I denne tid har jeg været et vigtigt medlem af de forskellige teams og bidraget til, at vi nåede vores målsætninger og udfordringer i spidsbelastningsperioder herunder især ved sammenlægning af Handelsbanken/Jyske Bank og i konverteringsbølger.</p>
                <div class="work">
                    <div class="linje">
                        <i class="fa-solid fa-suitcase"></i>
                        <h2>Arbejdserfaring</h2>
                    </div>
                    <div class="work-group">
                        <h3>Mortgage officer</h3>
                        <h4>Nordea Kredit</h4>
                        <div class="årgang">2023-</div>
                        <p>Udarbejdelse og administration af realkredit i Nordea koncernen i 
                            datterselskabet Nordea Kredit. Gældsovertagelse af aktive realkreditlån i forbindelse med skilsmisser, 
                            familiehandler, dødsfald m.m. Kommunikation af realkreditregler med alle interessenter i Nordea
                            koncernen. Forbedring af interne processer som sikrer kvalitet og hastighed hos 
                            Nordea Kredit.</p>
                    </div>
                    <div class="work">
                        <div class="work-group">
                            <h3>Freelance håndboldskribent</h3>
                            <h4>Hbold.dk og Europamester.dk</h4>
                            <div class="årgang">2024-</div>
                            <p>Udarbejdelse af håndboldnyheder og artikler fra ind- og udland. Udarbejdelse af analyser, kampreferater og karakterbøger af spillere og håndboldkampe. </p>
                        </div>
                        <div class="work">
                            <div class="work-group">
                                <h3>Bankrådgiver</h3>
                                <h4>Jyske Bank og Handelsbanken</h4>
                                <div class="årgang">2023</div>
                                <p>Rådgivning af egne kunder i portefølje med private kunder og 
                                    selvstændige samt mindre erhverv. Rådgivning i daglig økonomi, kreditter, bolig, bil, pension, investering 
                                    og pension. Administration af bankens processer i forhold til udarbejdelse af lån, 
                                    kreditter, investering, pension m.m.</p>
                            </div>
                            <div class="work">
                                <div class="work-group">
                                    <h3>Bankrådgivertrainee</h3>
                                    <h4>Jyske Bank og Handelsbanken</h4>
                                    <div class="årgang">2021-2023</div>
                                    <p>Overtagelse af afgående rådgivers kundeportefølje med private 
                                        kunder og selvstændige samt mindre erhverv. Rådgivning i daglig økonomi, kreditter, bolig, bil, pension, investering 
                                        og pension. Administration af bankens processer i forhold til udarbejdelse af lån, 
                                        kreditter, investering, pension m.m. Bestået eksamener i Skat, Kredit, Bolig, Investering og Pension.</p>
                                </div>
                                <div class="work">
                                    <div class="work-group">
                                        <h3>Salg og vurdering</h3>
                                        <h4>Lokalbolig Søborg</h4>
                                        <div class="årgang">2021</div>
                                        <p>Salg af boliger i Søborg, Bagsværd og Dyssegård. Markedsføring af boliger. Relevant lovgivning - Lov om formidling af fast ejendom.</p>
                                    </div>
                                    <div class="work">
                                        <div class="work-group">
                                            <h3>Kundekonsulent og Ambassadør</h3>
                                            <h4>Nykredit</h4>
                                            <div class="årgang">2018-2021</div>
                                            <p>BackOffice/administration. Opstart af ny afdeling. Udarbejdelse af realkreditlån, tjek af compliance samt opfølgning på 
                                                underskrifter. Udnævnt til ambassadør med ansvar for kvalitetskontrol.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- /work -->

                <div class="education">
                    <div class="linje">
                        <i class="fa-solid fa-book"></i>
                        <h2>Uddannelse</h2>
                    </div>
                    <div class="edu-group">
                        <h4>Finansiel rådgivning, CPHbusiness</h4>
                        <div class="årgang">2021-2023</div>
                        <p>Uddannelse til bankrådgiver herunder Skat, Kredit, Bolig, Investering og Pension.</p>
                    </div>
                    <div class="edu-group">
                        <h4>CPHbusiness</h4>
                        <div class="årgang">2017-2021</div>
                        <p>Professionsbachelor i Finans</p>
                    </div>
                    <div class="edu-group">
                        <h4>HHX Ringsted</h4>
                        <div class="årgang">2014-2017</div>
                        <p>International økonomi, Virksomhedsøkonomi, Afsætning og Idræt. ”IKA Elev” - morgentræning i forbindelse med håndboldakademi i Ringsted.</p>
                    </div>
                    <div class="frivilligt">
                        <div class="linje">
                            <i class="fa-solid fa-handshake-angle"></i>
                            <h2>Frivilligt arbejde</h2>
                        </div>
                        <div class="frivilligt">
                            <h3>Håndboldtræner</h3>
                            <h4>DHF Camp</h4>
                            <p>Træning af børne-håndboldspillere til håndboldcamp i Ringsted i samarbejde med TMS Ringsted.</p>
                        </div>
                    </div> <!-- /frivilligt -->
                </div> <!-- /education -->

            </div> <!-- /right-area -->
        </div> <!-- /content -->
    </div> <!-- /print-area -->

<script src="all.min.js"></script>
<!-- <script src="script.js"></script> -->
</body>
</html>
