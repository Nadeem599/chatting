<!DOCTYPE html>
<html>
<head>
    <title>WebSocket Test</title>
</head>
<body>
    <script>
        var conn = new WebSocket('ws://localhost:8080');

        conn.onopen = function(e) {
            console.log("Connection established!");
            
            // Send message after the connection is established
            conn.send(JSON.stringify({
                type: 'message',
                tier: 'tier',
                content: 'content'
            }));
        };

        conn.onmessage = function(e) {
            console.log('hghjgjhghjgj'.e);
        };
    </script>
</body>
</html>
