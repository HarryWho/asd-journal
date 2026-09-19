<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Post.php';

class CommentController extends Controller
{
    private Comment $commentModel;
    private Post $postModel;

    public function __construct()
    {
        $this->commentModel = new Comment();
        $this->postModel    = new Post();
    }

    // POST /comment/store -> add a comment (must be logged in, i.e. "subscribed")
    public function store(): void
    {
        $this->requireLogin();
        verify_csrf();

        $postId = (int) ($_POST['post_id'] ?? 0);
        $body   = trim($_POST['body'] ?? '');

        $post = $this->postModel->findById($postId);
        if (!$post || $body === '') {
            die('Invalid comment.');
        }

        $this->commentModel->create($postId, (int) $_SESSION['user_id'], $body);
        $this->redirect('post/show/' . $post['slug']);
    }

    // GET /comment/moderate -> list of comments awaiting approval (admin only)
    public function moderate(): void
    {
        $this->requireAdmin();
        $pending = $this->commentModel->getAllPending();
        $this->view('comments/moderate', [
            'title'   => 'Comments awaiting approval',
            'pending' => $pending,
        ]);
    }

    // POST /comment/approve/{id} -> make a pending comment public (admin only)
    public function approve(string $id = ''): void
    {
        $this->requireAdmin();
        verify_csrf();
        $this->commentModel->approve((int) $id);
        $this->redirect('comment/moderate');
    }

    // POST /comment/delete/{id} -> admin can moderate/remove/reject comments
    public function delete(string $id = ''): void
    {
        $this->requireAdmin();
        verify_csrf();
        $this->commentModel->delete((int) $id);
        $this->redirect('comment/moderate');
    }
}
