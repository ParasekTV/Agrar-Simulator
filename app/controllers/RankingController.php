<?php
/**
 * Ranking Controller
 *
 * Verwaltet Ranglisten und Herausforderungen.
 */
class RankingController extends Controller
{
    /**
     * Zeigt globale Rangliste
     */
    public function index(): void
    {
        $this->requireAuth();

        $page = (int) ($this->getQueryParam('page', 1));

        $rankingModel = new Ranking();
        $result = $rankingModel->getGlobalRanking($page);
        $myRank = $rankingModel->getFarmRank($this->getFarmId());

        $data = [
            'title' => 'Rangliste',
            'rankings' => $result['rankings'],
            'pagination' => [
                'page' => $result['page'],
                'totalPages' => $result['total_pages'],
                'total' => $result['total']
            ],
            'myRank' => $myRank,
            'challenges' => $rankingModel->getChallengeProgress($this->getFarmId()),
            'stats' => $rankingModel->getDashboardStats()
        ];

        $this->renderWithLayout('rankings/index', $data);
    }

    /**
     * Zeigt Genossenschafts-Rangliste
     */
    public function cooperatives(): void
    {
        $this->requireAuth();

        $rankingModel = new Ranking();

        $data = [
            'title' => 'Genossenschafts-Rangliste',
            'rankings' => $rankingModel->getCooperativeRanking()
        ];

        $this->renderWithLayout('rankings/cooperatives', $data);
    }

    /**
     * Zeigt woechentliche Herausforderungen
     */
    public function challenges(): void
    {
        $this->requireAuth();

        $rankingModel = new Ranking();
        $challenges = $rankingModel->getChallengeProgress($this->getFarmId());

        // Hole Bestenlisten fuer jede Herausforderung
        foreach ($challenges as &$challenge) {
            $challenge['leaderboard'] = $rankingModel->getChallengeLeaderboard($challenge['id'], 10);
        }

        $data = [
            'title' => 'Woechentliche Herausforderungen',
            'challenges' => $challenges
        ];

        $this->renderWithLayout('rankings/challenges', $data);
    }

    /**
     * API: Gibt globale Rangliste zurueck
     */
    public function globalApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $page = (int) ($this->getQueryParam('page', 1));

        $rankingModel = new Ranking();

        return $this->json($rankingModel->getGlobalRanking($page));
    }

    /**
     * API: Gibt Genossenschafts-Rangliste zurueck
     */
    public function cooperativesApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $rankingModel = new Ranking();

        return $this->json([
            'rankings' => $rankingModel->getCooperativeRanking()
        ]);
    }

    /**
     * API: Gibt woechentliche Herausforderungen zurueck
     */
    public function weeklyApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $rankingModel = new Ranking();

        return $this->json([
            'challenges' => $rankingModel->getChallengeProgress($this->getFarmId())
        ]);
    }
}
