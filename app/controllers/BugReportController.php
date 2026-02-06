<?php
/**
 * BugReport Controller
 *
 * Verwaltet Bug-Meldungen von Benutzern.
 */
class BugReportController extends Controller
{
    /**
     * Zeigt das Bug-Melden Formular
     */
    public function index(): void
    {
        $this->requireAuth();

        $this->renderWithLayout('bugreport/index', [
            'title' => 'Bug melden'
        ]);
    }

    /**
     * Verarbeitet eine Bug-Meldung
     */
    public function submit(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen. Bitte erneut versuchen.', 'danger');
            $this->redirect('/bugreport');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('title', 'Titel erforderlich')
            ->minLength('title', 5, 'Titel muss mindestens 5 Zeichen lang sein')
            ->maxLength('title', 200, 'Titel darf maximal 200 Zeichen lang sein')
            ->required('description', 'Beschreibung erforderlich')
            ->minLength('description', 20, 'Beschreibung muss mindestens 20 Zeichen lang sein');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/bugreport');
        }

        $bugReport = new BugReport();
        $result = $bugReport->create(
            Session::getUserId(),
            Session::getFarmId(),
            $data['title'],
            $data['description']
        );

        if ($result['success']) {
            Session::setFlash('success', $result['message'], 'success');
        } else {
            Session::setFlash('error', $result['message'], 'danger');
        }

        $this->redirect('/bugreport');
    }
}
