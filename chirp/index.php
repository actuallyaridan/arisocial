<?php
session_start();
try {
    // Connect to the SQLite database
    $db = new PDO('sqlite:' . __DIR__ . '/../chirp.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize default values
    $user = "Loading";
    $status = "If this stays here for a prolonged period of time, reload this page.";
    $timestamp = gmdate("Y-m-d\TH:i\Z");

    // Check if an id parameter is present in the URL
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $postId = $_GET['id'];

        // Fetch the post with the given ID
        $query = 'SELECT * FROM chirps WHERE id = :id';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            // Fetch user details from users table
            $userId = $post['user'];
            $userQuery = 'SELECT username, name, profilePic FROM users WHERE id = :id';
            $userStmt = $db->prepare($userQuery);
            $userStmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $userStmt->execute();
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                $user = htmlspecialchars($userData['username']);
                $profilePic = htmlspecialchars($userData['profilePic']);
                $name = htmlspecialchars($userData['name']);
            }

            $title = "$name on Chirp: \"" . htmlspecialchars($post['chirp']) . "\" / Chirp";
            $timestamp = gmdate("Y-m-d\TH:i\Z", $post['timestamp']);
            // Convert newlines to <br> tags
            $status = nl2br(htmlspecialchars($post['chirp']));
        }
    }
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo isset($title) ? $title : 'Chirp'; ?></title>
    <meta charset="UTF-8">
    <meta name="theme-color" content="#00001" /><meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link href="/src/styles/styles.css" rel="stylesheet">
    <link href="/src/styles/timeline.css" rel="stylesheet">
    <link href="/src/styles/menus.css" rel="stylesheet">
    <link href="/src/styles/responsive.css" rel="stylesheet">
  
    <script defer src="https://cdn.jsdelivr.net/npm/@twemoji/api@latest/dist/twemoji.min.js"
        crossorigin="anonymous"></script>
    <script src="/src/scripts/general.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

