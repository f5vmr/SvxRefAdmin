<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Node Information</title>
    <link rel="stylesheet" href="nodes.css">
</head>
<body>
    <header>
        <img src="logo.png" alt="Logo" onclick="window.location='index.php'">
        <a href="index.php" class="back-button">Dashboard</a>
    </header>
    <h1>Node Information</h1>
    <div class="container" id="nodes-container"></div>

    <footer>
        <p>&copy; 2024 YorkshireSVX. All rights reserved.</p>
        <p>Powered by SvxLink | Contact: <a href="mailto:support@svxlink.uk">support@svxlink.uk</a></p>

    </footer>

    <script>
        async function fetchData() {
            try {
                const response = await fetch('status.php'); // Fetch data from status.php
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                updateNodes(data.nodes); // Pass the nodes to the update function
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }

        function updateNodes(nodes) {
            const nodesContainer = document.getElementById('nodes-container');
            nodesContainer.innerHTML = ''; // Clear previous content

            for (const [key, node] of Object.entries(nodes)) {
                const nodeDiv = document.createElement('div');
                nodeDiv.className = 'node' + (node.isTalker ? ' talking' : '');

                nodeDiv.innerHTML = `
                    <h2>${key}</h2>
                    <p><strong>Is Talking:</strong> ${node.isTalker}</p>
                    <p><strong>Monitored TGs:</strong> ${node.monitoredTGs.join(', ')}</p>
                    <p><strong>Software:</strong> ${node.sw} v${node.swVer}</p>
                    ${node.nodeLocation ? `<p><strong>Location:</strong> ${node.nodeLocation}</p>` : ''}
                `;

                nodesContainer.appendChild(nodeDiv);
            }
        }

        // Fetch data on load and refresh every 5 seconds
        fetchData();
        setInterval(fetchData, 5000); // Refresh every 5 seconds
    </script>
</body>
</html>
