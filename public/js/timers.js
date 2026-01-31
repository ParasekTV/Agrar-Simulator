/**
 * Agrar Simulator - Timer System
 */

class GameTimers {
    constructor() {
        this.updateInterval = 60000; // 1 Minute
        this.init();
    }

    init() {
        this.updateAllTimers();
        setInterval(() => this.updateAllTimers(), this.updateInterval);

        // Schnellere Updates fuer letzte Minuten
        setInterval(() => this.updateUrgentTimers(), 10000);
    }

    updateAllTimers() {
        this.updateFieldTimers();
        this.updateResearchTimers();
        this.updateAnimalTimers();
    }

    updateUrgentTimers() {
        // Nur Timer die bald ablaufen
        document.querySelectorAll('.field-timer, .research-timer, .animal-timer').forEach(timer => {
            const targetTime = this.getTargetTime(timer);
            if (targetTime) {
                const diff = targetTime - Date.now();
                if (diff > 0 && diff < 600000) { // < 10 Minuten
                    this.updateTimerDisplay(timer, diff);
                }
            }
        });
    }

    getTargetTime(timer) {
        const harvestTime = timer.dataset.harvestTime;
        const completeTime = timer.dataset.completeTime;
        const collectionTime = timer.dataset.collectionTime;

        const timeString = harvestTime || completeTime || collectionTime;
        if (timeString) {
            return new Date(timeString).getTime();
        }
        return null;
    }

    updateFieldTimers() {
        document.querySelectorAll('.field-timer').forEach(timer => {
            const harvestTime = new Date(timer.dataset.harvestTime);
            const now = new Date();
            const diff = harvestTime - now;

            if (diff <= 0) {
                timer.textContent = 'Bereit zur Ernte!';
                timer.classList.add('ready');

                const field = timer.closest('.field-card, .field-mini');
                if (field) {
                    field.classList.remove('field-growing');
                    field.classList.add('field-ready');

                    // Zeige Harvest-Button falls versteckt
                    const harvestForm = field.querySelector('.harvest-form');
                    if (harvestForm) harvestForm.style.display = 'block';
                }
            } else {
                this.updateTimerDisplay(timer, diff);
            }
        });
    }

    updateResearchTimers() {
        document.querySelectorAll('.research-timer').forEach(timer => {
            const completeTime = new Date(timer.dataset.completeTime);
            const now = new Date();
            const diff = completeTime - now;

            if (diff <= 0) {
                timer.textContent = 'Abgeschlossen!';
                timer.classList.add('completed');

                // Benachrichtigung
                if (Notification.permission === 'granted') {
                    new Notification('Forschung abgeschlossen!', {
                        body: 'Deine Forschung ist fertig. Lade die Seite neu.',
                        icon: '/farming-simulator/public/images/icon.png'
                    });
                }
            } else {
                this.updateTimerDisplay(timer, diff);
            }
        });
    }

    updateAnimalTimers() {
        document.querySelectorAll('.animal-timer').forEach(timer => {
            const collectionTime = new Date(timer.dataset.collectionTime);
            const now = new Date();
            const diff = collectionTime - now;

            if (diff <= 0) {
                timer.textContent = 'Bereit zum Sammeln!';
                timer.classList.add('ready');
            } else {
                this.updateTimerDisplay(timer, diff);
            }
        });
    }

    updateTimerDisplay(timer, diff) {
        timer.textContent = this.formatTime(diff);
    }

    formatTime(milliseconds) {
        if (milliseconds < 0) return 'Bereit!';

        const seconds = Math.floor(milliseconds / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) {
            return `${days}T ${hours % 24}h`;
        }
        if (hours > 0) {
            return `${hours}h ${minutes % 60}m`;
        }
        if (minutes > 0) {
            return `${minutes}m ${seconds % 60}s`;
        }
        return `${seconds}s`;
    }
}

// Initialisiere Timer beim Laden
document.addEventListener('DOMContentLoaded', () => {
    new GameTimers();

    // Frage nach Benachrichtigungs-Berechtigung
    if ('Notification' in window && Notification.permission === 'default') {
        // Nur fragen wenn es Timer gibt
        const hasTimers = document.querySelectorAll('.research-timer, .field-timer').length > 0;
        if (hasTimers) {
            Notification.requestPermission();
        }
    }
});
