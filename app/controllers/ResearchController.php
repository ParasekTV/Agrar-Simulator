<?php
/**
 * Research Controller
 *
 * Verwaltet den Forschungsbaum.
 */
class ResearchController extends Controller
{
    /**
     * Zeigt den Forschungsbaum
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);
        $researchModel = new Research();

        $data = [
            'title' => 'Forschung',
            'researchTree' => $researchModel->getTree($farmId),
            'activeResearch' => $researchModel->getActive($farmId),
            'remainingTime' => $researchModel->getRemainingTime($farmId),
            'completedResearch' => $researchModel->getCompleted($farmId),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('research/index', $data);
    }

    /**
     * Startet eine Forschung (POST)
     */
    public function start(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/research');
        }

        $data = $this->getPostData();

        $researchModel = new Research();
        $result = $researchModel->start((int) $data['research_id'], $this->getFarmId());

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'research', 1);
        }

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/research');
    }

    /**
     * Bricht eine Forschung ab (POST)
     */
    public function cancel(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/research');
        }

        $researchModel = new Research();
        $result = $researchModel->cancel($this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/research');
    }

    /**
     * API: Gibt den Forschungsbaum zurueck
     */
    public function treeApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $researchModel = new Research();

        return $this->json([
            'tree' => $researchModel->getTree($this->getFarmId())
        ]);
    }

    /**
     * API: Startet eine Forschung
     */
    public function startApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['researchId'])) {
            return $this->jsonError('researchId erforderlich');
        }

        $researchModel = new Research();
        $result = $researchModel->start((int) $data['researchId'], $this->getFarmId());

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'research', 1);

            return $this->jsonSuccess($result['message'], [
                'completed_at' => $result['completed_at'] ?? null
            ]);
        }

        return $this->jsonError($result['message']);
    }

    /**
     * API: Gibt den aktuellen Forschungsfortschritt zurueck
     */
    public function progressApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $researchModel = new Research();
        $active = $researchModel->getActive($this->getFarmId());
        $remaining = $researchModel->getRemainingTime($this->getFarmId());

        return $this->json([
            'active' => $active,
            'remaining_time' => $remaining
        ]);
    }

    /**
     * API: Schliesst Forschung ab (manueller Check)
     */
    public function completeApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        // Pruefe ob Forschung fertig ist
        $count = Research::completeResearch();

        if ($count > 0) {
            return $this->jsonSuccess('Forschung abgeschlossen!');
        }

        return $this->json(['success' => false, 'message' => 'Keine Forschung bereit']);
    }
}
