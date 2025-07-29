<?php
session_start();
include("conn/conn.php");

$userId = $_SESSION['user_id'] ?? 0;
$receiverId = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

if ($userId == 0) {
    die("User not logged in.");
}

if ($receiverId == 0) {
    die("Receiver ID not found in URL.");
}

$sql = "SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $userId, $receiverId, $receiverId, $userId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

$sqlUser = "SELECT full_name, image, is_typing FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $receiverId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser->num_rows === 1) {
    $receiver = $resultUser->fetch_assoc();
    $receiverName = $receiver['full_name'];
    $receiverImage = $receiver['image'] ? 'uploads/' . $receiver['image'] : 'assets/images/default-profile.png';
    $isTyping = (bool)$receiver['is_typing'];
} else {
    $receiverName = "Unknown User";
    $receiverImage = "assets/images/default-profile.png";
    $isTyping = false;
}
if (isset($_GET['receiver_id'])) {
    $currentUserId = $_SESSION['user_id'];
    $receiverId = $_GET['receiver_id'];
    $sql = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $receiverId, $currentUserId);
    $stmt->execute();
}
$stmtUser->close();
?>
    <?php include("components/header.php"); ?>
    <title>Chat <?php echo htmlspecialchars($receiverName); ?> </title>
</head>
<body>
    <section class="form__section">
        <div class="main_section p-0" style="height: 90vh;">
            <div class="message_Container" style="position: relative; background: #222e35; padding-bottom: 15px;">
                <div class="top_message_row">
                    <div class="message_chatTwo">
                        <a href="dashboard"><i class="ri-arrow-left-line" style="font-size: 18px;"></i></a>
                        <img src="<?php echo htmlspecialchars($receiverImage); ?>" alt="Receiver Image" />
                        <span>
                            <h3>
                                <?php echo htmlspecialchars($receiverName); ?>
                            </h3>
                            <p>
                                <?php echo htmlspecialchars($isTyping ? 'offline' : 'Online'); ?>
                            </p>
                        </span>
                    </div>
                </div>
                <div class="middle_message_row" id="chatBox">
                    <?php foreach ($messages as $msg): ?>
                    <?php if ($msg['sender_id'] == $userId): ?>
                    <div class="text-end d-flex justify-content-end align-items-center message"
                        data-id="<?php echo $msg['id']; ?>">
                        <p>
                            <span class="message_send">
                                <span class="message_name">
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </span>
                                <span class="message_time">
                                    <?php echo date("H:i", strtotime($msg['created_at'])); ?>
                                </span>
                                <span class="message_check">
                                    <i class="ri-check-double-line"
                                        style="color: <?php echo ($msg['is_read']) ? '#007bff' : 'gray'; ?>;"></i>
                                </span>
                            </span>
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center message" data-id="<?php echo $msg['id']; ?>">
                        <p>
                            <span class="message_received">
                                <span class="message_name">
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </span>
                                <span class="message_time">
                                    <?php echo date("H:i", strtotime($msg['created_at'])); ?>
                                </span>
                            </span>
                        </p>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="bottom_message_row">
                    <form id="chatForm">
                        <input type="text" id="messageInput" placeholder="Type a message" autocomplete="off" />
                        <button type="submit"><i class="ri-send-plane-fill"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- ---------------JS---------------- -->
    <script src="assets/js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
        const receiverId = <?php echo json_encode($receiverId); ?>;

        const chatForm = document.getElementById("chatForm");
        const messageInput = document.getElementById("messageInput");
        const chatBox = document.getElementById("chatBox");

        let hasUnreadMessages = false;

        chatForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            fetch("backend/send_message.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `receiver_id=${receiverId}&message=${encodeURIComponent(message)}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = "";
                        loadMessages();
                    }
                });
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function isChatBoxVisible() {
            const rect = chatBox.getBoundingClientRect();
            return rect.top >= 0 && rect.bottom <= window.innerHeight;
        }

        function markMessagesAsRead() {
            fetch('backend/mark_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `sender_id=${receiverId}`
            });
        }

        function loadMessages() {
            fetch(`backend/fetch_messages.php?receiver_id=${receiverId}`)
                .then(res => res.json())
                .then(messages => {
                    chatBox.innerHTML = "";
                    hasUnreadMessages = false;

                    messages.forEach(msg => {
                        const isSender = msg.sender_id == currentUserId;
                        const dt = new Date(msg.created_at);
                        const options = { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Kolkata' };
                        const formattedTime = dt.toLocaleTimeString('en-IN', options);

                        if (!isSender && msg.is_read == 0) {
                            hasUnreadMessages = true;
                        }

                        if (isSender) {
                            chatBox.innerHTML += `
                                <div class="text-end d-flex justify-content-end align-items-center">
                                    <p>
                                        <span class="message_send">
                                            <span class="message_name">${escapeHtml(msg.message)}</span>
                                            <span class="message_time">${formattedTime}</span>
                                            <span class="message_check">
                                                <i class="ri-check-double-line" style="color: ${msg.is_read == 1 ? '#4949c4' : 'gray'};"></i>
                                            </span>
                                        </span>
                                    </p>
                                </div>`;
                        } else {
                            chatBox.innerHTML += `
                                <div class="d-flex align-items-center">
                                    <p>
                                        <span class="message_received">
                                            <span class="message_name">${escapeHtml(msg.message)}</span>
                                            <span class="message_time">${formattedTime}</span>
                                        </span>
                                    </p>
                                </div>`;
                        }
                    });

                    chatBox.scrollTop = chatBox.scrollHeight;

                    // Only mark as read when tab is active + chat is visible + unread messages
                    if (document.visibilityState === "visible" && isChatBoxVisible() && hasUnreadMessages) {
                        markMessagesAsRead();
                        hasUnreadMessages = false;
                    }
                });
        }

        // Check again when tab becomes active
        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible") {
                loadMessages();
            }
        });

        // Also check on scroll (in case user scrolls down to chat)
        window.addEventListener("scroll", () => {
            if (document.visibilityState === "visible" && isChatBoxVisible() && hasUnreadMessages) {
                markMessagesAsRead();
                hasUnreadMessages = false;
            }
        });

        setInterval(loadMessages, 1000);
        loadMessages();
    </script>
</body>
</html>