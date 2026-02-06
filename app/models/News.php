<?php
/**
 * News Model
 *
 * Verwaltet Forum/Zeitungs-Beiträge.
 */
class News
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Erstellt einen neuen Beitrag
     */
    public function createPost(int $farmId, string $title, string $content, string $category): array
    {
        // Validierung
        if (strlen($title) < 5 || strlen($title) > 200) {
            return ['success' => false, 'message' => 'Titel muss zwischen 5 und 200 Zeichen lang sein'];
        }

        if (strlen($content) < 20) {
            return ['success' => false, 'message' => 'Inhalt muss mindestens 20 Zeichen lang sein'];
        }

        $allowedCategories = ['announcement', 'market', 'cooperative', 'tips', 'offtopic'];
        if (!in_array($category, $allowedCategories)) {
            return ['success' => false, 'message' => 'Ungültige Kategorie'];
        }

        $postId = $this->db->insert('news_posts', [
            'author_farm_id' => $farmId,
            'title' => Validator::sanitizeString($title),
            'content' => Validator::sanitizeString($content),
            'category' => $category
        ]);

        $farm = new Farm($farmId);
        $farm->addPoints(5, 'Beitrag veröffentlicht');

        Logger::info('News post created', [
            'farm_id' => $farmId,
            'post_id' => $postId
        ]);

        return [
            'success' => true,
            'message' => 'Beitrag veröffentlicht',
            'post_id' => $postId
        ];
    }

    /**
     * Erstellt einen Admin-Beitrag (News/Changelog)
     */
    public function createAdminPost(int $adminUserId, string $title, string $content, string $category, bool $pinned = false): array
    {
        // Validierung
        if (strlen($title) < 3 || strlen($title) > 200) {
            return ['success' => false, 'message' => 'Titel muss zwischen 3 und 200 Zeichen lang sein'];
        }

        if (strlen($content) < 10) {
            return ['success' => false, 'message' => 'Inhalt muss mindestens 10 Zeichen lang sein'];
        }

        $allowedCategories = ['changelog', 'admin_news'];
        if (!in_array($category, $allowedCategories)) {
            return ['success' => false, 'message' => 'Ungültige Kategorie für Admin-Posts'];
        }

        $postId = $this->db->insert('news_posts', [
            'author_farm_id' => null,
            'admin_user_id' => $adminUserId,
            'title' => Validator::sanitizeString($title),
            'content' => $content, // HTML erlaubt für Admin-Posts
            'category' => $category,
            'is_admin_post' => 1,
            'is_pinned' => $pinned ? 1 : 0
        ]);

        Logger::info('Admin news post created', [
            'admin_user_id' => $adminUserId,
            'post_id' => $postId,
            'category' => $category
        ]);

        // Discord Webhook senden
        $this->sendToDiscord($title, $content, $category, $pinned);

        return [
            'success' => true,
            'message' => 'Admin-Beitrag veröffentlicht',
            'post_id' => $postId
        ];
    }

    /**
     * Aktualisiert einen Admin-Beitrag
     */
    public function updateAdminPost(int $postId, string $title, string $content, string $category, bool $pinned = false): array
    {
        $post = $this->db->fetchOne('SELECT * FROM news_posts WHERE id = ? AND is_admin_post = 1', [$postId]);

        if (!$post) {
            return ['success' => false, 'message' => 'Admin-Beitrag nicht gefunden'];
        }

        $this->db->update('news_posts', [
            'title' => Validator::sanitizeString($title),
            'content' => $content,
            'category' => $category,
            'is_pinned' => $pinned ? 1 : 0
        ], 'id = :id', ['id' => $postId]);

        Logger::info('Admin news post updated', ['post_id' => $postId]);

        return [
            'success' => true,
            'message' => 'Beitrag aktualisiert'
        ];
    }

    /**
     * Löscht einen Admin-Beitrag
     */
    public function deleteAdminPost(int $postId): array
    {
        $post = $this->db->fetchOne('SELECT * FROM news_posts WHERE id = ? AND is_admin_post = 1', [$postId]);

        if (!$post) {
            return ['success' => false, 'message' => 'Admin-Beitrag nicht gefunden'];
        }

        // Lösche Kommentare und Likes
        $this->db->delete('news_comments', 'post_id = :id', ['id' => $postId]);
        $this->db->delete('post_likes', 'post_id = :id', ['id' => $postId]);
        $this->db->delete('news_posts', 'id = :id', ['id' => $postId]);

        Logger::info('Admin news post deleted', ['post_id' => $postId]);

        return [
            'success' => true,
            'message' => 'Beitrag gelöscht'
        ];
    }

    /**
     * Gibt alle Admin-Beiträge zurück
     */
    public function getAdminPosts(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $posts = $this->db->fetchAll(
            "SELECT np.*, u.username as admin_name,
                    (SELECT COUNT(*) FROM news_comments WHERE post_id = np.id) as comment_count
             FROM news_posts np
             LEFT JOIN users u ON np.admin_user_id = u.id
             WHERE np.is_admin_post = 1
             ORDER BY np.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );

        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM news_posts WHERE is_admin_post = 1"
        );

        return [
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Gibt Beiträge zurück
     */
    public function getPosts(
        ?string $category = null,
        int $page = 1,
        int $perPage = 20
    ): array {
        $conditions = ['1=1'];
        $params = [];

        if ($category) {
            $conditions[] = 'np.category = ?';
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT np.*,
                       COALESCE(f.farm_name, CONCAT('Admin: ', u.username)) as author_name,
                       (SELECT COUNT(*) FROM news_comments WHERE post_id = np.id) as comment_count
                FROM news_posts np
                LEFT JOIN farms f ON np.author_farm_id = f.id
                LEFT JOIN users u ON np.admin_user_id = u.id
                WHERE {$whereClause}
                ORDER BY np.is_pinned DESC, np.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $posts = $this->db->fetchAll($sql, $params);

        $totalSql = "SELECT COUNT(*) FROM news_posts np WHERE {$whereClause}";
        $total = (int) $this->db->fetchColumn($totalSql, $params);

        return [
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Gibt einen einzelnen Beitrag zurück
     */
    public function getPost(int $postId): ?array
    {
        $post = $this->db->fetchOne(
            "SELECT np.*,
                    COALESCE(f.farm_name, CONCAT('Admin: ', u.username)) as author_name
             FROM news_posts np
             LEFT JOIN farms f ON np.author_farm_id = f.id
             LEFT JOIN users u ON np.admin_user_id = u.id
             WHERE np.id = ?",
            [$postId]
        );

        if (!$post) {
            return null;
        }

        // Erhöhe Views
        $this->db->query('UPDATE news_posts SET views = views + 1 WHERE id = ?', [$postId]);

        // Hole Kommentare
        $post['comments'] = $this->db->fetchAll(
            "SELECT nc.*, f.farm_name as author_name
             FROM news_comments nc
             JOIN farms f ON nc.author_farm_id = f.id
             WHERE nc.post_id = ?
             ORDER BY nc.created_at ASC",
            [$postId]
        );

        return $post;
    }

    /**
     * Erstellt einen Kommentar
     */
    public function createComment(int $postId, int $farmId, string $content): array
    {
        // Prüfe ob Post existiert
        if (!$this->db->exists('news_posts', 'id = ?', [$postId])) {
            return ['success' => false, 'message' => 'Beitrag nicht gefunden'];
        }

        if (strlen($content) < 5) {
            return ['success' => false, 'message' => 'Kommentar muss mindestens 5 Zeichen lang sein'];
        }

        $commentId = $this->db->insert('news_comments', [
            'post_id' => $postId,
            'author_farm_id' => $farmId,
            'content' => Validator::sanitizeString($content)
        ]);

        Logger::info('Comment created', [
            'farm_id' => $farmId,
            'post_id' => $postId,
            'comment_id' => $commentId
        ]);

        return [
            'success' => true,
            'message' => 'Kommentar veröffentlicht',
            'comment_id' => $commentId
        ];
    }

    /**
     * Liked einen Beitrag
     */
    public function likePost(int $postId, int $farmId): array
    {
        // Prüfe ob bereits geliked
        $existing = $this->db->fetchOne(
            'SELECT * FROM post_likes WHERE post_id = ? AND farm_id = ?',
            [$postId, $farmId]
        );

        if ($existing) {
            // Unlike
            $this->db->delete('post_likes', 'id = ?', [$existing['id']]);
            $this->db->query('UPDATE news_posts SET likes = likes - 1 WHERE id = ?', [$postId]);

            return ['success' => true, 'message' => 'Like entfernt', 'liked' => false];
        }

        // Like
        $this->db->insert('post_likes', [
            'post_id' => $postId,
            'farm_id' => $farmId
        ]);
        $this->db->query('UPDATE news_posts SET likes = likes + 1 WHERE id = ?', [$postId]);

        return ['success' => true, 'message' => 'Beitrag geliked', 'liked' => true];
    }

    /**
     * Löscht einen Beitrag
     */
    public function deletePost(int $postId, int $farmId): array
    {
        $post = $this->db->fetchOne(
            'SELECT * FROM news_posts WHERE id = ? AND author_farm_id = ?',
            [$postId, $farmId]
        );

        if (!$post) {
            return ['success' => false, 'message' => 'Beitrag nicht gefunden oder keine Berechtigung'];
        }

        $this->db->delete('news_posts', 'id = ?', [$postId]);

        Logger::info('Post deleted', [
            'farm_id' => $farmId,
            'post_id' => $postId
        ]);

        return ['success' => true, 'message' => 'Beitrag gelöscht'];
    }

    /**
     * Löscht einen Kommentar
     */
    public function deleteComment(int $commentId, int $farmId): array
    {
        $comment = $this->db->fetchOne(
            'SELECT * FROM news_comments WHERE id = ? AND author_farm_id = ?',
            [$commentId, $farmId]
        );

        if (!$comment) {
            return ['success' => false, 'message' => 'Kommentar nicht gefunden oder keine Berechtigung'];
        }

        $this->db->delete('news_comments', 'id = ?', [$commentId]);

        return ['success' => true, 'message' => 'Kommentar gelöscht'];
    }

    /**
     * Gibt Beiträge eines Benutzers zurück
     */
    public function getUserPosts(int $farmId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM news_posts
             WHERE author_farm_id = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$farmId, $limit]
        );
    }

    /**
     * Pinnt einen Beitrag (Admin-Funktion)
     */
    public function pinPost(int $postId, bool $pinned = true): array
    {
        $this->db->update('news_posts', ['is_pinned' => $pinned ? 1 : 0], 'id = :id', ['id' => $postId]);

        return [
            'success' => true,
            'message' => $pinned ? 'Beitrag angepinnt' : 'Pin entfernt'
        ];
    }

    /**
     * Gibt die beliebtesten Beiträge zurück
     */
    public function getPopular(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT np.*, f.farm_name as author_name
             FROM news_posts np
             JOIN farms f ON np.author_farm_id = f.id
             ORDER BY np.likes DESC, np.views DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Gibt die neuesten Beiträge zurück
     */
    public function getRecent(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT np.*, f.farm_name as author_name
             FROM news_posts np
             JOIN farms f ON np.author_farm_id = f.id
             ORDER BY np.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Sucht nach Beiträgen
     */
    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        $searchTerm = "%{$query}%";
        $offset = ($page - 1) * $perPage;

        $posts = $this->db->fetchAll(
            "SELECT np.*, f.farm_name as author_name
             FROM news_posts np
             JOIN farms f ON np.author_farm_id = f.id
             WHERE np.title LIKE ? OR np.content LIKE ?
             ORDER BY np.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$searchTerm, $searchTerm]
        );

        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM news_posts WHERE title LIKE ? OR content LIKE ?",
            [$searchTerm, $searchTerm]
        );

        return [
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Sendet einen Beitrag an Discord via Webhook
     */
    private function sendToDiscord(string $title, string $content, string $category, bool $pinned): void
    {
        try {
            $discord = new DiscordWebhook();
            $discord->sendNewsPost($title, $content, $category, $pinned);
        } catch (\Exception $e) {
            // Fehler beim Discord-Senden sollte den Beitrag nicht blockieren
            Logger::error('Discord Webhook failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Gibt öffentliche News für Gäste zurück (Admin-Posts)
     */
    public function getPublicPosts(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT np.*, u.username as admin_name
             FROM news_posts np
             LEFT JOIN users u ON np.admin_user_id = u.id
             WHERE np.is_admin_post = 1 AND np.category = 'admin_news'
             ORDER BY np.is_pinned DESC, np.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Gibt öffentliches Changelog für Gäste zurück
     */
    public function getPublicChangelog(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT np.*, u.username as admin_name
             FROM news_posts np
             LEFT JOIN users u ON np.admin_user_id = u.id
             WHERE np.is_admin_post = 1 AND np.category = 'changelog'
             ORDER BY np.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
}
