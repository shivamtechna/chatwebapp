<?php
session_start();
include("conn/conn.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index");
    exit();
}

$currentUserId = $_SESSION['user_id'];

// Fetch other users
$sqlOtherUsers = "
    SELECT 
        u.id, u.full_name, u.image, u.status,
        COUNT(m.id) AS unread_count
    FROM users u
    LEFT JOIN messages m 
        ON m.sender_id = u.id AND m.receiver_id = ? AND m.is_read = 0
    WHERE u.id != ?
    GROUP BY u.id
";

$stmtOtherUsers = $conn->prepare($sqlOtherUsers);
$stmtOtherUsers->bind_param("ii", $currentUserId, $currentUserId);
$stmtOtherUsers->execute();
$otherUsersResult = $stmtOtherUsers->get_result();

// Fetch current logged-in user profile
$currentUser = ['full_name' => '', 'about' => '', 'email' => '', 'phone' => '', 'image' => ''];
$sqlCurrentUser = "SELECT full_name, about, email, phone, image FROM users WHERE id = ?";
$stmtCurrentUser = $conn->prepare($sqlCurrentUser);
$stmtCurrentUser->bind_param("i", $currentUserId);
$stmtCurrentUser->execute();
$currentUserResult = $stmtCurrentUser->get_result();

if ($currentUserResult->num_rows === 1) {
    $currentUser = $currentUserResult->fetch_assoc();
}

$stmtOtherUsers->close();
$stmtCurrentUser->close();
?>
    <?php include("components/header.php"); ?>
    <title>Dashboard</title>
