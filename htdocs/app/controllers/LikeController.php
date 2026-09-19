<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Like.php';

class LikeController extends Controller
{
    private Like $likeModel;

    public function __construct()
    {
        $this->likeModel = new Like();
    }

    // POST /like/toggle -> like/unlike a post, works for anyone (returns JSON)
    public function toggle(): void
    {
        $postId = (int) ($_POST['post_id'] ?? 0);
        if ($postId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid post']);
            return;
        }

        $identifier = $this->getIdentifier();
        $newCount   = $this->likeModel->toggle($postId, $identifier);
        $liked      = $this->likeModel->hasLiked($postId, $identifier);

        header('Content-Type: application/json');
        echo json_encode(['likes' => $newCount, 'liked' => $liked]);
    }

    /**
     * Returns "user_<id>" for logged-in users, or "anon_<token>" for
     * anonymous visitors, setting a long-lived cookie the first time
     * so their like sticks across visits without needing an account.
     */
    private function getIdentifier(): string
    {
        if ($this->isLoggedIn()) {
            return 'user_' . $_SESSION['user_id'];
        }

        if (empty($_COOKIE['visitor_token'])) {
            $token = bin2hex(random_bytes(16));
            setcookie('visitor_token', $token, time() + (60 * 60 * 24 * 365), '/');
            $_COOKIE['visitor_token'] = $token; // usable immediately, this request
        }

        return 'anon_' . $_COOKIE['visitor_token'];
    }
}
