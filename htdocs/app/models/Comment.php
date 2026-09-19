<?php
require_once __DIR__ . '/../core/Model.php';

class Comment extends Model
{
    /**
     * Approved comments for everyone, PLUS the current viewer's own
     * pending comment(s) on this post - so they see their own comment
     * marked as "awaiting approval" instead of it just vanishing.
     */
    public function getVisibleForPost(int $postId, ?int $viewerId = null): array
    {
        if ($viewerId !== null) {
            return $this->fetchAll(
                "SELECT comments.*, users.username
                 FROM comments
                 JOIN users ON comments.user_id = users.id
                 WHERE post_id = ?
                   AND (status = 'approved' OR (status = 'pending' AND comments.user_id = ?))
                 ORDER BY created_at ASC",
                [$postId, $viewerId]
            );
        }

        return $this->fetchAll(
            "SELECT comments.*, users.username
             FROM comments
             JOIN users ON comments.user_id = users.id
             WHERE post_id = ? AND status = 'approved'
             ORDER BY created_at ASC",
            [$postId]
        );
    }

    public function create(int $postId, int $userId, string $body): int
    {
        $this->query(
            "INSERT INTO comments (post_id, user_id, body, status) VALUES (?, ?, ?, 'pending')",
            [$postId, $userId, $body]
        );
        return (int) $this->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->query('DELETE FROM comments WHERE id = ?', [$id]);
    }

    public function approve(int $id): void
    {
        $this->query("UPDATE comments SET status = 'approved' WHERE id = ?", [$id]);
    }

    public function getAllPending(): array
    {
        return $this->fetchAll(
            "SELECT comments.*, users.username, posts.title AS post_title, posts.slug AS post_slug
             FROM comments
             JOIN users ON comments.user_id = users.id
             JOIN posts ON comments.post_id = posts.id
             WHERE comments.status = 'pending'
             ORDER BY comments.created_at ASC"
        );
    }

    public function countPending(): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS c FROM comments WHERE status = 'pending'");
        return (int) $row['c'];
    }
}
