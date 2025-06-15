<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Groups</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styling for Talk Groups - One Box Per Line */
        .talkgroup-container {
            display: flex;
            flex-direction: column; /* Arrange boxes vertically */
            gap: 15px;
            padding: 20px;
            align-items: center; /* Center the boxes on the page */
        }

        .talkgroup-box {
            background-color: var(--card-bg-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            width: 90%; /* Use 90% of the page width */
            max-width: 600px; /* Limit the width for better readability */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }

        .talkgroup-box:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
        }

        .talkgroup-box h2 {
            font-size: 1.5em;
            color: var(--highlight-color);
            margin: 0 0 10px;
        }

        .talkgroup-box p {
            font-size: 1em;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <img src="logo.png" alt="SVX Logo" class="logo">
        <div class="header-links">
            <a href="index.php" class="info-link">Dashboard</a>
        </div>
    </header>

    <!-- Main Content Section -->
    <!-- Main Content Section -->
    <main>
        <h1>Talk Groups</h1>
        <div class="talkgroup-container">
            <?php
            require_once 'tgdb.php';
            foreach ($tgdb_array as $tg_number => $tg_description) {
                // Skip the 'Any TG' entry
                if ($tg_number !== 'Any TG') {
                    echo "<div class='talkgroup-box'>";
                    echo "<h2>{$tg_number}</h2>";
                    echo "<p>{$tg_description}</p>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </main>

<!--    <main>
        <h1>Talk Groups</h1>
        <div class="talkgroup-container">
            <div class="talkgroup-box"><h2>91</h2><p>International</p></div><div class="talkgroup-box"><h2>204</h2><p>Netherlands</p></div><div class="talkgroup-box"><h2>206</h2><p>Belgium</p></div><div class="talkgroup-box"><h2>208</h2><p>France</p></div><div class="talkgroup-box"><h2>235</h2><p>UK Wide</p></div><div class="talkgroup-box"><h2>240</h2><p>Sweden Wide</p></div><div class="talkgroup-box"><h2>260</h2><p>Poland</p></div><div class="talkgroup-box"><h2>262</h2><p>Germany</p></div><div class="talkgroup-box"><h2>2350</h2><p>Chat</p></div><div class="talkgroup-box"><h2>2600</h2><p>Poland - Chat Group</p></div><div class="talkgroup-box"><h2>23450</h2><p>Yorkshire Net UK</p></div><div class="talkgroup-box"><h2>23510</h2><p>South East Kent Sussex</p></div><div class="talkgroup-box"><h2>23511</h2><p>South East (Beds Essex Norf Suff)</p></div><div class="talkgroup-box"><h2>23520</h2><p>North West (Lancashire/Cumbria)</p></div><div class="talkgroup-box"><h2>23525</h2><p>M0XFN Freedom</p></div><div class="talkgroup-box"><h2>23526</h2><p>M0XHN HubNet</p></div><div class="talkgroup-box"><h2>23530</h2><p>Yorkshire-Humberside</p></div><div class="talkgroup-box"><h2>23540</h2><p>Wales</p></div><div class="talkgroup-box"><h2>23550</h2><p>Scotland</p></div><div class="talkgroup-box"><h2>23556</h2><p>DVSPh Multimode</p></div><div class="talkgroup-box"><h2>23560</h2><p>North East (Durham Newcastle Northumberland)</p></div><div class="talkgroup-box"><h2>23561</h2><p>DMR Talk Group 23561 When on.</p></div><div class="talkgroup-box"><h2>23570</h2><p>Northern Ireland</p></div><div class="talkgroup-box"><h2>23580</h2><p>West Midlands inc. Birmingham</p></div><div class="talkgroup-box"><h2>23590</h2><p>East Midlands (Warks Notts Derby Oxon Lincs)</p></div>        </div>
    </main> -->

    <!-- Footer Section -->
    <footer>
        <p>&copy; 2024 YorkshireSVX. All rights reserved.</p>
        <p>Powered by SvxLink | Contact: <a href="mailto:support@svxlink.uk">support@svxlink.uk</a></p>
    </footer>
</body>
</html>
