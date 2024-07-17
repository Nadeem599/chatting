<?php
include_once('logout_user_auto.php');

if (!isset($_SESSION['phoneNumber'])) {
    header("Location: /index.php");
    exit();
}

$secret_key = 'sk_test_51HoNUhI1jXJADDxK1ocbS5GpRNaDgwvnyyWFRUTD7JlQVRlibBOiDQjt7z3KTfZDqLkYtpeADX0Z5UUrBfs2evYR00O2W0tQ7N';

// Check subscription status
$customer_id = $_SESSION['customerid'];
$url = "https://api.stripe.com/v1/customers/$customer_id/subscriptions";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
$subscriptions_response = curl_exec($ch);
curl_close($ch);

// Check for errors
if ($subscriptions_response === false) {
    throw new Exception('cURL error: ' . curl_error($ch));
}

// Decode the JSON response
$subscriptions = json_decode($subscriptions_response, true);

$subscriptionLevel = '';
foreach ($subscriptions['data'] as $subscription) {
    if ($subscription['status'] == 'active') {
        $plan_nickname = $subscription['plan']['nickname'];
        if ($plan_nickname == 'level1') {
            $subscriptionLevel = 'bronze';
        } elseif ($plan_nickname == 'level2') {
            $subscriptionLevel = 'silver';
        } elseif ($plan_nickname == 'level3') {
            $subscriptionLevel = 'gold';
        } elseif ($plan_nickname == 'dailybasic') {
            $subscriptionLevel = 'admin';
        }
    } else {
      // do nothing
    }
}

if(empty($subscriptions['data'])){
    $subscriptionLevel = 'nonsubscriber';
}

