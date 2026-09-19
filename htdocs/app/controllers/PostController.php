<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Tag.php';

class PostController extends Controller
{
    private Post $postModel;
    private Comment $commentModel;
    private Tag $tagModel;

    public function __construct()
    {
        $this->postModel    = new Post();
        $this->commentModel = new Comment();
        $this->tagModel     = new Tag();
    }

    private function sanitizeBody(string $html): string
    {
        // Summernote produces these tags for its formatting options.
        // Anything else (e.g. <script>) gets stripped outright.
        $allowed = '<p><br><strong><em><u><s><h1><h2><h3><ul><ol><li><a><blockquote><code><pre><img>';
        $html = strip_tags($html, $allowed);

        // strip_tags only removes disallowed TAGS - it leaves attributes on
        // allowed tags untouched. Without this, something like
        // <a onclick="steal()"> or <a href="javascript:...">
        // could still slip through and run in a visitor's browser.
        $html = preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $html);
        $html = preg_replace('/src\s*=\s*(["\'])\s*javascript:.*?\1/i', 'src="#"', $html);

        return $html;
    }

    // POST /post/uploadimage -> saves a real file to disk and returns its URL,
    // instead of the editor embedding giant base64 blobs into the post body (admin only)
    public function uploadimage(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Not allowed']);
            return;
        }

        verify_csrf();

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid file received']);
            return;
        }

        $file = $_FILES['file'];
        $maxBytes = 4 * 1024 * 1024; // 4MB

        if ($file['size'] > $maxBytes) {
            http_response_code(400);
            echo json_encode(['error' => 'Image is too large (4MB max)']);
            return;
        }

        // Don't trust the filename or the browser-reported type - inspect the
        // actual file content to confirm it's really an image.
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            http_response_code(400);
            echo json_encode(['error' => 'That file is not a valid image']);
            return;
        }

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $mime = $imageInfo['mime'];

        if (!isset($mimeToExt[$mime])) {
            http_response_code(400);
            echo json_encode(['error' => 'Only JPG, PNG, GIF, or WEBP images are allowed']);
            return;
        }

        $uploadDir = __DIR__ . '/../../uploads/';

        // The folder may not exist yet if it was never copied over (empty
        // folders often get skipped when copying files individually) -
        // create it rather than failing.
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                http_response_code(500);
                echo json_encode(['error' => 'Uploads folder does not exist and could not be created. Check that htdocs/uploads/ exists and is writable on the server.']);
                return;
            }
        }

        if (!is_writable($uploadDir)) {
            http_response_code(500);
            echo json_encode(['error' => 'Uploads folder exists but is not writable by PHP. Check its permissions on the server (try 755).']);
            return;
        }

        $filename = bin2hex(random_bytes(12)) . '.' . $mimeToExt[$mime];
        $destPath = $uploadDir . $filename;

        // Re-encode the image through GD rather than just copying the upload.
        // This strips EXIF/metadata and neutralizes anything hidden inside the
        // file that isn't genuine image data - a disguised script embedded in
        // an image file won't survive being decoded and re-drawn from scratch.
        [$saved, $reason] = $this->reencodeImage($file['tmp_name'], $destPath, $mime);

        if (!$saved) {
            http_response_code(500);
            error_log('Image upload failed: ' . $reason);
            echo json_encode(['error' => 'Could not process that image (' . $reason . ')']);
            return;
        }

        echo json_encode(['url' => BASE_URL . 'uploads/' . $filename]);
    }

    /**
     * Returns [success, reason] - reason is only for logs/debugging,
     * never sensitive, safe to show to the admin who's uploading.
     */
    private function reencodeImage(string $sourcePath, string $destPath, string $mime): array
    {
        if (!function_exists('imagecreatefromjpeg')) {
            // GD isn't available on this server - fall back to a direct copy.
            // Still safe: mime/type was already verified via getimagesize().
            $ok = copy($sourcePath, $destPath);
            return [$ok, $ok ? '' : 'GD not available and direct copy failed - check folder permissions'];
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/gif'  => @imagecreatefromgif($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default      => false,
        };

        if (!$image) {
            return [false, "GD could not decode this {$mime} file"];
        }

        $result = match ($mime) {
            'image/jpeg' => imagejpeg($image, $destPath, 85),
            'image/png'  => imagepng($image, $destPath, 6),
            'image/gif'  => imagegif($image, $destPath),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $destPath, 85) : false,
            default      => false,
        };

        imagedestroy($image);
        return [$result, $result ? '' : "GD could not write the re-encoded {$mime} file to disk"];
    }

    private const POSTS_PER_PAGE = 10;

    public const MOOD_LABELS = [
        1 => 'Depleted',
        2 => 'Low spoons',
        3 => 'Steady',
        4 => 'Good day',
        5 => 'Full tank',
    ];

    private function parseMood(string $raw): ?int
    {
        $mood = (int) $raw;
        return ($mood >= 1 && $mood <= 5) ? $mood : null;
    }

    // GET /post/moodtracker -> your private mood-over-time view (admin only)
    public function moodtracker(): void
    {
        $this->requireAdmin();

        $history = $this->postModel->getMoodHistory((int) $_SESSION['user_id']);

        // Group by year-month for a simple monthly average bar chart
        $monthly = [];
        foreach ($history as $entry) {
            $key = date('Y-m', strtotime($entry['created_at']));
            $monthly[$key]['sum']   = ($monthly[$key]['sum'] ?? 0) + (int) $entry['mood_level'];
            $monthly[$key]['count'] = ($monthly[$key]['count'] ?? 0) + 1;
        }

        $monthlyAverages = [];
        foreach ($monthly as $key => $data) {
            $monthlyAverages[] = [
                'label'   => date('M Y', strtotime($key . '-01')),
                'average' => round($data['sum'] / $data['count'], 1),
            ];
        }

        $this->view('posts/mood-tracker', [
            'title'    => 'Mood tracker',
            'history'  => $history,
            'monthly'  => $monthlyAverages,
        ]);
    }

    // GET / -> the public feed, newest first, paginated (admin also sees drafts)
    public function index(): void
    {
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::POSTS_PER_PAGE;

        $pinned = $this->postModel->getPinnedPost($this->isAdmin());
        if ($pinned) {
            $pinnedTags = $this->tagModel->getForPost((int) $pinned['id']);
            $pinned['tags'] = $pinnedTags;
        }

        $posts      = $this->attachTags($this->postModel->getFeedPage(self::POSTS_PER_PAGE, $offset, $this->isAdmin()));
        $totalPosts = $this->postModel->countFeed($this->isAdmin());
        $totalPages = max(1, (int) ceil($totalPosts / self::POSTS_PER_PAGE));

        $this->view('posts/index', [
            'title'      => SITE_NAME,
            'posts'      => $posts,
            'pinned'     => $pinned,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }

    // GET /post/tag/{slug} -> posts filtered by one tag, paginated
    public function tag(string $slug = ''): void
    {
        $tagRow = $this->tagModel->findBySlug($slug);
        if (!$tagRow) {
            http_response_code(404);
            die('Tag not found');
        }

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::POSTS_PER_PAGE;

        $posts      = $this->attachTags($this->postModel->getByTagPage($slug, self::POSTS_PER_PAGE, $offset));
        $totalPosts = $this->postModel->countByTag($slug);
        $totalPages = max(1, (int) ceil($totalPosts / self::POSTS_PER_PAGE));

        $this->view('posts/index', [
            'title'      => $tagRow['name'] . ' — ' . SITE_NAME,
            'posts'      => $posts,
            'tagLabel'   => $tagRow['name'],
            'tagSlug'    => $slug,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }

    // GET /post/archive -> list all months you've written in
    // GET /post/archive/{YYYY-MM} -> entries from just that month
    public function archive(string $period = ''): void
    {
        if ($period === '') {
            $summary = $this->postModel->getArchiveSummary($this->isAdmin());

            $byYear = [];
            foreach ($summary as $row) {
                $year = substr($row['period'], 0, 4);
                $byYear[$year][] = [
                    'period' => $row['period'],
                    'label'  => date('F', strtotime($row['period'] . '-01')),
                    'count'  => (int) $row['c'],
                ];
            }

            $this->view('posts/archive', [
                'title'  => 'Archive — ' . SITE_NAME,
                'byYear' => $byYear,
            ]);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            http_response_code(404);
            die('Archive period not found');
        }

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::POSTS_PER_PAGE;

        $posts      = $this->attachTags($this->postModel->getByMonthPage($period, self::POSTS_PER_PAGE, $offset, $this->isAdmin()));
        $totalPosts = $this->postModel->countByMonth($period, $this->isAdmin());
        $totalPages = max(1, (int) ceil($totalPosts / self::POSTS_PER_PAGE));
        $monthLabel = date('F Y', strtotime($period . '-01'));

        $this->view('posts/index', [
            'title'          => $monthLabel . ' — ' . SITE_NAME,
            'posts'          => $posts,
            'archiveLabel'   => $monthLabel,
            'archivePeriod'  => $period,
            'page'           => $page,
            'totalPages'     => $totalPages,
        ]);
    }

    private function attachTags(array $posts): array
    {
        foreach ($posts as &$post) {
            $post['tags'] = $this->tagModel->getForPost((int) $post['id']);
        }
        return $posts;
    }

    // GET /post/show/{slug} -> read a single entry + its comments
    public function show(string $slug = ''): void
    {
        $post = $this->postModel->findBySlug($slug);
        if (!$post) {
            http_response_code(404);
            die('Post not found');
        }

        if ($post['status'] === 'draft' && !$this->isAdmin()) {
            http_response_code(404);
            die('Post not found');
        }

        $viewerId = $this->isLoggedIn() ? (int) $_SESSION['user_id'] : null;
        $comments = $this->commentModel->getVisibleForPost((int) $post['id'], $viewerId);
        $tags     = $this->tagModel->getForPost((int) $post['id']);

        $this->view('posts/show', [
            'title'    => $post['title'],
            'post'     => $post,
            'comments' => $comments,
            'tags'     => $tags,
            'page'     => (int) ($_GET['page'] ?? 1),
        ]);
    }

    // POST /post/pin/{id} -> pin this post to the top of the homepage (admin only)
    public function pin(string $id = ''): void
    {
        $this->requireAdmin();
        verify_csrf();
        $this->postModel->pin((int) $id);
        $this->redirect('');
    }

    // POST /post/unpin/{id} -> remove the pin (admin only)
    public function unpin(string $id = ''): void
    {
        $this->requireAdmin();
        verify_csrf();
        $this->postModel->unpin((int) $id);
        $this->redirect('');
    }

    // POST /post/generatelink/{id} -> create a fresh 30-min private link (admin only)
    public function generateLink(string $id = ''): void
    {
        $this->requireAdmin();
        verify_csrf();

        $minutes = (int) ($_POST['minutes'] ?? 30);
        $minutes = $minutes > 0 ? $minutes : 30;

        $this->postModel->generatePrivateLink((int) $id, $minutes);

        $post = $this->postModel->findById((int) $id);
        $this->redirect('post/edit/' . $post['slug']);
    }

    // GET /post/shared/{token} -> view a draft via its private link, no login needed
    public function shared(string $token = ''): void
    {
        if ($token === '') {
            http_response_code(404);
            die('Post not found');
        }

        $post = $this->postModel->findByPrivateToken($token);
        if (!$post) {
            http_response_code(404);
            die('This link has expired or is no longer valid.');
        }

        $viewerId = $this->isLoggedIn() ? (int) $_SESSION['user_id'] : null;
        $comments = $this->commentModel->getVisibleForPost((int) $post['id'], $viewerId);
        $tags     = $this->tagModel->getForPost((int) $post['id']);

        $this->view('posts/show', [
            'title'    => $post['title'],
            'post'     => $post,
            'comments' => $comments,
            'tags'     => $tags,
            'isPrivateView' => true,
        ]);
    }

    // GET /post/create -> show the new-post form (admin only)
    public function create(): void
    {
        $this->requireAdmin();
        $allTags = $this->tagModel->getAll();
        $this->view('posts/create', ['title' => 'New entry', 'allTags' => $allTags, 'moodLabels' => self::MOOD_LABELS]);
    }

    // POST /post/store -> save a new post (admin only)
    public function store(): void
    {
        $this->requireAdmin();
        verify_csrf();

        $title  = trim($_POST['title'] ?? '');
        $body   = trim($_POST['body'] ?? '');
        $status = $_POST['status'] ?? 'published';
        $mood   = $this->parseMood($_POST['mood'] ?? '');

        if ($title === '' || $body === '') {
            die('Title and body are required.');
        }

        $postId = $this->postModel->create((int) $_SESSION['user_id'], $title, $this->sanitizeBody($body), $status, $mood);

        $tags = trim($_POST['tags'] ?? '');
        if ($tags !== '') {
            $this->tagModel->syncTagsForPost($postId, $tags);
        }

        $this->redirect('');
    }

    // GET /post/edit/{slug} -> show the edit form (admin only)
    public function edit(string $slug = ''): void
    {
        $this->requireAdmin();

        $post = $this->postModel->findBySlug($slug);
        if (!$post) {
            die('Post not found');
        }

        $tags = $this->tagModel->getForPost((int) $post['id']);
        $allTags = $this->tagModel->getAll();

        $activeLink = null;
        if ($post['status'] === 'draft') {
            $linkInfo = $this->postModel->getActivePrivateLink((int) $post['id']);
            if ($linkInfo) {
                $activeLink = [
                    'url'        => BASE_URL . 'post/shared/' . $linkInfo['token'],
                    'expires_at' => $linkInfo['expires_at'],
                ];
            }
        }

        $this->view('posts/edit', [
            'title'      => 'Edit entry',
            'post'       => $post,
            'tags'       => $tags,
            'allTags'    => $allTags,
            'activeLink' => $activeLink,
            'moodLabels' => self::MOOD_LABELS,
            'returnPath'  => $slug . '?page=' . (int) ($_GET['page'] ?? 1)
        ]);
    }

    // POST /post/update/{id} -> save changes (admin only)
    public function update(string $id = ''): void
    {
        $this->requireAdmin();
        verify_csrf();
        $RETURN_PATH = $_POST['returnPath'] ?? '';
        $title  = trim($_POST['title'] ?? '');
        $body   = trim($_POST['body'] ?? '');
        $status = $_POST['status'] ?? 'published';
        $mood   = $this->parseMood($_POST['mood'] ?? '');

        if ($title === '' || $body === '') {
            die('Title and body are required.');
        }

        $this->postModel->update((int) $id, $title, $this->sanitizeBody($body), $status, $mood);

        $tags = trim($_POST['tags'] ?? '');
        $this->tagModel->syncTagsForPost((int) $id, $tags);

        $this->redirect('/post/show/' . $RETURN_PATH);
    }

    // POST /post/delete/{id} -> remove a post (admin only)
    public function delete(string $id = ''): void
    {
        $this->requireAdmin();
        verify_csrf();
        $this->postModel->delete((int) $id);
        $this->redirect('');
    }
}