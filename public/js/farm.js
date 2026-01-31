/**
 * Agrar Simulator - Farm Interaktionen
 */

class FarmManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Plant buttons via AJAX (optional)
        document.querySelectorAll('.plant-crop-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.plantCrop(e));
        });

        // Harvest buttons via AJAX (optional)
        document.querySelectorAll('.harvest-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.harvestField(e));
        });

        // Confirm dialogs
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', (e) => {
                const message = el.dataset.confirm || 'Bist du sicher?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }

    async plantCrop(event) {
        event.preventDefault();

        const btn = event.target;
        const fieldId = btn.dataset.fieldId;
        const cropSelect = document.querySelector(`#crop-select-${fieldId}`);

        if (!cropSelect || !cropSelect.value) {
            showNotification('Bitte waehle eine Feldfrucht', 'error');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Pflanze...';

        try {
            const result = await API.post('/field/plant', {
                fieldId: parseInt(fieldId),
                cropId: parseInt(cropSelect.value)
            });

            if (result.success) {
                showNotification(result.message, 'success');
                location.reload();
            } else {
                showNotification(result.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Pflanzen';
            }
        } catch (error) {
            showNotification('Fehler beim Pflanzen', 'error');
            btn.disabled = false;
            btn.textContent = 'Pflanzen';
        }
    }

    async harvestField(event) {
        event.preventDefault();

        const btn = event.target;
        const fieldId = btn.dataset.fieldId;

        btn.disabled = true;
        btn.textContent = 'Ernte...';

        try {
            const result = await API.post('/field/harvest', {
                fieldId: parseInt(fieldId)
            });

            if (result.success) {
                showNotification(
                    `${result.yield} Einheiten ${result.crop_name} geerntet! Wert: ${formatCurrency(result.value)}`,
                    'success'
                );
                updateFarmStats();
                location.reload();
            } else {
                showNotification(result.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Ernten';
            }
        } catch (error) {
            showNotification('Fehler beim Ernten', 'error');
            btn.disabled = false;
            btn.textContent = 'Ernten';
        }
    }

    async feedAnimals(farmAnimalId) {
        const result = await API.post('/animal/feed', {
            farmAnimalId: farmAnimalId
        });

        if (result.success) {
            showNotification(result.message, 'success');
            updateFarmStats();
        } else {
            showNotification(result.message, 'error');
        }

        return result;
    }

    async collectProducts(farmAnimalId) {
        const result = await API.post('/animal/collect', {
            farmAnimalId: farmAnimalId
        });

        if (result.success) {
            showNotification(
                `${result.quantity}x ${result.product} gesammelt!`,
                'success'
            );
            updateFarmStats();
        } else {
            showNotification(result.message, 'error');
        }

        return result;
    }
}

// ==========================================
// Research Manager
// ==========================================

class ResearchManager {
    async startResearch(researchId) {
        const result = await API.post('/research/start', {
            researchId: researchId
        });

        if (result.success) {
            showNotification(result.message, 'success');
            location.reload();
        } else {
            showNotification(result.message, 'error');
        }

        return result;
    }

    async checkProgress() {
        const result = await API.get('/research/progress');

        if (result.active && result.remaining_time) {
            if (result.remaining_time.completed) {
                showNotification('Forschung abgeschlossen! Lade Seite neu...', 'success');
                setTimeout(() => location.reload(), 2000);
            }
        }

        return result;
    }
}

// ==========================================
// Market Manager
// ==========================================

class MarketManager {
    async createListing(itemType, itemId, quantity, pricePerUnit) {
        const result = await API.post('/market/create', {
            itemType: itemType,
            itemId: itemId,
            quantity: quantity,
            pricePerUnit: pricePerUnit
        });

        if (result.success) {
            showNotification(result.message, 'success');
            location.reload();
        } else {
            showNotification(result.message, 'error');
        }

        return result;
    }

    async buyListing(listingId, quantity) {
        const result = await API.post('/market/buy', {
            listingId: listingId,
            quantity: quantity
        });

        if (result.success) {
            showNotification(result.message, 'success');
            updateFarmStats();
            location.reload();
        } else {
            showNotification(result.message, 'error');
        }

        return result;
    }

    async cancelListing(listingId) {
        const result = await API.delete(`/market/cancel/${listingId}`);

        if (result.success) {
            showNotification(result.message, 'success');
            location.reload();
        } else {
            showNotification(result.message, 'error');
        }

        return result;
    }
}

// ==========================================
// Initialisierung
// ==========================================

let farmManager;
let researchManager;
let marketManager;

document.addEventListener('DOMContentLoaded', () => {
    farmManager = new FarmManager();
    researchManager = new ResearchManager();
    marketManager = new MarketManager();

    // Periodische Updates (alle 2 Minuten)
    setInterval(() => {
        updateFarmStats();
    }, 120000);

    // Forschungs-Check (alle 30 Sekunden falls aktive Forschung)
    const hasActiveResearch = document.querySelector('.research-timer');
    if (hasActiveResearch) {
        setInterval(() => {
            researchManager.checkProgress();
        }, 30000);
    }
});
