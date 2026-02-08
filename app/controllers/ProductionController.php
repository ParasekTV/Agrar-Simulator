<?php
/**
 * Production Controller
 *
 * Verwaltet Produktionen und deren Verwaltung.
 */
class ProductionController extends Controller
{
    /**
     * Zeigt Produktions-Übersicht
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $productionModel = new Production();
        $farm = new Farm($farmId);

        $data = [
            'title' => 'Produktionen',
            'productions' => $productionModel->getFarmProductions($farmId),
            'availableProductions' => $productionModel->getAvailableProductions($farmId),
            'categories' => $productionModel->getCategories(),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('productions/index', $data);
    }

    /**
     * Zeigt Produktions-Shop
     */
    public function shop(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $productionModel = new Production();
        $farm = new Farm($farmId);

        $data = [
            'title' => 'Produktionen kaufen',
            'availableProductions' => $productionModel->getAvailableProductions($farmId),
            'categories' => $productionModel->getCategories(),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('productions/shop', $data);
    }

    /**
     * Zeigt einzelne Produktion
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $productionModel = new Production();
        $storageModel = new Storage();

        $production = $productionModel->getFarmProduction($id, $farmId);

        if (!$production) {
            Session::setFlash('error', 'Produktion nicht gefunden', 'danger');
            $this->redirect('/productions');
        }

        // Hole Lagerbestände für Inputs
        $inputStock = [];
        foreach ($production['inputs'] as $input) {
            $inputStock[$input['product_id']] = $storageModel->getProductQuantity($farmId, $input['product_id']);
        }

        $data = [
            'title' => $production['name_de'],
            'production' => $production,
            'inputStock' => $inputStock
        ];

        $this->renderWithLayout('productions/show', $data);
    }

    /**
     * Kauft eine Produktion (POST)
     */
    public function buy(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/productions/shop');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator->required('production_id')->numeric('production_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/productions/shop');
        }

        $productionModel = new Production();
        $result = $productionModel->buyProduction($this->getFarmId(), (int) $data['production_id']);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/productions');
    }

    /**
     * Aktiviert/Deaktiviert Produktion (POST)
     */
    public function toggle(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/productions');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator->required('farm_production_id')->numeric('farm_production_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/productions');
        }

        $productionModel = new Production();
        $result = $productionModel->toggleProduction((int) $data['farm_production_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/productions');
    }

    /**
     * Startet Produktion (POST)
     */
    public function start(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/productions');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator->required('farm_production_id')->numeric('farm_production_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/productions');
        }

        $productionModel = new Production();
        $result = $productionModel->startProduction((int) $data['farm_production_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $redirectTo = isset($data['redirect']) ? $data['redirect'] : '/productions';
        $this->redirect($redirectTo);
    }

    /**
     * Sammelt Produkte ein (POST)
     */
    public function collect(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/productions');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator->required('farm_production_id')->numeric('farm_production_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/productions');
        }

        $productionModel = new Production();
        $result = $productionModel->collectProduction((int) $data['farm_production_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $redirectTo = isset($data['redirect']) ? $data['redirect'] : '/productions';
        $this->redirect($redirectTo);
    }

    /**
     * Startet kontinuierliche Produktion (POST)
     */
    public function startContinuous(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/productions');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator->required('farm_production_id')->numeric('farm_production_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/productions');
        }

        $productionModel = new Production();
        $result = $productionModel->startContinuousProduction((int) $data['farm_production_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $redirectTo = isset($data['redirect']) ? $data['redirect'] : '/productions';
        $this->redirect($redirectTo);
    }

    /**
     * Stoppt kontinuierliche Produktion (POST)
     */
    public function stopContinuous(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/productions');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator->required('farm_production_id')->numeric('farm_production_id');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/productions');
        }

        $productionModel = new Production();
        $result = $productionModel->stopContinuousProduction((int) $data['farm_production_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $redirectTo = isset($data['redirect']) ? $data['redirect'] : '/productions';
        $this->redirect($redirectTo);
    }

    /**
     * Zeigt Produktions-Logs
     */
    public function logs(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $productionModel = new Production();

        $data = [
            'title' => 'Produktions-Historie',
            'logs' => $productionModel->getProductionLogs($farmId, 100)
        ];

        $this->renderWithLayout('productions/logs', $data);
    }

    /**
     * API: Gibt Produktionen zurück
     */
    public function listApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $productionModel = new Production();
        $productions = $productionModel->getFarmProductions($this->getFarmId());

        return $this->json(['productions' => $productions]);
    }

    /**
     * API: Gibt einzelne Produktion zurück
     */
    public function getApi(int $id): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $productionModel = new Production();
        $production = $productionModel->getFarmProduction($id, $this->getFarmId());

        if (!$production) {
            return $this->jsonError('Produktion nicht gefunden', 404);
        }

        return $this->json(['production' => $production]);
    }

    /**
     * API: Startet Produktion
     */
    public function startApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmProductionId'])) {
            return $this->jsonError('farmProductionId erforderlich');
        }

        $productionModel = new Production();
        $result = $productionModel->startProduction((int) $data['farmProductionId'], $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['ready_at' => $result['ready_at'] ?? null])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Sammelt Produktion ein
     */
    public function collectApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmProductionId'])) {
            return $this->jsonError('farmProductionId erforderlich');
        }

        $productionModel = new Production();
        $result = $productionModel->collectProduction((int) $data['farmProductionId'], $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['items' => $result['items'] ?? []])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Toggle Produktion
     */
    public function toggleApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmProductionId'])) {
            return $this->jsonError('farmProductionId erforderlich');
        }

        $productionModel = new Production();
        $result = $productionModel->toggleProduction((int) $data['farmProductionId'], $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['is_active' => $result['is_active'] ?? false])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Startet kontinuierliche Produktion
     */
    public function startContinuousApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmProductionId'])) {
            return $this->jsonError('farmProductionId erforderlich');
        }

        $productionModel = new Production();
        $result = $productionModel->startContinuousProduction((int) $data['farmProductionId'], $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['efficiency' => $result['efficiency'] ?? 100])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Stoppt kontinuierliche Produktion
     */
    public function stopContinuousApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmProductionId'])) {
            return $this->jsonError('farmProductionId erforderlich');
        }

        $productionModel = new Production();
        $result = $productionModel->stopContinuousProduction((int) $data['farmProductionId'], $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['cycles_completed' => $result['cycles_completed'] ?? 0])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Gibt Produktions-Logs zurück
     */
    public function logsApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $limit = (int) ($_GET['limit'] ?? 50);
        $limit = min(100, max(1, $limit));

        $productionModel = new Production();
        $logs = $productionModel->getProductionLogs($this->getFarmId(), $limit);

        return $this->json(['logs' => $logs]);
    }
}
