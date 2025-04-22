/**
 * advanced-ui.js
 * Fonctionnalités UI avancées pour TAB Logistique
 */

document.addEventListener('DOMContentLoaded', function() {
    // Animations d'entrée pour les cartes
    animateCards();
    
    // Graphiques avancés avec données réelles
    initializeAdvancedCharts();
    
    // Améliorations des tables de données
    enhanceDatatablesIfPresent();
    
    // Notifications et alertes améliorées
    initializeAdvancedNotifications();
});

/**
 * Ajoute des animations d'apparition aux cartes
 */
function animateCards() {
    const cards = document.querySelectorAll('.card');
    
    // Si l'API Intersection Observer est disponible
    if ('IntersectionObserver' in window) {
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                // Si la carte entre dans le viewport
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('card-animated');
                        cardObserver.unobserve(entry.target);
                    }, entry.target.dataset.delay || 0);
                }
            });
        }, { threshold: 0.1 });
        
        // Observer chaque carte avec un délai différent
        cards.forEach((card, index) => {
            card.dataset.delay = index * 100; // Délai croissant pour effet cascade
            card.classList.add('card-animate-ready');
            cardObserver.observe(card);
        });
    } else {
        // Fallback pour les navigateurs ne supportant pas IntersectionObserver
        cards.forEach(card => card.classList.add('card-animated'));
    }
}

/**
 * Initialise des graphiques avancés si la page en contient
 */
function initializeAdvancedCharts() {
    // Si Chart.js est chargé
    if (typeof Chart !== 'undefined') {
        // 1. Graphique des statuts de colis
        const statusChartEl = document.getElementById('statusChart');
        if (statusChartEl) {
            const statusCtx = statusChartEl.getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['En attente', 'Reçu', 'En transit', 'En livraison', 'Livré', 'Retourné'],
                    datasets: [{
                        data: [12, 19, 8, 5, 30, 3],
                        backgroundColor: [
                            '#ffc107', // En attente - warning
                            '#20c997', // Reçu - teal
                            '#0dcaf0', // En transit - info
                            '#0d6efd', // En livraison - primary
                            '#198754', // Livré - success
                            '#dc3545'  // Retourné - danger
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 20
                            }
                        }
                    }
                }
            });
        }
        
        // 2. Graphique des colis par mois
        const monthlyChartEl = document.getElementById('monthlyChart');
        if (monthlyChartEl) {
            const monthlyCtx = monthlyChartEl.getContext('2d');
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                    datasets: [{
                        label: 'Colis créés',
                        data: [65, 59, 80, 81, 56, 55, 40, 45, 60, 75, 70, 80],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }
}

/**
 * Améliore les tables de données si DataTables est présent
 */
function enhanceDatatablesIfPresent() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.data-table').each(function() {
            $(this).DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    'copy', 'excel', 'pdf', 'csv'
                ]
            });
        });
    }
}

/**
 * Initialise des notifications avancées
 */
function initializeAdvancedNotifications() {
    // Créer une div pour les notifications si elle n'existe pas
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.className = 'notification-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(notificationContainer);
    }
    
    // Fonction pour ajouter une notification
    window.showNotification = function(message, type = 'info', duration = 5000) {
        const notificationId = 'notification-' + Date.now();
        const iconMap = {
            'success': 'fa-check-circle',
            'warning': 'fa-exclamation-triangle',
            'danger': 'fa-times-circle',
            'info': 'fa-info-circle'
        };
        
        const notificationHTML = `
            <div id="${notificationId}" class="notification notification-${type}">
                <div class="notification-icon">
                    <i class="fas ${iconMap[type] || 'fa-info-circle'}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-message">${message}</div>
                </div>
                <button type="button" class="notification-close" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Ajouter au conteneur
        notificationContainer.insertAdjacentHTML('beforeend', notificationHTML);
        
        // Récupérer l'élément créé
        const notification = document.getElementById(notificationId);
        
        // Ajouter la classe "show" après un court délai pour l'animation
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Configurer le bouton de fermeture
        const closeButton = notification.querySelector('.notification-close');
        closeButton.addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300); // Attendre la fin de l'animation
        });
        
        // Auto-fermeture après la durée spécifiée
        if (duration !== 0) {
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
        
        return notification;
    };
}