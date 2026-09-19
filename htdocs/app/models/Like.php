<?php
require_once __DIR__ . '/../core/Model.php';

class Like extends Model
{
    /**
     * Toggle a like on/off for a given post + identifier.
     * Identifier is "user_<id>" if logged in, or "anon_<token>" from a cookie.
     * Returns the new like count for the post.
     */
    public function toggle(int $postId, string $identifier): int
    {
        $existing = $this->fetchOne(
            'SELECT id FROM likes WHERE post_id = ? AND identifier = ?',
            [$postId, $identifier]
        );

        if ($existing) {
            $this->query('DELETE FROM likes WHERE id = ?', [$existing['id']]);
        } else {
            $this->query(
                'INSERT INTO likes (post_id, identifier) VALUES (?, ?)',
                [$postId, $identifier]
            );
        }

        return $this->countForPost($postId);
    }

    public function countForPost(int $postId): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS c FROM likes WHERE post_id = ?', [$postId]);
        return (int) $row['c'];
    }

    public function hasLiked(int $postId, string $identifier): bool
    {
        return (bool) $this->fetchOne(
            'SELECT id FROM likes WHERE post_id = ? AND identifier = ?',
            [$postId, $identifier]
        );
    }
}
