<?php
/**
 * Admin Controller
 *
 * Verwaltungsoberflaeche fuer Administratoren.
 */
class AdminController extends Controller
{
    /**
     * Prueft Admin-Berechtigung
     */
    private function requireAdmin(): void
    {
        $this->requireAuth();

        $user = $this->db->fetchOne(
            'SELECT is_admin FROM users WHERE id = ?',
            [Session::getUserId()]
        );

        if (!$user || !$user['is_admin']) {
            Session::setFlash('error', 'Keine Berechtigung', 'danger');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Admin Dashboard
     */
    public function index(): void
    {
        $this->requireAdmin();

        $stats = [
            'users' => $this->db->count('users'),
            'farms' => $this->db->count('farms'),
            'cooperatives' => $this->db->count('cooperatives'),
            'active_users' => $this->db->count('users', 'last_login > DATE_SUB(NOW(), INTERVAL 7 DAY)'),
            'total_money' => $this->db->fetchColumn('SELECT SUM(money) FROM farms') ?? 0,
            'total_points' => $this->db->fetchColumn('SELECT SUM(points) FROM farms') ?? 0
        ];

        $recentUsers = $this->db->fetchAll(
            'SELECT u.*, f.farm_name, f.money, f.level
             FROM users u
             LEFT JOIN farms f ON u.id = f.user_id
             ORDER BY u.created_at DESC
             LIMIT 10'
        );

        $this->renderWithLayout('admin/index', [
            'title' => 'Admin-Bereich',
            'stats' => $stats,
            'recentUsers' => $recentUsers
        ]);
    }

    // ==========================================
    // BENUTZER-VERWALTUNG
    // ==========================================

    /**
     * Benutzer-Liste
     */
    public function users(): void
    {
        $this->requireAdmin();

        $page = (int)($this->getQueryParam('page', 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $search = $this->getQueryParam('search', '');
        $where = '1=1';
        $params = [];

        if ($search) {
            $where = 'u.username LIKE ? OR u.email LIKE ? OR f.farm_name LIKE ?';
            $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
        }

        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users u LEFT JOIN farms f ON u.id = f.user_id WHERE {$where}",
            $params
        );

        $users = $this->db->fetchAll(
            "SELECT u.*, f.id as farm_id, f.farm_name, f.money, f.points, f.level
             FROM users u
             LEFT JOIN farms f ON u.id = f.user_id
             WHERE {$where}
             ORDER BY u.id DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->renderWithLayout('admin/users', [
            'title' => 'Benutzer verwalten',
            'users' => $users,
            'page' => $page,
            'totalPages' => ceil($total / $perPage),
            'search' => $search,
            'total' => $total
        ]);
    }

    /**
     * Benutzer bearbeiten - Formular
     */
    public function editUser(int $id): void
    {
        $this->requireAdmin();

        $user = $this->db->fetchOne(
            'SELECT u.*, f.id as farm_id, f.farm_name, f.money, f.points, f.level, f.experience
             FROM users u
             LEFT JOIN farms f ON u.id = f.user_id
             WHERE u.id = ?',
            [$id]
        );

        if (!$user) {
            Session::setFlash('error', 'Benutzer nicht gefunden', 'danger');
            $this->redirect('/admin/users');
        }

        $this->renderWithLayout('admin/user_edit', [
            'title' => 'Benutzer bearbeiten',
            'user' => $user
        ]);
    }

    /**
     * Benutzer aktualisieren
     */
    public function updateUser(int $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungueltige Anfrage', 'danger');
            $this->redirect("/admin/users/{$id}");
        }

        $data = $this->getPostData();

        // Update User
        $this->db->update('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'is_admin' => isset($data['is_admin']) ? 1 : 0
        ], 'id = :id', ['id' => $id]);

        // Update Farm if exists
        $farm = $this->db->fetchOne('SELECT id FROM farms WHERE user_id = ?', [$id]);
        if ($farm) {
            $this->db->update('farms', [
                'farm_name' => $data['farm_name'],
                'money' => (float)$data['money'],
                'points' => (int)$data['points'],
                'level' => (int)$data['level'],
                'experience' => (int)$data['experience']
            ], 'user_id = :user_id', ['user_id' => $id]);
        }

        // Passwort aendern wenn angegeben
        if (!empty($data['new_password'])) {
            $hash = password_hash($data['new_password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $this->db->update('users', ['password_hash' => $hash], 'id = :id', ['id' => $id]);
        }

        Logger::info('Admin updated user', ['admin_id' => Session::getUserId(), 'user_id' => $id]);

        Session::setFlash('success', 'Benutzer aktualisiert', 'success');
        $this->redirect("/admin/users/{$id}");
    }

    /**
     * Benutzer loeschen
     */
    public function deleteUser(int $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungueltige Anfrage', 'danger');
            $this->redirect('/admin/users');
        }

        // Verhindere Selbstloeschung
        if ($id === Session::getUserId()) {
            Session::setFlash('error', 'Du kannst dich nicht selbst loeschen', 'danger');
            $this->redirect('/admin/users');
        }

        // Loesche zugehoerige Daten
        $farm = $this->db->fetchOne('SELECT id FROM farms WHERE user_id = ?', [$id]);
        if ($farm) {
            $this->db->delete('fields', 'farm_id = :id', ['id' => $farm['id']]);
            $this->db->delete('farm_animals', 'farm_id = :id', ['id' => $farm['id']]);
            $this->db->delete('farm_vehicles', 'farm_id = :id', ['id' => $farm['id']]);
            $this->db->delete('farm_research', 'farm_id = :id', ['id' => $farm['id']]);
            $this->db->delete('inventory', 'farm_id = :id', ['id' => $farm['id']]);
            $this->db->delete('cooperative_members', 'farm_id = :id', ['id' => $farm['id']]);
            $this->db->delete('farms', 'id = :id', ['id' => $farm['id']]);
        }

        $this->db->delete('users', 'id = :id', ['id' => $id]);

        Logger::info('Admin deleted user', ['admin_id' => Session::getUserId(), 'user_id' => $id]);

        Session::setFlash('success', 'Benutzer geloescht', 'success');
        $this->redirect('/admin/users');
    }

    // ==========================================
    // FARM-VERWALTUNG
    // ==========================================

    /**
     * Farm-Liste
     */
    public function farms(): void
    {
        $this->requireAdmin();

        $page = (int)($this->getQueryParam('page', 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $total = $this->db->count('farms');

        $farms = $this->db->fetchAll(
            "SELECT f.*, u.username, u.email
             FROM farms f
             JOIN users u ON f.user_id = u.id
             ORDER BY f.points DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );

        $this->renderWithLayout('admin/farms', [
            'title' => 'Hoefe verwalten',
            'farms' => $farms,
            'page' => $page,
            'totalPages' => ceil($total / $perPage),
            'total' => $total
        ]);
    }

    /**
     * Farm bearbeiten - Formular
     */
    public function editFarm(int $id): void
    {
        $this->requireAdmin();

        $farm = $this->db->fetchOne(
            'SELECT f.*, u.username, u.email
             FROM farms f
             JOIN users u ON f.user_id = u.id
             WHERE f.id = ?',
            [$id]
        );

        if (!$farm) {
            Session::setFlash('error', 'Hof nicht gefunden', 'danger');
            $this->redirect('/admin/farms');
        }

        // Hole zusaetzliche Daten
        $fields = $this->db->fetchAll('SELECT * FROM fields WHERE farm_id = ?', [$id]);
        $animals = $this->db->fetchAll(
            'SELECT fa.*, a.name as animal_name
             FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.farm_id = ?',
            [$id]
        );
        $vehicles = $this->db->fetchAll(
            'SELECT fv.*, v.name as vehicle_name
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.farm_id = ?',
            [$id]
        );

        $this->renderWithLayout('admin/farm_edit', [
            'title' => 'Hof bearbeiten',
            'farm' => $farm,
            'fields' => $fields,
            'animals' => $animals,
            'vehicles' => $vehicles
        ]);
    }

    /**
     * Farm aktualisieren
     */
    public function updateFarm(int $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungueltige Anfrage', 'danger');
            $this->redirect("/admin/farms/{$id}");
        }

        $data = $this->getPostData();

        $this->db->update('farms', [
            'farm_name' => $data['farm_name'],
            'money' => (float)$data['money'],
            'points' => (int)$data['points'],
            'level' => (int)$data['level'],
            'experience' => (int)$data['experience']
        ], 'id = :id', ['id' => $id]);

        Logger::info('Admin updated farm', ['admin_id' => Session::getUserId(), 'farm_id' => $id]);

        Session::setFlash('success', 'Hof aktualisiert', 'success');
        $this->redirect("/admin/farms/{$id}");
    }

    // ==========================================
    // GENOSSENSCHAFTEN-VERWALTUNG
    // ==========================================

    /**
     * Genossenschaften-Liste
     */
    public function cooperatives(): void
    {
        $this->requireAdmin();

        $cooperatives = $this->db->fetchAll(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM cooperative_members WHERE cooperative_id = c.id) as member_count,
                    (SELECT farm_name FROM farms WHERE id = c.founder_farm_id) as founder_name
             FROM cooperatives c
             ORDER BY c.created_at DESC'
        );

        $this->renderWithLayout('admin/cooperatives', [
            'title' => 'Genossenschaften verwalten',
            'cooperatives' => $cooperatives
        ]);
    }

    /**
     * Genossenschaft bearbeiten - Formular
     */
    public function editCooperative(int $id): void
    {
        $this->requireAdmin();

        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$id]);

        if (!$coop) {
            Session::setFlash('error', 'Genossenschaft nicht gefunden', 'danger');
            $this->redirect('/admin/cooperatives');
        }

        $members = $this->db->fetchAll(
            'SELECT cm.*, f.farm_name, f.level, f.points, u.username
             FROM cooperative_members cm
             JOIN farms f ON cm.farm_id = f.id
             JOIN users u ON f.user_id = u.id
             WHERE cm.cooperative_id = ?',
            [$id]
        );

        $this->renderWithLayout('admin/cooperative_edit', [
            'title' => 'Genossenschaft bearbeiten',
            'coop' => $coop,
            'members' => $members
        ]);
    }

    /**
     * Genossenschaft aktualisieren
     */
    public function updateCooperative(int $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungueltige Anfrage', 'danger');
            $this->redirect("/admin/cooperatives/{$id}");
        }

        $data = $this->getPostData();

        $this->db->update('cooperatives', [
            'name' => $data['name'],
            'description' => $data['description'],
            'treasury' => (float)$data['treasury'],
            'level' => (int)$data['level'],
            'member_limit' => (int)$data['member_limit']
        ], 'id = :id', ['id' => $id]);

        Logger::info('Admin updated cooperative', ['admin_id' => Session::getUserId(), 'coop_id' => $id]);

        Session::setFlash('success', 'Genossenschaft aktualisiert', 'success');
        $this->redirect("/admin/cooperatives/{$id}");
    }

