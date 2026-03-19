<?php
// QA.php - Latest Q&A / Community Chat
include_once "./Database/db.php";

$isLoggedIn = !empty($_SESSION["user_id"]);
$userId = $isLoggedIn ? $_SESSION["user_id"] : null;
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_question'])) {
    if ($isLoggedIn) {
        $question = trim($_POST['question']);
        if (!empty($question)) {
            $stmt = $conn->prepare("INSERT INTO `qa_posts` (`user_id`, `question`) VALUES (?, ?)");
            if ($stmt) {
                $stmt->bind_param("is", $userId, $question);
                if ($stmt->execute()) {
                    $stmt->close();
                    // Redirect to prevent form resubmission
                    header("Location: index.php?qa=true");
                    exit();
                } else {
                    $error = "Failed to post your question. Please try again.";
                }
            }
        } else {
            $error = "Question cannot be empty.";
        }
    } else {
        $error = "You must be logged in to post.";
    }
}

// Fetch all posts with username
$query = "
    SELECT q.id, q.question, q.created_at, u.username
    FROM `qa_posts` q
    JOIN `users` u ON q.user_id = u.id
    ORDER BY q.created_at DESC
";
$result = $conn->query($query);
$posts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
?>

<div class="qa-page-container">
    <div class="qa-header">
        <h1>💬 Community Q&A</h1>
        <p>Ask questions, share recommendations, and chat with fellow anime fans!</p>
    </div>

    <div class="qa-content">
        <!-- Post Form -->
        <div class="qa-form-container">
            <?php if ($isLoggedIn): ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form action="index.php?qa=true" method="POST" class="qa-form">
                    <textarea
                        name="question"
                        placeholder="What's on your mind? Ask for an anime recommendation..."
                        required
                        rows="3"
                    ></textarea>
                    <div class="form-footer">
                        <span class="user-badge">Posting as: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong></span>
                        <button type="submit" name="post_question" class="btn-post">Post Message</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="login-prompt">
                    <h3>Join the conversation!</h3>
                    <p>You need to be logged in to ask questions or chat.</p>
                    <a href="?login=true" class="btn-login-prompt">Log In to Post</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Posts List -->
        <div class="posts-container">
            <h2>Recent Discussions</h2>

            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <div class="icon">🤐</div>
                    <p>It's quiet here... Be the first to start a discussion!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-avatar">
                            <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                        </div>
                        <div class="post-body">
                            <div class="post-meta">
                                <span class="post-author"><?php echo htmlspecialchars($post['username']); ?></span>
                                <span class="post-time"><?php echo date("M j, Y • g:i a", strtotime($post['created_at'])); ?></span>
                            </div>
                            <div class="post-text">
                                <?php echo nl2br(htmlspecialchars($post['question'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.qa-page-container {
    padding: 30px;
    max-width: 900px;
    margin: 0 auto;
    min-height: 100vh;
}

.qa-header {
    text-align: center;
    margin-bottom: 30px;
}

.qa-header h1 {
    font-size: 32px;
    color: #333;
    margin-bottom: 10px;
}

.qa-header p {
    font-size: 16px;
    color: #666;
}

.qa-form-container {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 40px;
}

.qa-form textarea {
    width: 100%;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: inherit;
    font-size: 15px;
    resize: vertical;
    margin-bottom: 15px;
    transition: border-color 0.3s;
}

.qa-form textarea:focus {
    outline: none;
    border-color: #667eea;
}

.form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-badge {
    font-size: 14px;
    color: #555;
}

.btn-post {
    padding: 10px 24px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-post:hover {
    background: #5568d3;
}

.login-prompt {
    text-align: center;
    padding: 20px 0;
}

.login-prompt h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.login-prompt p {
    color: #666;
    margin: 0 0 20px 0;
}

.btn-login-prompt {
    display: inline-block;
    padding: 10px 24px;
    background: #ff9800;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-login-prompt:hover {
    background: #f57c00;
}

.posts-container h2 {
    font-size: 22px;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
}

.post-card {
    display: flex;
    gap: 15px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.post-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    flex-shrink: 0;
}

.post-body {
    flex-grow: 1;
}

.post-meta {
    margin-bottom: 8px;
    display: flex;
    align-items: baseline;
    gap: 10px;
}

.post-author {
    font-weight: bold;
    font-size: 16px;
    color: #222;
}

.post-time {
    font-size: 13px;
    color: #888;
}

.post-text {
    font-size: 15px;
    color: #444;
    line-height: 1.5;
}

.no-posts {
    text-align: center;
    padding: 50px 20px;
    background: white;
    border-radius: 10px;
}

.no-posts .icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.no-posts p {
    color: #666;
    font-size: 16px;
}

.alert {
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 600px) {
    .form-footer {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }

    .btn-post {
        width: 100%;
    }

    .post-card {
        padding: 15px;
    }
}
</style>
