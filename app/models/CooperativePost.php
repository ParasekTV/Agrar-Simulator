<?php
/**
 * CooperativePost Model
 *
 * Verwaltet die Genossenschafts-Pinnwand (internes Forum).
 */
class CooperativePost
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Erstellt einen neuen Beitrag
     */
    public function create(int $cooperativeId, int $authorFarmId, string $title, string $content, bool $isAnnouncement = false): array
    {
        // Prüfe Mitgliedschaft
        if (!$this->isMember($cooperativeId, $authorFarmId)) {
            return ['success' => false, 'message' => 'Du bist kein Mitglied dieser Genossenschaft'];
        }

        // Validiere Input
        $title = trim($title);
        $content = trim($content);

        if (strlen($title) < 3 || strlen($title) > 200) {
            return ['success' => false, 'message' => 'Titel muss zwischen 3 und 200 Zeichen lang sein'];
        }

        if (strlen($content) < 10) {
            return ['success' => false, 'message' => 'Inhalt muss mindestens 10 Zeichen lang sein'];
        }

        // Nur Leader/Co-Leader können Ankündigungen erstellen
        if ($isAnnouncement && !$this->isLeader($cooperativeId, $authorFarmId)) {
            $isAnnouncement = false;
        }

        $postId = $this->db->insert('cooperative_posts', [
            'cooperative_id' => $cooperativeId,
            'author_farm_id' => $authorFarmId,
            'title' => $title,
            'content' => $content,
            'is_announcement' => $isAnnouncement ? 1 : 0
        ]);

        Logger::info('Cooperative post created', [
            'coop_id' => $cooperativeId,
            'farm_id' => $authorFarmId,
            'post_id' => $postId
        ]);

        return [
            'success' => true,
            'message' => 'Beitrag erstellt!',
            'post_id' => $postId
        ];
    }

    /**
     * Gibt alle Beiträge einer Genossenschaft zurück
     */
    public function getPosts(int $cooperativeId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $posts = $this->db->fetchAll(
            "SELECT cp.*, f.farm_name as author_name,
                    (SELECT COUNT(*) FROM cooperative_post_likes WHERE post_id = cp.id) as like_count,
                    (SELECT COUNT(*) FROM cooperative_post_comments WHERE post_id = cp.id) as comment_count
             FROM cooperative_posts cp
             JOIN farms f ON cp.author_farm_id = f.id
             WHERE cp.cooperative_id = ?
             ORDER BY cp.is_pinned DESC, cp.is_announcement DESC, cp.created_at DESC
             LIMIT ? OFFSET ?",
            [$cooperativeId, $perPage, $offset]
        );

        $total = (int) $this->db->fetchColumn(
            'SELECT COUNT(*) FROM cooperative_posts WHERE cooperative_id = ?',
            [$cooperativeId]
        );

        return [
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Gibt einen einzelnen Beitrag zurück
     */
    public function getPost(int $postId, int $cooperativeId): ?array
    {
        $post = $this->db->fetchOne(
            "SELECT cp.*, f.farm_name as author_name,
                    (SELECT COUNT(*) FROM cooperative_post_likes WHERE post_id = cp.id) as like_count
             FROM cooperative_posts cp
             JOIN farms f ON cp.author_farm_id = f.id
             WHERE cp.id = ? AND cp.cooperative_id = ?",
            [$postId, $cooperativeId]
        );

        if ($post) {
            // Erhöhe Views
            $this->db->query(
                'UPDATE cooperative_posts SET views_count = views_count + 1 WHERE id = ?',
                [$postId]
            );
        }

        return $post;
    }

    /**
     * Löscht einen Beitrag
     */
    public function delete(int $postId, int $farmId, int $cooperativeId): array
    {
        $post = $this->db->fetchOne(
            'SELECT * FROM cooperative_posts WHERE id = ? AND cooperative_id = ?',
            [$postId, $cooperativeId]
        );

        if (!$post) {
            return ['success' => false, 'message' => 'Beitrag nicht gefunden'];
        }

        // Nur Autor oder Leader können löschen
        if ($post['author_farm_id'] !== $farmId && !$this->isLeader($cooperativeId, $farmId)) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        $this->db->delete('cooperative_posts', 'id = ?', [$postId]);

        Logger::info('Cooperative post deleted', [
            'post_id' => $postId,
            'deleted_by' => $farmId
        ]);

        return ['success' => true, 'message' => 'Beitrag gelöscht'];
    }

    /**
     * Pinnt/Unpinnt einen Beitrag
     */
    public function togglePin(int $postId, int $farmId, int $cooperativeId): array
    {
        if (!$this->isLeader($cooperativeId, $farmId)) {
            return ['success' => false, 'message' => 'Nur Leader können Beiträge anpinnen'];
        }

        $post = $this->db->fetchOne(
            'SELECT * FROM cooperative_posts WHERE id = ? AND cooperative_id = ?',
            [$postId, $cooperativeId]
        );

        if (!$post) {
            return ['success' => false, 'message' => 'Beitrag nicht gefunden'];
        }

        $newStatus = $post['is_pinned'] ? 0 : 1;
        $this->db->update('cooperative_posts', ['is_pinned' => $newStatus], 'id = :id', ['id' => $postId]);

        return [
            'success' => true,
            'message' => $newStatus ? 'Beitrag angepinnt' : 'Beitrag nicht mehr angepinnt',
            'is_pinned' => $newStatus
        ];
    }

    // ==========================================
    // KOMMENTARE
    // ==========================================

    /**
     * Fügt einen Kommentar hinzu
     */
    public function addComment(int $postId, int $authorFarmId, string $content): array
    {
        $post = $this->db->fetchOne('SELECT cooperative_id FROM cooperative_posts WHERE id = ?', [$postId]);

        if (!$post) {
            return ['success' => false, 'message' => 'Beitrag nicht gefunden'];
        }

        if (!$this->isMember($post['cooperative_id'], $authorFarmId)) {
            return ['success' => false, 'message' => 'Du bist kein Mitglied dieser Genossenschaft'];
        }

        $content = trim($content);
        if (strlen($content) < 2) {
            return ['success' => false, 'message' => 'Kommentar zu kurz'];
        }

        $commentId = $this->db->insert('cooperative_post_comments', [
            'post_id' => $postId,
            'author_farm_id' => $authorFarmId,
            'content' => $content
        ]);

        return [
            'success' => true,
            'message' => 'Kommentar hinzugefügt',
            'comment_id' => $commentId
        ];
    }

    /**
     * Gibt Kommentare eines Beitrags zurück
     */
    public function getComments(int $postId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, f.farm_name as author_name
             FROM cooperative_post_comments c
             JOIN farms f ON c.author_farm_id = f.id
             WHERE c.post_id = ?
             ORDER BY c.created_at ASC",
            [$postId]
        );
    }

    /**
     * Löscht einen Kommentar
     */
    public function deleteComment(int $commentId, int $farmId, int $cooperativeId): array
    {
        $comment = $this->db->fetchOne(
            "SELECT c.*, cp.cooperative_id
             FROM cooperative_post_comments c
             JOIN cooperative_posts cp ON c.post_id = cp.id
             WHERE c.id = ?",
            [$commentId]
        );

        if (!$comment) {
            return ['success' => false, 'message' => 'Kommentar nicht gefunden'];
        }

        if ($comment['cooperative_id'] !== $cooperativeId) {
            return ['success' => false, 'message' => 'Falscher Kontext'];
        }

        // Nur Autor oder Leader können löschen
        if ($comment['author_farm_id'] !== $farmId && !$this->isLeader($cooperativeId, $farmId)) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        $this->db->delete('cooperative_post_comments', 'id = ?', [$commentId]);

        return ['success' => true, 'message' => 'Kommentar gelöscht'];
    }

    // ==========================================
    // LIKES/REAKTIONEN
    // ==========================================

    /**
     * Gibt Like oder entfernt ihn
     */
    public function toggleLike(int $postId, int $farmId, string $reactionType = 'like'): array
    {
        $post = $this->db->fetchOne('SELECT cooperative_id FROM cooperative_posts WHERE id = ?', [$postId]);

        if (!$post) {
            return ['success' => false, 'message' => 'Beitrag nicht gefunden'];
        }

        if (!$this->isMember($post['cooperative_id'], $farmId)) {
            return ['success' => false, 'message' => 'Du bist kein Mitglied dieser Genossenschaft'];
        }

        // Prüfe ob bereits geliked
        $existing = $this->db->fetchOne(
            'SELECT * FROM cooperative_post_likes WHERE post_id = ? AND farm_id = ?',
            [$postId, $farmId]
        );

        if ($existing) {
            // Entferne Like
            $this->db->delete('cooperative_post_likes', 'id = ?', [$existing['id']]);
            return ['success' => true, 'message' => 'Like entfernt', 'liked' => false];
        } else {
            // Füge Like hinzu
            $this->db->insert('cooperative_post_likes', [
                'post_id' => $postId,
                'farm_id' => $farmId,
                'reaction_type' => $reactionType
            ]);
            return ['success' => true, 'message' => 'Beitrag geliked', 'liked' => true];
        }
    }

    /**
     * Prüft ob Farm einen Beitrag geliked hat
     */
    public function hasLiked(int $postId, int $farmId): bool
    {
        $result = $this->db->fetchOne(
            'SELECT id FROM cooperative_post_likes WHERE post_id = ? AND farm_id = ?',
            [$postId, $farmId]
        );
        return $result !== null;
    }

    // ==========================================
    // UNGELESENE BEITRÄGE
    // ==========================================

    /**
     * Markiert Beitrag als gelesen
     */
    public function markAsRead(int $postId, int $farmId): void
    {
        $this->db->query(
            'INSERT IGNORE INTO cooperative_post_reads (post_id, farm_id) VALUES (?, ?)',
            [$postId, $farmId]
        );
    }

    /**
     * Gibt Anzahl ungelesener Beiträge zurück
     */
    public function getUnreadCount(int $cooperativeId, int $farmId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM cooperative_posts cp
             WHERE cp.cooperative_id = ?
             AND NOT EXISTS (
                 SELECT 1 FROM cooperative_post_reads cpr
                 WHERE cpr.post_id = cp.id AND cpr.farm_id = ?
             )",
            [$cooperativeId, $farmId]
        );
    }

    // ==========================================
    // HILFSMETHODEN
    // ==========================================

    /**
     * Prüft ob Farm Mitglied der Genossenschaft ist
     */
    private function isMember(int $cooperativeId, int $farmId): bool
    {
        $result = $this->db->fetchOne(
            'SELECT id FROM cooperative_members WHERE cooperative_id = ? AND farm_id = ?',
            [$cooperativeId, $farmId]
        );
        return $result !== null;
    }

    /**
     * Prüft ob Farm Leader oder Admin ist
     */
    private function isLeader(int $cooperativeId, int $farmId): bool
    {
        $result = $this->db->fetchOne(
            "SELECT id FROM cooperative_members
             WHERE cooperative_id = ? AND farm_id = ? AND role IN ('founder', 'admin')",
            [$cooperativeId, $farmId]
        );
        return $result !== null;
    }
}
