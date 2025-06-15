<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UK-Wide SVX Node Monitor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <img src="logo.png" alt="SVX Logo" class="logo">
        <div class="header-links">
            <a href="nodes.php" class="info-link">Node Information</a>
            <a href="talkgroups.php" class="info-link">Talk Groups</a>
            <a href="downloads/index.php" class="info-link">Downloads</a>
            <a href="register/index.php" class="join-link" target="_blank">Join SvxLink Reflector</a>            
        </div>
    </header>

    <!-- Main Content Section -->
    <main>
        <h1>UK-Wide SVX Node Monitor</h1>
        <div class="container" id="nodes-container"></div>
    </main>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 2024 YorkshireSVX. All rights reserved.</p>
        <p>Powered by SvxLink | Contact: <a href="mailto:support@svxlink.uk">support@svxlink.uk</a></p>
    </footer>

    <!-- JavaScript Section -->
    <script>
        let activeNodes = {};

        async function fetchData() {
            try {
                const response = await fetch('status.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();

                if (data.nodes) {
                    updateNodes(data.nodes);
                } else {
                    console.error("No 'nodes' key found in the JSON response.");
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }

        function updateNodes(nodes) {
            const nodesContainer = document.getElementById('nodes-container');
            nodesContainer.innerHTML = '';

            for (const [key, node] of Object.entries(nodes)) {
                const isTalking = node.isTalker;

                if (isTalking) {
                    if (!activeNodes[key]) {
                        activeNodes[key] = {
                            startTime: new Date(),
                            timerId: null,
                        };
                    }
                } else {
                    if (activeNodes[key]) {
                        clearInterval(activeNodes[key].timerId);
                        delete activeNodes[key];
                    }
                }

                const nodeDiv = document.createElement('div');
                nodeDiv.className = `node ${isTalking ? 'talking' : ''}`;

                const startTime = activeNodes[key]?.startTime
                    ? activeNodes[key].startTime.toLocaleTimeString()
                    : '';
                const talkTime = activeNodes[key]?.startTime
                    ? Math.floor((new Date() - activeNodes[key].startTime) / 1000)
                    : 0;

                nodeDiv.innerHTML = `
                    <div class="node-summary">
                        <div>
                            <span class="node-name">${key}</span>
                            <span class="node-tgs">Monitored TGs: ${node.monitoredTGs.join(', ')}</span>
                        </div>
                        <div class="node-time">
                            ${isTalking ? `<p><strong>Active TG:</strong> ${node.tg || 'N/A'}</p>` : ''}
                            ${isTalking ? `<p><strong>Start Talk:</strong> ${startTime}</p>` : ''}
                            ${isTalking ? `<p><strong>Talk Time:</strong> <span id="talk-time-${key}">${talkTime} seconds</span></p>` : ''}
                        </div>
                    </div>
                `;
                nodesContainer.appendChild(nodeDiv);

                if (isTalking && !activeNodes[key].timerId) {
                    activeNodes[key].timerId = setInterval(() => {
                        const elapsedTime = Math.floor((new Date() - activeNodes[key].startTime) / 1000);
                        const talkTimeElement = document.getElementById(`talk-time-${key}`);
                        if (talkTimeElement) {
                            talkTimeElement.textContent = `${elapsedTime} seconds`;
                        }
                    }, 1000);
                }
            }
        }

        fetchData();
        setInterval(fetchData, 1000);
    </script>
</body>
</html>