</head>
<body>
    <section class="form__section">
        <div class="main_section" style="padding: 0; height: 90vh;">
            <div class="message_Container position-relative">
                <form id="searchForm" class="search_form" onsubmit="return false;">
                    <i class="ri-user-6-line profile" style="left: 15px;"></i>
                    <input type="search" id="searchInput" placeholder="Search Members and Chats" autocomplete="off" />
                    <i class="ri-search-line"></i>
                </form>
                <div id="searchResults">
                    <!-- Search results will appear here -->
                </div>
                <!-- profile -->
                <div class="profile-section" id="profileSection">
                    <div class="d-flex justify-content-between">
                        <h1>Profile</h1>
                        <a href="backend/logout"><i class="ri-logout-circle-line" style="font-size: 18px;"></i></a>
                    </div>
                    <!-- Profile update form for current logged-in user -->
                    <form method="POST" enctype="multipart/form-data" action="backend/profile_update">
                        <div class="profile-pic" id="profilePic" style="cursor: pointer;">
                            <img src="<?= !empty($currentUser['image']) ? 'uploads/' . htmlspecialchars($currentUser['image']) : 'assets/images/perfil.png'; ?>"
                                alt="Profile Picture" id="profileImage" />
                            <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
                        </div>

                        <div class="profile-info">
                            <p class="label">Your name</p>
                            <input type="text" class="info-box" id="nameInput" name="full_name"
                                value="<?= htmlspecialchars($currentUser['full_name'] ?? ''); ?>" autocomplete="off" />

                            <p class="label">About</p>
                            <input type="text" class="info-box" id="aboutInput" name="about"
                                value="<?= htmlspecialchars($currentUser['about'] ?? ''); ?>" autocomplete="off" />

                            <p class="label">Email</p>
                            <input type="text" class="info-box" id="emailInput" name="email"
                                value="<?= htmlspecialchars($currentUser['email'] ?? ''); ?>" autocomplete="off" />

                            <p class="label">Phone</p>
                            <input type="text" class="info-box" id="phoneInput" name="phone"
                                value="<?= htmlspecialchars($currentUser['phone'] ?? ''); ?>" autocomplete="off" />
                        </div>

                        <button type="submit" class="update-btn">Update Profile</button>
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
        // updateProfile
        const profileSection = document.getElementById("profileSection");
        // Function to save profile when clicked outside the section
        document.addEventListener("click", function (e) {
            // Prevent saving profile if clicking the delete button or inside profile section
            if (
                !profileSection.contains(e.target) &&
                !e.target.closest(".delete-chat")
            ) {
                saveProfile();
            }
        });
        function saveProfile() {
            const full_name = document.getElementById("nameInput").value.trim();
            const about = document.getElementById("aboutInput").value.trim();
            const email = document.getElementById("emailInput").value.trim();
            const phone = document.getElementById("phoneInput").value.trim();

            const formData = new URLSearchParams();
            formData.append("full_name", full_name);
            formData.append("about", about);
            formData.append("email", email);
            formData.append("phone", phone);
        };
        // search
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        // Store recent chat users in localStorage
        function storeRecentUser(user) {
            let recent = JSON.parse(localStorage.getItem('recentChatUsers') || '[]');
            recent = recent.filter(u => u.id !== user.id);
            recent.unshift(user);
            localStorage.setItem('recentChatUsers', JSON.stringify(recent));
        }
        // Called when new message received (user object expected)
        function addUserFromIncomingMessage(user) {
            storeRecentUser(user);
            renderRecentUsers();
        }
        // Search users from backend
        function searchUsers(query) {
            fetch('backend/search_users.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    sessionStorage.setItem('searchQuery', query);
                    sessionStorage.setItem('searchResults', JSON.stringify(data));
                    renderResults(data);
                })
                .catch(() => {
                    searchResults.innerHTML = "<p style='padding: 10px 25px;'>Error fetching users.</p>";
                });
        }
        // Render search or recent users
        function renderResults(users) {
            if (!users || users.length === 0) {
                searchResults.innerHTML = "<p style='padding: 10px 25px;'>No users found.</p>";
                return;
            }
            searchResults.innerHTML = users.map(user => renderUser(user)).join('');
        }
        function renderUser(user, isRecent = false) {
            return `
                <div class="position-relative" data-id="${user.id}">
                    <a href="chat.php?receiver_id=${user.id}" data-user='${JSON.stringify(user)}' class="meaasge_chat">
                        <div style="position:relative;">
                            <img src="${user.image ? 'uploads/' + user.image : 'assets/images/perfil.png'}" />
                            <span class="status-dot ${user.status === 'online' ? 'online' : 'offline'}"></span>
                        </div>
                        <span>
                            <h2>${user.full_name}</h2>
                            <p>${user.status}</p>
                        </span>
                        ${user.unread_count && user.unread_count > 0 ? `<span class="notification-dot"><i class="ri-notification-2-fill"></i></span>` : ''}
                    </a>
                    ${isRecent ? `
                    <button class="delete-chat" data-id="${user.id}">
                        <i class="ri-delete-bin-2-fill"></i>
                    </button>` : ''}
                </div>`;
        }
        // Typing input event: search or show recent
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim();
            if (q) {
                searchUsers(q);
            } else {
                searchResults.innerHTML = '';
                sessionStorage.removeItem('searchQuery');
                sessionStorage.removeItem('searchResults');
                renderRecentUsers();
            }
        });
        // On page load: restore previous search or show recent
        window.addEventListener('DOMContentLoaded', () => {
            const q = sessionStorage.getItem('searchQuery');
            const results = sessionStorage.getItem('searchResults');

            if (q && results) {
                searchInput.value = q;
                renderResults(JSON.parse(results));
            } else {
                renderRecentUsers();
            }
        });
        // Save user to recent on chat start click
        searchResults.addEventListener('click', function (e) {
            const anchor = e.target.closest('a[data-user]');
            if (anchor) {
                const user = JSON.parse(anchor.dataset.user);
                storeRecentUser(user);
            }
        });
        // Delete chat handler
        searchResults.addEventListener('click', function (e) {
            if (e.target.classList.contains('delete-chat') || e.target.closest('.delete-chat')) {
                const button = e.target.closest('.delete-chat');
                const userId = button.dataset.id;
                if (confirm('Are you sure you want to delete this chat?')) {
                    let recent = JSON.parse(localStorage.getItem('recentChatUsers') || '[]');
                    recent = recent.filter(u => u.id != userId);
                    localStorage.setItem('recentChatUsers', JSON.stringify(recent));

                    fetch('backend/delete_chat.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `receiver_id=${userId}`
                    }).then(() => {
                        renderRecentUsers();
                    }).catch(() => {
                        alert('Failed to delete chat from server.');
                        renderRecentUsers();
                    });
                }
            }
        });
        // Render recent chat users list
        function renderRecentUsers() {
            fetch('backend/get_recent_users.php')
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        searchResults.innerHTML = "<p style='height: 84vh; display: flex; justify-content: center; align-items: center;'>Search for users to start a chat.</p>";
                        return;
                    }

                    searchResults.innerHTML = `<p style="padding:10px 25px; font-weight:bold;">Recent Chats</p>` + data.map(user => renderUser(user, true)).join('');
                });
        }
    </script>
</body>
</html>