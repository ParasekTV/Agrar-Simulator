<?php
/**
 * Cooperative Controller
 *
 * Verwaltet Agrargenossenschaften.
 */
class CooperativeController extends Controller
{
    /**
     * Zeigt Genossenschaften-Übersicht
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $coopModel = new Cooperative();

        $membership = $coopModel->getMembership($farmId);

        $data = [
            'title' => 'Genossenschaften',
            'membership' => $membership,
            'cooperatives' => $coopModel->getAll()
        ];

        if ($membership) {
            $data['coopDetails'] = $coopModel->getDetails($membership['cooperative_id']);
        }

        $this->renderWithLayout('cooperative/index', $data);
    }

    /**
     * Zeigt Details einer Genossenschaft
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $coopModel = new Cooperative();
        $details = $coopModel->getDetails($id);

        if (!$details) {
            Session::setFlash('error', 'Genossenschaft nicht gefunden', 'danger');
            $this->redirect('/cooperative');
        }

        $data = [
            'title' => $details['name'],
            'cooperative' => $details,
            'membership' => $coopModel->getMembership($this->getFarmId())
        ];

        $this->renderWithLayout('cooperative/show', $data);
    }

    /**
     * Gründet eine Genossenschaft (POST)
     */
    public function create(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('name', 'Name erforderlich')
            ->minLength('name', 3, 'Name muss mindestens 3 Zeichen lang sein')
            ->maxLength('name', 50, 'Name darf maximal 50 Zeichen lang sein');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/cooperative');
        }

