<?php
/**
 * Field Controller
 *
 * Verwaltet Felder, Pflanzen und Ernten.
 */
class FieldController extends Controller
{
    /**
     * Zeigt Feld-Übersicht
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);
        $fieldModel = new Field();

        $data = [
            'title' => 'Felder',
            'fields' => $farm->getFields(),
            'availableCrops' => $fieldModel->getAvailableCrops($farmId),
            'availableFertilizers' => $fieldModel->getAvailableFertilizers($farmId),
            'availableLimeTypes' => $fieldModel->getAvailableLimeTypes($farmId),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('fields/index', $data);
    }

    /**
     * Zeigt ein einzelnes Feld
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $fieldModel = new Field();

        $field = $fieldModel->getField($id, $farmId);

        if (!$field) {
            Session::setFlash('error', 'Feld nicht gefunden', 'danger');
            $this->redirect('/fields');
        }

        $data = [
            'title' => "Feld #{$id}",
            'field' => $field,
            'availableCrops' => $fieldModel->getAvailableCrops($farmId),
            'availableFertilizers' => $fieldModel->getAvailableFertilizers($farmId),
            'availableLimeTypes' => $fieldModel->getAvailableLimeTypes($farmId)
        ];

        $this->renderWithLayout('fields/show', $data);
    }

    /**
     * Pflanzt eine Feldfrucht (POST)
     */
    public function plant(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('field_id')
            ->required('crop_id')
            ->numeric('field_id')
            ->numeric('crop_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/fields');
        }

        $fieldModel = new Field();
        $result = $fieldModel->plantCrop(
            (int) $data['field_id'],
            (int) $data['crop_id'],
            $this->getFarmId()
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/fields');
    }

    /**
     * Erntet ein Feld (POST)
     */
    public function harvest(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator->required('field_id')->numeric('field_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/fields');
        }

        $fieldModel = new Field();
        $result = $fieldModel->harvest((int) $data['field_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        // Aktualisiere Herausforderungsfortschritt
        if ($result['success']) {
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'production', $result['yield']);
        }

        $this->redirect('/fields');
    }

    /**
     * Kauft ein neues Feld (POST)
     */
    public function buy(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('size')
            ->numeric('size')
            ->min('size', 1, 'Mindestgröße: 1 Hektar')
            ->max('size', 10, 'Maximalgröße: 10 Hektar');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/fields');
        }

        $fieldModel = new Field();
        $result = $fieldModel->buyField($this->getFarmId(), (float) $data['size']);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/fields');
    }

    /**
     * Düngt ein Feld mit Basis-Dünger (POST) - Legacy
     */
    public function fertilize(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields');
        }

        $data = $this->getPostData();

        $fieldModel = new Field();
        $result = $fieldModel->fertilize((int) $data['field_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/fields');
    }

    /**
     * Wendet erweiterten Dünger an (POST)
     */
    public function applyFertilizer(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('field_id')
            ->required('fertilizer_type_id')
            ->numeric('field_id')
            ->numeric('fertilizer_type_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/fields');
        }

        $fieldModel = new Field();
        $result = $fieldModel->applyFertilizer(
            (int) $data['field_id'],
            (int) $data['fertilizer_type_id'],
            $this->getFarmId()
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/fields');
    }

    /**
     * Wendet Kalk an (POST)
     */
    public function lime(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('field_id')
            ->required('lime_type_id')
            ->numeric('field_id')
            ->numeric('lime_type_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/fields');
        }

        $fieldModel = new Field();
        $result = $fieldModel->applyLime(
            (int) $data['field_id'],
            (int) $data['lime_type_id'],
            $this->getFarmId()
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/fields');
    }

    /**
     * API: Pflanzt eine Feldfrucht
     */
    public function plantApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['fieldId']) || empty($data['cropId'])) {
            return $this->jsonError('fieldId und cropId erforderlich');
        }

        $fieldModel = new Field();
        $result = $fieldModel->plantCrop(
            (int) $data['fieldId'],
            (int) $data['cropId'],
            $this->getFarmId()
        );

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['harvest_at' => $result['harvest_at'] ?? null])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Erntet ein Feld
     */
    public function harvestApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['fieldId'])) {
            return $this->jsonError('fieldId erforderlich');
        }

        $fieldModel = new Field();
        $result = $fieldModel->harvest((int) $data['fieldId'], $this->getFarmId());

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'production', $result['yield']);

            return $this->jsonSuccess($result['message'], [
                'yield' => $result['yield'],
                'value' => $result['value'],
                'crop_name' => $result['crop_name']
            ]);
        }

        return $this->jsonError($result['message']);
    }

    /**
     * API: Gibt ein einzelnes Feld zurück
     */
    public function getApi(int $id): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $fieldModel = new Field();
        $field = $fieldModel->getField($id, $this->getFarmId());

        if (!$field) {
            return $this->jsonError('Feld nicht gefunden', 404);
        }

        return $this->json(['field' => $field]);
    }

    /**
     * API: Kauft ein neues Feld
     */
    public function buyApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();
        $size = (float) ($data['size'] ?? 1);

        if ($size < 1 || $size > 10) {
            return $this->jsonError('Feldgröße muss zwischen 1 und 10 Hektar liegen');
        }

        $fieldModel = new Field();
        $result = $fieldModel->buyField($this->getFarmId(), $size);

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['field_id' => $result['field_id'] ?? null])
            : $this->jsonError($result['message']);
    }
}
