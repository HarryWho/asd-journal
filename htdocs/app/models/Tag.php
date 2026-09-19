<?php
require_once __DIR__ . '/../core/Model.php';

class Tag extends Model
{
    public function getAll(): array
    {
        return $this->fetchAll('SELECT * FROM tags ORDER BY name ASC');
    }

    public function findBySlug(string $slug): array|false
    {
        return $this->fetchOne('SELECT * FROM tags WHERE slug = ?', [$slug]);
    }

    public function getForPost(int $postId): array
    {
        return $this->fetchAll(
            'SELECT tags.* FROM tags
             JOIN post_tags ON tags.id = post_tags.tag_id
             WHERE post_tags.post_id = ?
             ORDER BY tags.name ASC',
            [$postId]
        );
    }

    /**
     * Takes raw comma-separated input like "Poetry, Article, mental health",
     * finds or creates each tag, and links them all to the given post.
     * Replaces whatever tags the post had before.
     */
    public function syncTagsForPost(int $postId, string $rawTagString): void
    {
        $this->query('DELETE FROM post_tags WHERE post_id = ?', [$postId]);

        $names = array_filter(array_map('trim', explode(',', $rawTagString)));

        foreach ($names as $name) {
            $tagId = $this->findOrCreate($name);
            $this->query(
                'INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)',
                [$postId, $tagId]
            );
        }
    }

    private function findOrCreate(string $name): int
    {
        $slug = $this->slugify($name);

        $existing = $this->fetchOne('SELECT id FROM tags WHERE slug = ?', [$slug]);
        if ($existing) {
            return (int) $existing['id'];
        }

        $this->query('INSERT INTO tags (name, slug) VALUES (?, ?)', [$name, $slug]);
        return (int) $this->lastInsertId();
    }

    private function slugify(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
