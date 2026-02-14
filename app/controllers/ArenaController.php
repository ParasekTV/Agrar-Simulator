<?php
/**
 * Arena Controller
 *
 * Verwaltet den FSL-inspirierten Genossenschafts-Wettkampf.
 */
class ArenaController extends Controller
{
    /**
     * Zeigt Arena-Hauptseite
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $coopModel = new Cooperative();
        $arenaModel = new Arena();

        $membership = $coopModel->getMembership($farmId);

        if (!$membership) {
            Session::setFlash('error', 'Du musst einer Genossenschaft angehören, um an der Arena teilzunehmen.', 'warning');
            $this->redirect('/cooperative');
            return;
        }

        $coopId = $membership['cooperative_id'];

        $data = [
            'title' => 'Wettkampf-Arena',
            'membership' => $membership,
            'cooperatives' => $coopModel->getAll(),
            'pendingChallenges' => $arenaModel->getPendingChallenges($coopId),
            'activeMatches' => $arenaModel->getActiveMatches($coopId),
            'rankings' => $arenaModel->getArenaRankings(10)
        ];

        $this->renderWithLayout('arena/index', $data);
    }

    /**
     * Zeigt Rangliste
     */
    public function rankings(): void
    {
        $this->requireAuth();

        $arenaModel = new Arena();

        $data = [
            'title' => 'Arena-Rangliste',
            'rankings' => $arenaModel->getArenaRankings(100)
        ];

        $this->renderWithLayout('arena/rankings', $data);
    }

    /**
     * Zeigt Match-Details
     */
    public function match(int $id): void
    {
        $this->requireAuth();

        $arenaModel = new Arena();
        $match = $arenaModel->getMatch($id);

        if (!$match) {
            Session::setFlash('error', 'Match nicht gefunden', 'danger');
            $this->redirect('/arena');
            return;
        }

        $data = [
            'title' => 'Match #' . $id,
            'match' => $match,
            'pickBanState' => $arenaModel->getPickBanState($id),
            'participants' => $arenaModel->getParticipants($id),
            'availableVehicles' => $arenaModel->getAvailableVehicles($id)
        ];

        $this->renderWithLayout('arena/match', $data);
    }

    /**
     * Herausforderung senden (POST)
     */
    public function challenge(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $farmId = $this->getFarmId();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($farmId);

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/arena');
            return;
        }

        $arenaModel = new Arena();
        $result = $arenaModel->challengeCooperative(
            $membership['cooperative_id'],
            (int) $data['defender_coop_id']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/arena');
    }

    /**
     * Herausforderung annehmen (POST)
     */
    public function accept(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $farmId = $this->getFarmId();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($farmId);

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/arena');
            return;
        }

        $arenaModel = new Arena();
        $result = $arenaModel->acceptChallenge(
            (int) $data['match_id'],
            $membership['cooperative_id']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        if ($result['success']) {
            $this->redirect('/arena/match/' . $data['match_id']);
        } else {
            $this->redirect('/arena');
        }
    }

    /**
     * Herausforderung ablehnen (POST)
     */
    public function decline(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $farmId = $this->getFarmId();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($farmId);

        if (!$membership) {
            Session::setFlash('error', 'Du bist in keiner Genossenschaft', 'danger');
            $this->redirect('/arena');
            return;
        }

        $arenaModel = new Arena();
        $result = $arenaModel->declineChallenge(
            (int) $data['match_id'],
            $membership['cooperative_id']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/arena');
    }

    /**
     * Fahrzeug picken (POST)
     */
    public function pick(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $farmId = $this->getFarmId();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($farmId);

        $arenaModel = new Arena();
        $result = $arenaModel->pickVehicle(
            (int) $data['match_id'],
            $membership['cooperative_id'],
            (int) $data['vehicle_id']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/arena/match/' . $data['match_id']);
    }

    /**
     * Fahrzeug bannen (POST)
     */
    public function ban(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $farmId = $this->getFarmId();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($farmId);

        $arenaModel = new Arena();
        $result = $arenaModel->banVehicle(
            (int) $data['match_id'],
            $membership['cooperative_id'],
            (int) $data['vehicle_id']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/arena/match/' . $data['match_id']);
    }

    /**
     * Rolle zuweisen (POST)
     */
    public function assignRole(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $farmId = $this->getFarmId();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($farmId);

        $arenaModel = new Arena();
        $result = $arenaModel->assignRole(
            (int) $data['match_id'],
            $farmId,
            $data['role'],
            $membership['cooperative_id']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/arena/match/' . $data['match_id']);
    }

    /**
     * Bereit melden (POST)
     */
    public function ready(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $farmId = $this->getFarmId();

        $arenaModel = new Arena();
        $result = $arenaModel->setParticipantReady(
            (int) $data['match_id'],
            $farmId
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/arena/match/' . $data['match_id']);
    }

    /**
     * Match starten (POST)
     */
    public function start(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/arena');
        }

        $data = $this->getPostData();

        $arenaModel = new Arena();
        $result = $arenaModel->startMatch((int) $data['match_id']);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/arena/match/' . $data['match_id']);
    }
}