    /**
     * Genossenschaft loeschen
     */
    public function deleteCooperative(int $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungueltige Anfrage', 'danger');
            $this->redirect('/admin/cooperatives');
        }

        $this->db->delete('cooperative_members', 'cooperative_id = :id', ['id' => $id]);
        $this->db->delete('cooperative_shared_equipment', 'cooperative_id = :id', ['id' => $id]);
        $this->db->delete('cooperatives', 'id = :id', ['id' => $id]);

        Logger::info('Admin deleted cooperative', ['admin_id' => Session::getUserId(), 'coop_id' => $id]);

        Session::setFlash('success', 'Genossenschaft geloescht', 'success');
        $this->redirect('/admin/cooperatives');
    }

    /**
     * Mitglied aus Genossenschaft entfernen
     */
    public function removeMember(): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungueltige Anfrage', 'danger');
            $this->redirect('/admin/cooperatives');
        }

        $data = $this->getPostData();
        $coopId = (int)$data['cooperative_id'];
        $farmId = (int)$data['farm_id'];

        $this->db->delete('cooperative_members',
            'cooperative_id = :coop_id AND farm_id = :farm_id',
            ['coop_id' => $coopId, 'farm_id' => $farmId]
        );

        Session::setFlash('success', 'Mitglied entfernt', 'success');
        $this->redirect("/admin/cooperatives/{$coopId}");
    }
}
