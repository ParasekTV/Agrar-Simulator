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
}