<body>
<body>
    <header>
        <div id="desktopMenu">
            <nav>
                <img src="/src/images/icons/chirp.svg" alt="Chirp" onclick="playChirpSound()">
                <a href="/"><img src="/src/images/icons/house.svg" alt=""> Home</a>
                <a href="/explore"><img src="/src/images/icons/search.svg" alt=""> Explore</a>
                <a href="/notifications"><img src="/src/images/icons/bell.svg" alt=""> Notifications</a>
                <a href="/messages"><img src="/src/images/icons/envelope.svg" alt=""> Messages</a>
                <a href="/user"><img src="/src/images/icons/person.svg" alt=""> Profile</a>
                <a href="/compose" class="newchirp">Chirp</a>
            </nav>
            <div id="menuSettings">
                <a href="settings">⚙️ Settings</a>
                <?php if (isset($_SESSION['username'])): ?>
                <a href="/signout.php">🚪 Sign Out</a>
                <?php else: ?>
                <a href="/signin/">🚪 Sign In</a>
                <?php endif; ?>
            </div>
            <button id="settingsButtonWrapper" type="button" onclick="showMenuSettings()">
                <img class="userPic"
                    src="<?php echo isset($_SESSION['profile_pic']) ? htmlspecialchars($_SESSION['profile_pic']) : '/src/images/users/guest/user.svg'; ?>"
                    alt="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest'; ?>">
                <div>
                    <p><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Guest'; ?></p>
                    <p class="subText">
                        @<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest'; ?>
                    </p>
                </div>
                <p class="settingsButton">⚙️</p>
            </button>
        </div>
    </header>

    <main>
        <div id="feed" class="thread">
            <div id="iconChirp" onclick="playChirpSound()">
                <img src="/src/images/icons/chirp.svg" alt="Chirp">
            </div>
            <div id="timelineSelect">
                <button id="back" class="selected" onclick="back()"><img alt="" class="emoji"
                        src="/src/images/icons/back.svg"> Back</button>
            </div>
            <?php if (!$post || empty($postId)) : ?>
            <!-- If post is not found or no ID provided, show this -->
            <div id="notFound">
                <p>Chirp not found</p>
                <p class="subText">That chirp does not exist.</p>
            </div>
            <?php else : ?>
            <!-- Display the fetched post -->
            <div id="chirps">
                <div class="chirpThread" id="<?php echo $postId; ?>">
                    <div class="chirpInfo">
                        <div>
                            <img class="userPic"
                                src="<?php echo isset($profilePic) ? htmlspecialchars($profilePic) : '/src/images/users/guest/user.svg'; ?>"
                                alt="<?php echo isset($user) ? htmlspecialchars($user) : 'Guest'; ?>">
                            <div>
                                <p><?php echo isset($name) ? htmlspecialchars($name) : 'Guest'; ?></p>
                                <p class="subText">@<?php echo isset($user) ? htmlspecialchars($user) : 'guest'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Display chirp content with line breaks -->
                    <pre><?php echo $status; ?></pre>
                    <div class="chirpInteractThread">
                        <p class="subText postedDate">Posted at:
                            <script>
                                const options = {
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                };
                                document.write(new Date("<?php echo $timestamp ?>").toLocaleString([], options));
                            </script>
                        </p>
                        <div>
                            <button type="button" class="reply"><img alt="Reply"
                                    src="/src/images/icons/reply.svg"><br>0 replies</button>
                            <button type="button" class="rechirp"><img alt="Rechirp"
                                    src="/src/images/icons/rechirp.svg"><br>0 rechirps</button>
                            <button type="button" class="like"><img alt="Like"
                                    src="/src/images/icons/like.svg"><br>0 likes</button>
                        </div>
                    </div>
                    <div id="replyTo">
                        <textarea id="replytotext" maxlength="240" placeholder="Reply to @<?php echo isset($user) ? htmlspecialchars($user) : 'guest'; ?>..."></textarea>
                        <button class="postChirp">Reply</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <aside id="sideBar">
        <div id="trends">
            <p>Trends for you</p>
            <div>
                <a>gay people</a>
                <p class="subText">12 chirps</p>
            </div>
            <div>
                <a>twitter</a>
                <p class="subText">47 chirps</p>
            </div>
            <div>
                <a>iphone 69</a>
                <p class="subText">62 chirps</p>
            </div>
        </div>
        <div id="whotfollow">
            <p>Who to follow</p>
            <div>
                <div>
                    <img class="userPic"
                        src="https://pbs.twimg.com/user_images/1717013664954499072/2dcJ0Unw_400x400.png" alt="Apple">
                    <div>
                        <p>Apple <img class="verified" src="/src/images/icons/verified.svg" alt="Verified"></p>
                        <p class="subText">@apple</p>
                    </div>
                </div>
                <a class="followButton following">Following</a>
            </div>
            <div>
                <div>
                    <img class="userPic"
                        src="https://pbs.twimg.com/user_images/1380530524779859970/TfwVAbyX_400x400.jpg"
                        alt="President Biden">
                    <div>
                        <p>President Biden <img class="verified" src="/src/images/icons/verified.svg" alt="Verified">
                        </p>
                        <p class="subText">@POTUS</p>
                    </div>
                </div>
                <a class="followButton">Follow</a>
            </div>
        </div>
        <div>
            <p class="subText">Inspired by Twitter/X. No code has been sourced from Twitter/X. Twemoji by Twitter Inc/X
                Corp is licensed under CC-BY 4.0.</p>
        </div>
    </aside>
    <footer>
        <div class="mobileCompose">
            <a class="chirpMoile" href="compose">Chirp</a>
        </div>
        <div>
            <a href="/"><img src="/src/images/icons/house.svg" alt="Home"></a>
            <a href="/explore"><img src="/src/images/icons/search.svg" alt="Explore"></a>
            <a href="/notifications"><img src="/src/images/icons/bell.svg" alt="Notifications"></a>
            <a href="/messages"><img src="/src/images/icons/envelope.svg" alt="Messages"></a>
            <a href="/user"><img src="/src/images/icons/person.svg" alt="Profile"></a>
        </div>
    </footer>
    <script src="/src/scripts/general.js"></script>
</body>

</html>
