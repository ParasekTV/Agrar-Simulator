<?php
/**
 * Animal Controller
 *
 * Verwaltet Tiere, Fütterung und Produktsammlung.
 */
class AnimalController extends Controller
{
    /**
     * Zeigt Tier-Übersicht
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);
        $animalModel = new Animal();

        $data = [
            'title' => 'Tiere',
            'farmAnimals' => $animalModel->getFarmAnimalsWithStatus($farmId),
            'availableAnimals' => $animalModel->getAvailableAnimals($farmId),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('animals/index', $data);
    }

    /**
     * Kauft Tiere (POST)
     */
    public function buy(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/animals');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('animal_id')
            ->numeric('animal_id')
            ->required('quantity')
            ->numeric('quantity')
            ->min('quantity', 1, 'Mindestens 1 Tier');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/animals');
        }

        $animalModel = new Animal();
        $result = $animalModel->buy(
            (int) $data['animal_id'],
            (int) $data['quantity'],
            $this->getFarmId()
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/animals');
    }

    /**
     * Füttert Tiere (POST)
     */
    public function feed(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/animals');
        }

        $data = $this->getPostData();

        $animalModel = new Animal();
        $result = $animalModel->feed(
            (int) $data['farm_animal_id'],
            $this->getFarmId()
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/animals');
    }

    /**
     * Sammelt Tierprodukte (POST)
     */
    public function collect(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/animals');
        }

        $data = $this->getPostData();

        $animalModel = new Animal();
        $result = $animalModel->collect(
            (int) $data['farm_animal_id'],
            $this->getFarmId()
        );

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'production', $result['quantity']);
        }

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/animals');
    }

    /**
     * Verkauft Tiere (POST)
     */
    public function sell(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/animals');
        }

        $data = $this->getPostData();

        $animalModel = new Animal();
        $result = $animalModel->sell(
            (int) $data['farm_animal_id'],
            (int) $data['quantity'],
            $this->getFarmId()
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/animals');
    }

    /**
     * API: Kauft Tiere
     */
    public function buyApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['animalId']) || empty($data['quantity'])) {
            return $this->jsonError('animalId und quantity erforderlich');
        }

        $animalModel = new Animal();
        $result = $animalModel->buy(
            (int) $data['animalId'],
            (int) $data['quantity'],
            $this->getFarmId()
        );

        return $result['success']
            ? $this->jsonSuccess($result['message'])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Füttert Tiere
     */
    public function feedApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmAnimalId'])) {
            return $this->jsonError('farmAnimalId erforderlich');
        }

        $animalModel = new Animal();
        $result = $animalModel->feed(
            (int) $data['farmAnimalId'],
            $this->getFarmId()
        );

        return $result['success']
            ? $this->jsonSuccess($result['message'], [
                'new_health' => $result['new_health'] ?? null,
                'new_happiness' => $result['new_happiness'] ?? null
            ])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Sammelt Tierprodukte
     */
    public function collectApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmAnimalId'])) {
            return $this->jsonError('farmAnimalId erforderlich');
        }

        $animalModel = new Animal();
        $result = $animalModel->collect(
            (int) $data['farmAnimalId'],
            $this->getFarmId()
        );

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'production', $result['quantity']);

            return $this->jsonSuccess($result['message'], [
                'quantity' => $result['quantity'],
                'product' => $result['product'],
                'value' => $result['value']
            ]);
        }

        return $this->jsonError($result['message']);
    }

    /**
     * API: Gibt verfügbare Tiere zum Kauf zurück
     */
    public function availableApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $animalModel = new Animal();

        return $this->json([
            'animals' => $animalModel->getAvailableAnimals($this->getFarmId())
        ]);
    }
}
