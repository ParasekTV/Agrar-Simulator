<?php
/**
 * News Controller
 *
 * Verwaltet Forum/Zeitung.
 */
class NewsController extends Controller
{
    /**
     * Zeigt Beitrags-Übersicht
     */
    public function index(): void
    {
        $this->requireAuth();

        $page = (int) ($this->getQueryParam('page', 1));
        $category = $this->getQueryParam('category');

        $newsModel = new News();
        $result = $newsModel->getPosts($category, $page);

        $data = [
            'title' => 'Bauernzeitung',
            'posts' => $result['posts'],
            'pagination' => [
                'page' => $result['page'],
                'totalPages' => $result['total_pages'],
                'total' => $result['total']
            ],
            'popularPosts' => $newsModel->getPopular(5),
            'category' => $category,
            'categories' => [
                'changelog' => 'Changelog',
                'admin_news' => 'News',
                'announcement' => 'Ankündigungen',
                'market' => 'Markt',
                'cooperative' => 'Genossenschaften',
                'tips' => 'Tipps & Tricks',
                'offtopic' => 'Sonstiges'
            ]
        ];

        $this->renderWithLayout('news/index', $data);
    }

    /**
     * Zeigt einen einzelnen Beitrag
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $newsModel = new News();
        $post = $newsModel->getPost($id);

        if (!$post) {
            Session::setFlash('error', 'Beitrag nicht gefunden', 'danger');
            $this->redirect('/news');
        }

        $data = [
            'title' => $post['title'],
            'post' => $post
        ];

        $this->renderWithLayout('news/show', $data);
    }

    /**
     * Zeigt Formular für neuen Beitrag
     */
    public function create(): void
    {
        $this->requireAuth();

        $data = [
            'title' => 'Neuer Beitrag',
            'categories' => [
                'changelog' => 'Changelog',
                'admin_news' => 'News',
                'announcement' => 'Ankündigungen',
                'market' => 'Markt',
                'cooperative' => 'Genossenschaften',
                'tips' => 'Tipps & Tricks',
                'offtopic' => 'Sonstiges'
            ]
        ];

        $this->renderWithLayout('news/create', $data);
    }

    /**
     * Speichert neuen Beitrag (POST)
     */
    public function store(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/news/create');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('title', 'Titel erforderlich')
            ->minLength('title', 5, 'Titel muss mindestens 5 Zeichen lang sein')
            ->maxLength('title', 200, 'Titel darf maximal 200 Zeichen lang sein')
            ->required('content', 'Inhalt erforderlich')
            ->minLength('content', 20, 'Inhalt muss mindestens 20 Zeichen lang sein')
            ->required('category', 'Kategorie erforderlich');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/news/create');
        }

        $newsModel = new News();
        $result = $newsModel->createPost(
            $this->getFarmId(),
            $data['title'],
            $data['content'],
            $data['category']
        );

        if ($result['success']) {
            Session::setFlash('success', $result['message'], 'success');
            $this->redirect('/news/' . $result['post_id']);
        } else {
            Session::setFlash('error', $result['message'], 'danger');
            $this->redirect('/news/create');
        }
    }

    /**
     * Fügt Kommentar hinzu (POST)
     */
    public function comment(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/news');
        }

        $data = $this->getPostData();

        $newsModel = new News();
        $result = $newsModel->createComment(
            (int) $data['post_id'],
            $this->getFarmId(),
            $data['content']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/news/' . $data['post_id']);
    }

    /**
     * Liked einen Beitrag (POST)
     */
    public function like(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/news');
        }

        $data = $this->getPostData();

        $newsModel = new News();
        $result = $newsModel->likePost((int) $data['post_id'], $this->getFarmId());

        Session::setFlash('success', $result['message'], 'success');
        $this->redirect('/news/' . $data['post_id']);
    }

    /**
     * Löscht einen Beitrag (POST)
     */
    public function delete(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/news');
        }

        $data = $this->getPostData();

        $newsModel = new News();
        $result = $newsModel->deletePost((int) $data['post_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/news');
    }

    /**
     * Suche
     */
    public function search(): void
    {
        $this->requireAuth();

        $query = $this->getQueryParam('q', '');
        $page = (int) ($this->getQueryParam('page', 1));

        $newsModel = new News();
        $result = $newsModel->search($query, $page);

        $data = [
            'title' => 'Suche: ' . $query,
            'posts' => $result['posts'],
            'pagination' => [
                'page' => $result['page'],
                'totalPages' => $result['total_pages'],
                'total' => $result['total']
            ],
            'query' => $query
        ];

        $this->renderWithLayout('news/search', $data);
    }

    /**
     * API: Gibt Beiträge zurück
     */
    public function postsApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $page = (int) ($this->getQueryParam('page', 1));
        $category = $this->getQueryParam('category');

        $newsModel = new News();

        return $this->json($newsModel->getPosts($category, $page));
    }

    /**
     * API: Erstellt einen Beitrag
     */
    public function createApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['title']) || empty($data['content']) || empty($data['category'])) {
            return $this->jsonError('title, content und category erforderlich');
        }

        $newsModel = new News();
        $result = $newsModel->createPost(
            $this->getFarmId(),
            $data['title'],
            $data['content'],
            $data['category']
        );

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['post_id' => $result['post_id'] ?? null])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Liked einen Beitrag
     */
    public function likeApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['postId'])) {
            return $this->jsonError('postId erforderlich');
        }

        $newsModel = new News();
        $result = $newsModel->likePost((int) $data['postId'], $this->getFarmId());

        return $this->json($result);
    }
}
