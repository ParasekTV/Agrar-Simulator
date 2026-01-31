<?php
/**
 * Vehicle Controller
 *
 * Verwaltet Fahrzeuge und Geraete.
 */
class VehicleController extends Controller
{
    /**
     * Zeigt Fahrzeug-Uebersicht
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);
        $vehicleModel = new Vehicle();

        $data = [
            'title' => 'Fahrzeuge',
            'farmVehicles' => $vehicleModel->getFarmVehicles($farmId),
            'availableVehicles' => $vehicleModel->getAvailableVehicles($farmId),
            'efficiencyBonus' => $vehicleModel->getTotalEfficiencyBonus($farmId),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('vehicles/index', $data);
    }

    /**
     * Kauft ein Fahrzeug (POST)
     */
    public function buy(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/vehicles');
        }

        $data = $this->getPostData();

        $vehicleModel = new Vehicle();
        $result = $vehicleModel->buy((int) $data['vehicle_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/vehicles');
    }

    /**
     * Verkauft ein Fahrzeug (POST)
     */
    public function sell(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/vehicles');
        }

        $data = $this->getPostData();

        $vehicleModel = new Vehicle();
        $result = $vehicleModel->sell((int) $data['farm_vehicle_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/vehicles');
    }

    /**
     * Repariert ein Fahrzeug (POST)
     */
    public function repair(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/vehicles');
        }

        $data = $this->getPostData();

        $vehicleModel = new Vehicle();
        $result = $vehicleModel->repair((int) $data['farm_vehicle_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/vehicles');
    }

    /**
     * API: Kauft ein Fahrzeug
     */
    public function buyApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['vehicleId'])) {
            return $this->jsonError('vehicleId erforderlich');
        }

        $vehicleModel = new Vehicle();
        $result = $vehicleModel->buy((int) $data['vehicleId'], $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Verkauft ein Fahrzeug
     */
    public function sellApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmVehicleId'])) {
            return $this->jsonError('farmVehicleId erforderlich');
        }

        $vehicleModel = new Vehicle();
        $result = $vehicleModel->sell((int) $data['farmVehicleId'], $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['income' => $result['income'] ?? 0])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Repariert ein Fahrzeug
     */
    public function repairApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['farmVehicleId'])) {
            return $this->jsonError('farmVehicleId erforderlich');
        }

        $vehicleModel = new Vehicle();
        $result = $vehicleModel->repair((int) $data['farmVehicleId'], $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['cost' => $result['cost'] ?? 0])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Gibt verfuegbare Fahrzeuge zum Kauf zurueck
     */
    public function availableApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $vehicleModel = new Vehicle();

        return $this->json([
            'vehicles' => $vehicleModel->getAvailableVehicles($this->getFarmId())
        ]);
    }
}
