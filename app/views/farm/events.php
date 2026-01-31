<div class="events-page">
    <div class="page-header">
        <h1>Ereignisse</h1>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#128196;</span>
            <h3>Keine Ereignisse</h3>
            <p>Hier werden alle Aktivitaeten auf deiner Farm protokolliert.</p>
        </div>
    <?php else: ?>
        <div class="events-timeline">
            <?php
            $currentDate = '';
            foreach ($events as $event):
                $eventDate = date('d.m.Y', strtotime($event['created_at']));
                if ($eventDate !== $currentDate):
                    $currentDate = $eventDate;
            ?>
                <div class="timeline-date"><?= $eventDate ?></div>
            <?php endif; ?>

                <div class="timeline-event event-<?= $event['event_type'] ?>">
                    <div class="event-icon">
                        <?php
                        $icons = [
                            'harvest' => '&#127806;',
                            'sale' => '&#128176;',
                            'purchase' => '&#128722;',
                            'research' => '&#128300;',
                            'building' => '&#127968;',
                            'level_up' => '&#11088;',
                            'points' => '&#127942;'
                        ];
                        echo $icons[$event['event_type']] ?? '&#128196;';
                        ?>
                    </div>
                    <div class="event-details">
                        <span class="event-time"><?= date('H:i', strtotime($event['created_at'])) ?></span>
                        <span class="event-description"><?= htmlspecialchars($event['description']) ?></span>
                        <div class="event-values">
                            <?php if ($event['points_earned'] > 0): ?>
                                <span class="event-points">+<?= $event['points_earned'] ?> Punkte</span>
                            <?php endif; ?>
                            <?php if ($event['money_change'] != 0): ?>
                                <span class="event-money <?= $event['money_change'] > 0 ? 'positive' : 'negative' ?>">
                                    <?= $event['money_change'] > 0 ? '+' : '' ?><?= number_format($event['money_change'], 0, ',', '.') ?> T
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= BASE_URL ?>/events?page=<?= $page - 1 ?>" class="pagination-link">&laquo;</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="<?= BASE_URL ?>/events?page=<?= $i ?>"
                       class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= BASE_URL ?>/events?page=<?= $page + 1 ?>" class="pagination-link">&raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.events-timeline {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}
.timeline-date {
    font-weight: 600;
    color: var(--color-primary);
    padding: 0.5rem 0;
    border-bottom: 2px solid var(--color-primary);
    margin-bottom: 1rem;
    margin-top: 1rem;
}
.timeline-date:first-child {
    margin-top: 0;
}
.timeline-event {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--color-gray-200);
}
.timeline-event:last-child {
    border-bottom: none;
}
.event-icon {
    font-size: 1.5rem;
    width: 40px;
    text-align: center;
}
.event-details {
    flex: 1;
}
.event-time {
    font-size: 0.85rem;
    color: var(--color-gray-500);
    display: block;
    margin-bottom: 0.25rem;
}
.event-description {
    display: block;
    margin-bottom: 0.25rem;
}
.event-values {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
}
.event-points {
    color: var(--color-primary);
    font-weight: 500;
}
.event-money {
    font-weight: 500;
}
.event-money.positive {
    color: var(--color-success);
}
.event-money.negative {
    color: var(--color-danger);
}
</style>