        $coopModel = new Cooperative();
        $result = $coopModel->create(
            $this->getFarmId(),
            Validator::sanitizeString($data['name']),
            Validator::sanitizeString($data['description'] ?? '')
        );

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'cooperative', 1);
        }

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative');
    }

    /**
     * Tritt einer Genossenschaft bei (POST)
     */
    public function join(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->join($this->getFarmId(), (int) $data['cooperative_id']);

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'cooperative', 1);
        }

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative');
    }

    /**
     * Verlässt die Genossenschaft (POST)
     */
    public function leave(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative');
        }

        $coopModel = new Cooperative();
        $result = $coopModel->leave($this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative');
    }

    /**
     * Spendet an die Genossenschaft (POST)
     */
    public function donate(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->donate($this->getFarmId(), (float) $data['amount']);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative');
    }

    /**
     * Teilt ein Gerät (POST)
     */
    public function shareEquipment(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->shareEquipment(
            $this->getFarmId(),
            (int) $data['farm_vehicle_id'],
            (float) ($data['fee_per_hour'] ?? 0)
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative');
    }

    /**
     * Leiht ein Gerät aus (POST)
     */
    public function borrowEquipment(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->borrowEquipment(
            $this->getFarmId(),
            (int) $data['equipment_id']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative');
    }

    /**
     * Gibt ein ausgeliehenes Gerät zurück (POST)
     */
    public function returnEquipment(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->returnEquipment(
            $this->getFarmId(),
            (int) $data['equipment_id'],
            (float) ($data['hours_used'] ?? 1)
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative');
    }

    /**
     * API: Gibt Genossenschaften zurück
     */
    public function listApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $coopModel = new Cooperative();

        return $this->json([
            'cooperatives' => $coopModel->getAll(),
            'membership' => $coopModel->getMembership($this->getFarmId())
        ]);
    }

    /**
     * API: Gibt Mitglieder einer Genossenschaft zurück
     */
    public function membersApi(int $id): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $coopModel = new Cooperative();
        $details = $coopModel->getDetails($id);

        if (!$details) {
            return $this->jsonError('Genossenschaft nicht gefunden', 404);
        }

        return $this->json(['members' => $details['members']]);
    }

    // ============================================
    // BEWERBUNGEN
    // ============================================

    /**
     * Sendet eine Bewerbung (POST)
     */
    public function apply(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->applyToJoin(
            $this->getFarmId(),
            (int) $data['cooperative_id'],
            Validator::sanitizeString($data['message'] ?? '')
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative');
    }

    /**
     * Zeigt Bewerbungen
     */
    public function applications(): void
    {
        $this->requireAuth();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/cooperative');
        }

        $data = [
            'title' => 'Bewerbungen',
            'membership' => $membership,
            'applications' => $coopModel->getPendingApplications($membership['cooperative_id']),
            'canManage' => $coopModel->hasPermission($this->getFarmId(), 'manage_members')
        ];

        $this->renderWithLayout('cooperative/applications', $data);
    }

    /**
     * Bearbeitet Bewerbung (POST)
     */
    public function processApplication(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative/applications');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->processApplication(
            $this->getFarmId(),
            (int) $data['application_id'],
            $data['action'] === 'accept'
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative/applications');
    }

    // ============================================
    // MITGLIEDER VERWALTEN
    // ============================================

    /**
     * Zeigt Mitgliederverwaltung
     */
    public function members(): void
    {
        $this->requireAuth();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/cooperative');
        }

        $coopDetails = $coopModel->getDetails($membership['cooperative_id']);

        $data = [
            'title' => 'Mitglieder verwalten',
            'membership' => $membership,
            'coopDetails' => $coopDetails,
            'roles' => $coopModel->getRoles(),
            'canManage' => $coopModel->hasPermission($this->getFarmId(), 'manage_members')
        ];

        $this->renderWithLayout('cooperative/members', $data);
    }

    /**
     * Weist Rolle zu (POST)
     */
    public function assignRole(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative/members');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->assignRole(
            $this->getFarmId(),
            (int) $data['target_farm_id'],
            $data['role']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative/members');
    }

    /**
     * Entfernt Mitglied (POST)
     */
    public function kick(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative/members');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->kickMember($this->getFarmId(), (int) $data['target_farm_id']);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative/members');
    }

    // ============================================
    // LAGER
    // ============================================

    /**
     * Zeigt Genossenschaftslager
     */
    public function warehouse(): void
    {
        $this->requireAuth();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/cooperative');
        }

        $farm = new Farm($this->getFarmId());

        $data = [
            'title' => 'Genossenschaftslager',
            'membership' => $membership,
            'warehouse' => $coopModel->getWarehouse($membership['cooperative_id']),
            'farmInventory' => $farm->getInventory(),
            'canWithdraw' => $coopModel->hasPermission($this->getFarmId(), 'manage_warehouse')
        ];

        $this->renderWithLayout('cooperative/warehouse', $data);
    }

    /**
     * Lagert ein (POST)
     */
    public function deposit(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative/warehouse');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->depositToWarehouse(
            $this->getFarmId(),
            (int) $data['product_id'],
            (int) $data['quantity']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative/warehouse');
    }

    /**
     * Entnimmt (POST)
     */
    public function withdraw(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative/warehouse');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->withdrawFromWarehouse(
            $this->getFarmId(),
            (int) $data['product_id'],
            (int) $data['quantity']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative/warehouse');
    }

    // ============================================
    // FINANZEN
    // ============================================

    /**
     * Zeigt Finanzen
     */
    public function finances(): void
    {
        $this->requireAuth();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/cooperative');
        }

        $coopDetails = $coopModel->getDetails($membership['cooperative_id']);

        $data = [
            'title' => 'Finanzen',
            'membership' => $membership,
            'coopDetails' => $coopDetails,
            'transactions' => $coopModel->getTransactions($membership['cooperative_id']),
            'canManage' => $coopModel->hasPermission($this->getFarmId(), 'manage_finances')
        ];

        $this->renderWithLayout('cooperative/finances', $data);
    }

    /**
     * Hebt Geld ab (POST)
     */
    public function withdrawMoney(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative/finances');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->withdrawMoney(
            $this->getFarmId(),
            (float) $data['amount'],
            Validator::sanitizeString($data['reason'] ?? '')
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative/finances');
    }

    // ============================================
    // FORSCHUNG
    // ============================================

    /**
     * Zeigt Forschung
     */
    public function research(): void
    {
        $this->requireAuth();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/cooperative');
        }

        // Prüfe auf abgeschlossene Forschungen
        $coopModel->checkResearchCompletion($membership['cooperative_id']);

        $coopDetails = $coopModel->getDetails($membership['cooperative_id']);

        $data = [
            'title' => 'Forschung',
            'membership' => $membership,
            'coopDetails' => $coopDetails,
            'researchTree' => $coopModel->getResearchTree($membership['cooperative_id']),
            'canManage' => $coopModel->hasPermission($this->getFarmId(), 'manage_research')
        ];

        $this->renderWithLayout('cooperative/research', $data);
    }

    /**
     * Startet Forschung (POST)
     */
    public function startResearch(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/cooperative/research');
        }

        $data = $this->getPostData();

        $coopModel = new Cooperative();
        $result = $coopModel->startResearch($this->getFarmId(), (int) $data['research_id']);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/cooperative/research');
    }

    // ============================================
    // HERAUSFORDERUNGEN
    // ============================================

    /**
     * Zeigt Herausforderungen
     */
    public function challenges(): void
    {
        $this->requireAuth();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/cooperative');
        }

        // Generiere ggf. neue Herausforderungen
        $coopModel->generateChallenges($membership['cooperative_id']);

        $challenges = $coopModel->getActiveChallenges($membership['cooperative_id']);

        // Hole Beiträge für jede Herausforderung
        foreach ($challenges as &$challenge) {
            $challenge['contributions'] = $coopModel->getChallengeContributions($challenge['id']);
        }

        $data = [
            'title' => 'Herausforderungen',
            'membership' => $membership,
            'challenges' => $challenges
        ];

        $this->renderWithLayout('cooperative/challenges', $data);
    }
}