if ($subscriptionLevel) {
    $_SESSION['subscriptionLevel'] = $subscriptionLevel;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINICHAT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
            justify-content: center;
            text-align: center;
        }
        .container {
            flex-wrap: wrap;
            gap: 40px;
            justify-content: center;
            align-items: center;
        }
        .chat-window {
            flex: 1 1 300px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            max-width: 400px;
            justify-content: center;
            align-items: center;
        }
        h2 {
            color: #333;
            margin-top: 0;
            justify-content: center;
            align-items: center;
        }
        .message-list {
            height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message {
            background-color: #e6f2ff;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message .sender {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .input-area {
            display: flex;
        }
        .input-area input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px 0 0 5px;
        }
        .input-area button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }
        #notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 16px;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    
    <center>
        <div class="container">
            <div class="chat-window" id="admin-window" style="display: none;">
                <h2>Admin Window</h2>
                <div class="message-list" id="admin-messages"></div>
                <div class="input-area">
                    <select id="tier-select">
                        <option value="bronze">Bronze</option>
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                        <option value="nonsubscriber">nonsubscriber</option>
                    </select>
                    <input type="text" id="admin-input" placeholder="Type your message...">
                    <button onclick="sendMessage()">Send</button>
                </div>
                <button onclick="clearMessages()">Clear All Messages</button>
            </div>

            <div class="chat-window" id="bronze-window" style="display: none;">
                <h2>Bronze Tier</h2>
                <div class="message-list" id="bronze-messages"></div>
            </div>

            <div class="chat-window" id="silver-window" style="display: none;">
                <h2>Silver Tier</h2>
                <div class="message-list" id="silver-messages"></div>
            </div>

            <div class="chat-window" id="gold-window" style="display: none;">
                <h2>Gold Tier</h2>
                <div class="message-list" id="gold-messages"></div>
            </div>
            <div class="chat-window" id="commonchat-window" style="display: none;">
                <h2>Common chat Tier</h2>
                <div class="message-list" id="commonchat-messages"></div>
            </div>
        </div>
    </center>

    <div id="notification"></div>

    <audio id="typing-sound" src="typing-sound.mp3"></audio>
    <audio id="notification-sound" src="notification-sound.mp3"></audio>

    <script>
    const subscriptionLevel = "<?php echo $_SESSION['subscriptionLevel'] ?? ''; ?>";
    const ws = new WebSocket('ws://localhost:8080');


    ws.onopen = () => {
        console.log('WebSocket connection established nadee');
    };

    ws.onmessage = (event) => {
     const data = JSON.parse(event.data);

        if (data.type === 'message') {
            if (data.tier === 'nonsubscriber') {
                
                if (subscriptionLevel === 'admin' || subscriptionLevel === 'bronze' || subscriptionLevel === 'silver' || subscriptionLevel === 'gold' || subscriptionLevel === 'nonsubscriber') {
                    displayMessage(data.tier, data.timestamp , data.message);
                    showNotification(`hey! Lets check New message`);
                    playSound('notification-sound.mp3');
                }
                
            } else if (data.tier === 'bronze') {
                
                if (subscriptionLevel === 'admin' || subscriptionLevel === 'bronze' || subscriptionLevel === 'silver' || subscriptionLevel === 'gold') {
                    displayMessage(data.tier, data.timestamp , data.message);
                    showNotification(`hey! Lets check New message`);
                    playSound('notification-sound.mp3');
                }
                
            } else if (data.tier === 'silver') {
                if (subscriptionLevel === 'silver' || subscriptionLevel === 'admin' || subscriptionLevel === 'gold') {
                    displayMessage(data.tier, data.timestamp , data.message);
                    showNotification(`hey! Lets check New message`);
                    playSound('notification-sound.mp3');
                }
            } else if (data.tier === 'gold') {
                if (subscriptionLevel === 'gold' || subscriptionLevel === 'admin') {
                    displayMessage(data.tier, data.timestamp , data.message);
                    showNotification(`hey! Lets check New message`);
                    playSound('notification-sound.mp3');
                }
            }else{
                // don nothing
            }
        } else if (data.type === 'load') {
           loadOldMessages(data.messages);
        }else if (data.type === 'clear') {
            clearAllMessages();
        }
    };



    
    function sendMessage() {
        const tier = document.getElementById('tier-select').value;
        const message = document.getElementById('admin-input').value;
        if (message.trim() === '') return;

        const type = 'message';

        const now = new Date();
        const timestamp = now.toLocaleString();

        ws.send(JSON.stringify({
            type: type,
            tier: tier,
            content: message,
            timestamp: timestamp
        }));

        document.getElementById('admin-input').value = '';
    }



    ws.onclose = () => {
        console.log('WebSocket connection closed');
    };

    ws.onerror = (error) => {
        console.error('WebSocket error:', error);
    };

  

    function playSound(filename) {
        const audio = new Audio(filename);
        audio.play();
    }

    function displayMessage(tier, timestamp , content) {
        const messageElement = document.createElement('div');
        messageElement.className = 'message';
        messageElement.innerHTML = `<div class="sender">${timestamp}</div><div class="content">${content}</div>`;

        if ( tier === 'nonsubscriber') {
            document.getElementById('commonchat-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('bronze-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('silver-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('gold-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('admin-messages').appendChild(messageElement.cloneNode(true));
        }

        if ( tier === 'bronze') {
            document.getElementById('bronze-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('silver-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('gold-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('admin-messages').appendChild(messageElement.cloneNode(true));
        }
        if (tier === 'silver') {
            document.getElementById('silver-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('gold-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('admin-messages').appendChild(messageElement.cloneNode(true));
        }
        if (tier === 'gold') {
            document.getElementById('gold-messages').appendChild(messageElement.cloneNode(true));
            document.getElementById('admin-messages').appendChild(messageElement.cloneNode(true));
        }
    }

    function showNotification(message) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    function loadOldMessages(messages) {
        if (messages.trim() === '') return;

        const messageArray = messages.trim().split('\n');
        messageArray.forEach(message => {
            const data = JSON.parse(message);
            displayMessage(data.tier, data.timestamp, data.message);
        });
    }


    function clearMessages() {
        ws.send(JSON.stringify({ type: 'clear' }));
    }


    function clearAllMessages() {
        document.getElementById('admin-messages').innerHTML = '';
        document.getElementById('bronze-messages').innerHTML = '';
        document.getElementById('silver-messages').innerHTML = '';
        document.getElementById('gold-messages').innerHTML = '';
        document.getElementById('commonchat-messages').innerHTML = '';

        if(subscriptionLevel == 'admin'){
            showNotification('All Messages are removed!');
            playSound('notification-sound.mp3');
        }
    }


    if (subscriptionLevel === 'bronze') {
        document.getElementById('bronze-window').style.display = 'block';
    } else if (subscriptionLevel === 'silver') {
        document.getElementById('silver-window').style.display = 'block';
    } else if (subscriptionLevel === 'gold') {
        document.getElementById('gold-window').style.display = 'block';
    }else if (subscriptionLevel === 'admin') {
        document.getElementById('admin-window').style.display = 'block';
        document.getElementById('bronze-window').style.display = 'block';
        document.getElementById('silver-window').style.display = 'block';
        document.getElementById('gold-window').style.display = 'block';
        document.getElementById('commonchat-window').style.display = 'block';
    }else if(subscriptionLevel === 'nonsubscriber'){
        document.getElementById('commonchat-window').style.display = 'block';
    }
</script>
</body>
</html>
