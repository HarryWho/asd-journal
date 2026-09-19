<?php
require_once __DIR__ . '/../core/Model.php';

class Post extends Model
{
    public function getAllPublished(): array
    {
        return $this->fetchAll(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             WHERE status = 'published'
             ORDER BY posts.created_at DESC"
        );
    }

    public function getPublishedPage(int $perPage, int $offset): array
    {
        $perPage = max(1, $perPage);
        $offset  = max(0, $offset);

        // LIMIT/OFFSET inlined as ints (not raw user input) - PDO placeholders
        // for these two aren't reliable across all driver configs.
        return $this->fetchAll(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             WHERE status = 'published'
             ORDER BY posts.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
    }

    public function getArchiveSummary(bool $includeDrafts = false): array
    {
        $statusWhere = $includeDrafts ? '1=1' : "status = 'published'";

        return $this->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS c
             FROM posts
             WHERE {$statusWhere}
             GROUP BY period
             ORDER BY period DESC"
        );
    }

    public function getByMonthPage(string $period, int $perPage, int $offset, bool $includeDrafts = false): array
    {
        $statusWhere = $includeDrafts ? '1=1' : "status = 'published'";
        $perPage = max(1, $perPage);
        $offset  = max(0, $offset);

        return $this->fetchAll(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             WHERE {$statusWhere} AND DATE_FORMAT(posts.created_at, '%Y-%m') = ?
             ORDER BY posts.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$period]
        );
    }

    public function countByMonth(string $period, bool $includeDrafts = false): int
    {
        $statusWhere = $includeDrafts ? '1=1' : "status = 'published'";

        $row = $this->fetchOne(
            "SELECT COUNT(*) AS c FROM posts WHERE {$statusWhere} AND DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$period]
        );
        return (int) $row['c'];
    }

    public function getFeedPage(int $perPage, int $offset, bool $includeDrafts = false): array
    {
        $perPage = max(1, $perPage);
        $offset  = max(0, $offset);
        $statusWhere = $includeDrafts ? '1=1' : "status = 'published'";

        // Exclude the pinned post here - it's shown separately as the hero,
        // so it shouldn't also appear duplicated in the regular list.
        return $this->fetchAll(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             WHERE {$statusWhere} AND is_pinned = 0
             ORDER BY posts.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
    }

    public function countFeed(bool $includeDrafts = false): int
    {
        $statusWhere = $includeDrafts ? '1=1' : "status = 'published'";
        $row = $this->fetchOne("SELECT COUNT(*) AS c FROM posts WHERE {$statusWhere} AND is_pinned = 0");
        return (int) $row['c'];
    }

    public function getPinnedPost(bool $includeDrafts = false): array|false
    {
        $statusWhere = $includeDrafts ? '1=1' : "status = 'published'";

        return $this->fetchOne(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             WHERE is_pinned = 1 AND {$statusWhere}
             LIMIT 1"
        );
    }

    /**
     * Pins one post and unpins any other (only one pinned post at a time).
     */
    public function pin(int $id): void
    {
        $this->query('UPDATE posts SET is_pinned = 0 WHERE is_pinned = 1');
        $this->query('UPDATE posts SET is_pinned = 1 WHERE id = ?', [$id]);
    }

    public function unpin(int $id): void
    {
        $this->query('UPDATE posts SET is_pinned = 0 WHERE id = ?', [$id]);
    }

    public function countPublished(): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS c FROM posts WHERE status = 'published'");
        return (int) $row['c'];
    }

    public function findBySlug(string $slug): array|false
    {
        return $this->fetchOne(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             WHERE slug = ?",
            [$slug]
        );
    }

    public function getByTag(string $tagSlug): array
    {
        return $this->fetchAll(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             JOIN post_tags ON posts.id = post_tags.post_id
             JOIN tags ON post_tags.tag_id = tags.id
             WHERE posts.status = 'published' AND tags.slug = ?
             ORDER BY posts.created_at DESC",
            [$tagSlug]
        );
    }

    public function getByTagPage(string $tagSlug, int $perPage, int $offset): array
    {
        $perPage = max(1, $perPage);
        $offset  = max(0, $offset);

        return $this->fetchAll(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             JOIN post_tags ON posts.id = post_tags.post_id
             JOIN tags ON post_tags.tag_id = tags.id
             WHERE posts.status = 'published' AND tags.slug = ?
             ORDER BY posts.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$tagSlug]
        );
    }

    public function countByTag(string $tagSlug): int
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS c
             FROM posts
             JOIN post_tags ON posts.id = post_tags.post_id
             JOIN tags ON post_tags.tag_id = tags.id
             WHERE posts.status = 'published' AND tags.slug = ?",
            [$tagSlug]
        );
        return (int) $row['c'];
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne('SELECT * FROM posts WHERE id = ?', [$id]);
    }

    public function findByPrivateToken(string $token): array|false
    {
        $post = $this->fetchOne(
            "SELECT posts.*, users.username,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             WHERE private_token = ?",
            [$token]
        );

        if (!$post) {
            return false;
        }

        if ($this->isTokenExpired($post)) {
            return false;
        }

        return $post;
    }

    /**
     * Generates a brand-new private link that expires after $minutes,
     * replacing any previous link for this post (old link stops working).
     */
    public function generatePrivateLink(int $id, int $minutes = 30): array
    {
        $token     = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', time() + ($minutes * 60));

        $this->query(
            'UPDATE posts SET private_token = ?, private_token_expires_at = ? WHERE id = ?',
            [$token, $expiresAt, $id]
        );

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    /**
     * Returns the post's current link info only if it still has time left,
     * or null if there's no link yet / it already expired.
     */
    public function getActivePrivateLink(int $id): ?array
    {
        $post = $this->findById($id);

        if (!$post || empty($post['private_token']) || $this->isTokenExpired($post)) {
            return null;
        }

        return ['token' => $post['private_token'], 'expires_at' => $post['private_token_expires_at']];
    }

    private function isTokenExpired(array $post): bool
    {
        if (empty($post['private_token_expires_at'])) {
            return true; // no expiry set = treat as invalid/expired
        }

        return strtotime($post['private_token_expires_at']) < time();
    }

    public function create(int $userId, string $title, string $body, string $status = 'published', ?int $mood = null): int
    {
        $slug = $this->generateUniqueSlug($title);

        $this->query(
            'INSERT INTO posts (user_id, title, slug, body, status, mood_level) VALUES (?, ?, ?, ?, ?, ?)',
            [$userId, $title, $slug, $body, $status, $mood]
        );

        return (int) $this->lastInsertId();
    }

    public function update(int $id, string $title, string $body, string $status, ?int $mood = null): void
    {
        $this->query(
            'UPDATE posts SET title = ?, body = ?, status = ?, mood_level = ? WHERE id = ?',
            [$title, $body, $status, $mood, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->query('DELETE FROM posts WHERE id = ?', [$id]);
    }

    /**
     * Every post you've logged a mood on, oldest first - used to draw
     * the mood tracker. Includes drafts, since tracking your state
     * shouldn't depend on whether an entry ever went public.
     */
    public function getMoodHistory(int $userId): array
    {
        return $this->fetchAll(
            'SELECT id, title, slug, created_at, mood_level
             FROM posts
             WHERE user_id = ? AND mood_level IS NOT NULL
             ORDER BY created_at ASC',
            [$userId]
        );
    }

    /**
     * Turns "My First Entry" into "my-first-entry", and if that's
     * already taken, appends -2, -3, etc. until it's unique.
     */
    private function generateUniqueSlug(string $title): string
    {
        $base = strtolower(trim($title));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base);
        $base = trim($base, '-');

        $slug = $base;
        $i = 2;

        while ($this->fetchOne('SELECT id FROM posts WHERE slug = ?', [$slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
