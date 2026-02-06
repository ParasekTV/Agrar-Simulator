<?php
/**
 * Page Controller
 *
 * Verwaltet statische Seiten wie Impressum und Datenschutz.
 */
class PageController extends Controller
{
    /**
     * Impressum
     */
    public function impressum(): void
    {
        $this->renderWithLayout('pages/impressum', [
            'title' => 'Impressum'
        ]);
    }

    /**
     * Datenschutzerklärung
     */
    public function datenschutz(): void
    {
        $this->renderWithLayout('pages/datenschutz', [
            'title' => 'Datenschutzerklärung'
        ]);
    }

    /**
     * Spielregeln
     */
    public function spielregeln(): void
    {
        $this->renderWithLayout('pages/spielregeln', [
            'title' => 'Spielregeln'
        ]);
    }
}
