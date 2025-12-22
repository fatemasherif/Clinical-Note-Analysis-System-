<?php
class AdminForum {
    private $username;
    private $forumPosts;

    public function __construct($username, $forumPosts = []) {
        $this->username = $username;
        $this->forumPosts = $forumPosts;
    }

    public function render() {
        $totalUsers = 0; // Not needed here
        $totalFeedback = 0; // Not needed here

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Forum | CNAS</title>

  <style>
    /* ===== Base Styles ===== */
    body {
      background-color: #f3f4f6;
      font-family: system-ui, -apple-system, sans-serif;
      margin: 0;
      color: #1f2937;
    }

    h1, h2, h3 {
      margin: 0;
    }

    /* ===== Navbar ===== */
    nav {
      background-color: #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 32px;
    }

    nav h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #1d4ed8;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 24px;
      margin: 0;
      padding: 0;
      color: #374151;
    }

    nav ul a {
      text-decoration: none;
      color: inherit;
      transition: 0.2s;
    }

    nav ul a:hover {
      color: #2563eb;
    }

    nav ul a.text-blue-700 {
      font-weight: 600;
      color: #1d4ed8;
      border-bottom: 2px solid #2563eb;
      padding-bottom: 4px;
    }

    nav ul a.hover\:text-red-600:hover {
      color: #dc2626;
    }

    /* ===== Main Section ===== */
    main {
      padding: 40px;
    }

    main h2 {
      font-size: 1.875rem;
      font-weight: 700;
      color: #1e40af;
      margin-bottom: 2rem;
    }

    /* ===== Forum Section ===== */
    .feedback {
      background-color: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      border-radius: 12px;
      padding: 32px;
      margin-bottom: 2.5rem;
    }

    .feedback h3 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 16px;
    }

    .feedback ul {
      list-style: none;
      padding: 0;
    }

    .feedback li {
      padding: 12px 0;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .feedback li:last-child {
      border-bottom: none;
    }

    .post-content {
      flex: 1;
    }

    .post-actions {
      display: flex;
      gap: 8px;
    }

    .edit-btn, .delete-btn {
      padding: 4px 8px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.8rem;
    }

    .edit-btn {
      background-color: #2563eb;
      color: white;
    }

    .edit-btn:hover {
      background-color: #1d4ed8;
    }

    .delete-btn {
      background-color: #dc2626;
      color: white;
    }

    .delete-btn:hover {
      background-color: #b91c1c;
    }

    .edit-form {
      display: none;
      margin-top: 8px;
    }

    .edit-form textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 8px;
    }

    .edit-form .buttons {
      display: flex;
      gap: 8px;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav>
    <h1>CNAS Admin Dashboard</h1>
    <ul>
      <li><a href="admin_dashboard.php">Dashboard</a></li>
      <li><a href="admin_users.php">Manage Users</a></li>
      <li><a href="admin_notes.php">Clinical Notes</a></li>
      <li><a href="admin_forum.php" class="text-blue-700">Forums</a></li>
      <li><a href="logout.php" class="hover:text-red-600">Logout</a></li>
    </ul>
  </nav>

  <!-- Main -->
  <main>
    <h2>Forum Discussion History</h2>

    <!-- Forum Posts -->
    <section class="feedback">
      <h3>All Forum Posts</h3>
      <ul>
HTML;
        if (!empty($this->forumPosts)) {
            foreach ($this->forumPosts as $post) {
                echo "<li data-post-id='{$post['id']}'>
                    <div class='post-content'>
                        <b>{$post['username']} ({$post['role']})</b>: 
                        <span class='post-text'>{$post['content']}</span> 
                        <small>- {$post['created_at']}</small>
                    </div>
                    <div class='post-actions'>
                        <button class='edit-btn' onclick='editPost({$post['id']})'>Edit</button>
                        <button class='delete-btn' onclick='deletePost({$post['id']})'>Delete</button>
                    </div>
                </li>";
            }
        } else {
            echo "<li>No posts yet.</li>";
        }
        echo <<<HTML
      </ul>
    </section>
  </main>

  <script>
    function editPost(postId) {
      const li = document.querySelector('li[data-post-id="' + postId + '"]');
      const postText = li.querySelector('.post-text');
      const currentText = postText.textContent;

      // Hide actions and show edit form
      li.querySelector('.post-actions').style.display = 'none';

      // Create edit form
      const editForm = document.createElement('div');
      editForm.className = 'edit-form';
      editForm.innerHTML = '<textarea>' + currentText + '</textarea><div class="buttons"><button onclick="saveEdit(' + postId + ', this)">Save</button><button onclick="cancelEdit(' + postId + ')">Cancel</button></div>';

      li.appendChild(editForm);
      editForm.style.display = 'block';
    }

    function saveEdit(postId, button) {
      const li = document.querySelector('li[data-post-id="' + postId + '"]');
      const textarea = li.querySelector('.edit-form textarea');
      const newContent = textarea.value.trim();

      if (newContent === '') {
        alert('Post content cannot be empty');
        return;
      }

      // Send update request
      fetch('admin_forum.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=edit&post_id=' + postId + '&content=' + encodeURIComponent(newContent)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          li.querySelector('.post-text').textContent = newContent;
          cancelEdit(postId);
        } else {
          alert('Error updating post: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error updating post');
        console.error('Error:', error);
      });
    }

    function cancelEdit(postId) {
      const li = document.querySelector('li[data-post-id="' + postId + '"]');
      const editForm = li.querySelector('.edit-form');
      if (editForm) {
        editForm.remove();
      }
      li.querySelector('.post-actions').style.display = 'flex';
    }

    function deletePost(postId) {
      if (!confirm('Are you sure you want to delete this post?')) {
        return;
      }

      fetch('admin_forum.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=delete&post_id=' + postId
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.querySelector('li[data-post-id="' + postId + '"]').remove();
        } else {
          alert('Error deleting post: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error deleting post');
        console.error('Error:', error);
      });
    }
  </script>

</body>
</html>
HTML;
    }
}
?>