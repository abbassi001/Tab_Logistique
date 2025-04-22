/**
 * Fichier: wizard.js
 * Description: Script commun pour toutes les étapes du wizard de gestion des colis
 */

const ColisWizard = {
    /**
     * Initialisation du wizard
     */
    init: function() {
        this.initTooltips();
        this.setupFormValidation();
        this.initDatepickers();
        this.setupStepIcons();
        this.setupNavigation();
        this.setupUnsavedChangesWarning();
    },

    /**
     * Active les tooltips Bootstrap
     */
    initTooltips: function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    /**
     * Configure la validation des formulaires
     */
    setupFormValidation: function() {
        const wizardForm = document.getElementById('wizard-form');
        if (wizardForm) {
            wizardForm.addEventListener('submit', function(event) {
                if (!wizardForm.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                wizardForm.classList.add('was-validated');
            });
        }
    },

    /**
     * Initialise les sélecteurs de date avec Flatpickr si disponible
     */
    initDatepickers: function() {
        const dateInputs = document.querySelectorAll('input[type="datetime-local"], input[type="date"]');
        dateInputs.forEach(function(input) {
            if (typeof flatpickr === 'function') {
                const isDateOnly = input.type === 'date';
                flatpickr(input, {
                    enableTime: !isDateOnly,
                    dateFormat: isDateOnly ? "Y-m-d" : "Y-m-d H:i",
                    time_24hr: true,
                    locale: {
                        firstDayOfWeek: 1, // Lundi
                        weekdays: {
                            shorthand: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                            longhand: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
                        },
                        months: {
                            shorthand: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                            longhand: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
                        }
                    }
                });
            }
        });
    },

    /**
     * Configure les animations pour les icônes des étapes
     */
    setupStepIcons: function() {
        const stepIcons = document.querySelectorAll('.step-icon');
        stepIcons.forEach(function(icon) {
            icon.addEventListener('mouseenter', function() {
                if (!icon.classList.contains('bg-success')) {
                    icon.classList.add('shadow-sm');
                    icon.style.transform = 'scale(1.05)';
                    icon.style.transition = 'transform 0.2s ease-in-out';
                }
            });
            
            icon.addEventListener('mouseleave', function() {
                icon.classList.remove('shadow-sm');
                icon.style.transform = 'scale(1)';
            });
        });
    },

    /**
     * Configure la navigation entre les étapes
     */
    setupNavigation: function() {
        const navigationButtons = document.querySelectorAll('.nav-step');
        navigationButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                const stepNumber = button.getAttribute('data-step');
                const currentStep = document.querySelector('[data-current-step]');
                
                if (!currentStep) return true;
                
                // Vérifier si on navigue vers une étape précédente (sans validation)
                const currentStepNumber = parseInt(currentStep.getAttribute('data-current-step'));
                const isPreviousStep = parseInt(stepNumber) < currentStepNumber;
                
                const wizardForm = document.getElementById('wizard-form');
                if (isPreviousStep || !wizardForm || wizardForm.checkValidity()) {
                    // Navigation autorisée
                    return true;
                } else {
                    // Empêcher la navigation et afficher les erreurs
                    event.preventDefault();
                    wizardForm.classList.add('was-validated');
                    
                    // Afficher un message d'erreur
                    ColisWizard.showAlert('danger', 'Veuillez corriger les erreurs avant de continuer.');
                    
                    // Faire défiler jusqu'au premier champ avec erreur
                    const firstError = document.querySelector('.form-control:invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    },

    /**
     * Affiche une alerte dans le conteneur d'alertes
     */
    showAlert: function(type, message) {
        const alertContainer = document.getElementById('alert-container');
        if (alertContainer) {
            // Créer l'élément d'alerte
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            
            // Icône en fonction du type d'alerte
            let icon = 'info-circle';
            if (type === 'danger') icon = 'exclamation-triangle';
            else if (type === 'success') icon = 'check-circle';
            else if (type === 'warning') icon = 'exclamation-circle';
            
            // Contenu de l'alerte
            alertDiv.innerHTML = `
                <i class="fas fa-${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Ajouter l'alerte au conteneur
            alertContainer.appendChild(alertDiv);
            
            // Retirer automatiquement l'alerte après 5 secondes
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 150);
            }, 5000);
        }
    },

    /**
     * Met en place un avertissement pour les changements non sauvegardés
     */
    setupUnsavedChangesWarning: function() {
        const wizardForm = document.getElementById('wizard-form');
        if (wizardForm) {
            // Marquer le formulaire comme modifié lorsqu'un champ change
            const formInputs = wizardForm.querySelectorAll('input, select, textarea');
            formInputs.forEach(input => {
                input.addEventListener('change', function() {
                    wizardForm.setAttribute('data-modified', 'true');
                });
            });
            
            // Vérifier avant de quitter la page
            window.addEventListener('beforeunload', function(event) {
                if (wizardForm.getAttribute('data-modified') === 'true') {
                    // La confirmation n'est pas personnalisable dans les navigateurs modernes pour des raisons de sécurité
                    event.preventDefault();
                    event.returnValue = 'Des modifications non sauvegardées seront perdues. Voulez-vous vraiment quitter cette page?';
                }
            });
            
            // Désactiver l'avertissement lors de la soumission du formulaire
            wizardForm.addEventListener('submit', function() {
                wizardForm.removeAttribute('data-modified');
            });
        }
    },
    
    /**
     * Méthodes spécifiques aux étapes
     */
    steps: {
        // Étape 1: Informations du colis
        step1: {
            init: function() {
                // Code spécifique à l'étape 1
                ColisWizard.steps.step1.setupTrackingCodeGenerator();
                ColisWizard.steps.step1.setupWeightCalculation();
            },
            
            // Générateur de code de suivi
            setupTrackingCodeGenerator: function() {
                const generateBtn = document.getElementById('generate-tracking');
                const trackingInput = document.getElementById('colis_codeTracking');
                
                if (generateBtn && trackingInput) {
                    generateBtn.addEventListener('click', function() {
                        // Générer un code de suivi aléatoire si le champ est vide
                        if (!trackingInput.value) {
                            const prefix = 'TAB';
                            const random = Math.floor(Math.random() * 10000000).toString().padStart(7, '0');
                            const suffix = new Date().getFullYear().toString().substr(2, 2);
                            
                            trackingInput.value = `${prefix}${random}${suffix}`;
                            
                            // Marquer le formulaire comme modifié
                            const wizardForm = document.getElementById('wizard-form');
                            if (wizardForm) {
                                wizardForm.setAttribute('data-modified', 'true');
                            }
                        }
                    });
                }
            },
            
            // Calcul automatique du poids
            setupWeightCalculation: function() {
                // Vous pouvez ajouter ici un calculateur de poids basé sur les dimensions
            }
        },
        
        // Étape 2: Expéditeur
        step2: {
            init: function() {
                // Code spécifique à l'étape 2
                ColisWizard.steps.step2.setupAddressAutocomplete();
            },
            
            // Autocomplétion d'adresse
            setupAddressAutocomplete: function() {
                // Vous pouvez intégrer ici une API d'autocomplétion d'adresse
            }
        },
        
        // Étape 3: Destinataire
        step3: {
            init: function() {
                // Code spécifique à l'étape 3
                // Similaire à l'étape 2
            }
        },
        
        // Étape 4: Statut
        step4: {
            init: function() {
                // Code spécifique à l'étape 4
            }
        },
        
        // Étape 5: Transport
        step5: {
            init: function() {
                // Code spécifique à l'étape 5
            }
        },
        
        // Étape 6: Photos
        step6: {
            init: function() {
                // Code spécifique à l'étape 6
                ColisWizard.steps.step6.setupImagePreview();
            },
            
            // Prévisualisation des images
            setupImagePreview: function() {
                const fileInput = document.getElementById('photo_file');
                const previewContainer = document.getElementById('image-preview');
                
                if (fileInput && previewContainer) {
                    fileInput.addEventListener('change', function() {
                        // Vider le conteneur de prévisualisation
                        previewContainer.innerHTML = '';
                        
                        // Créer une prévisualisation pour chaque fichier sélectionné
                        if (this.files && this.files.length > 0) {
                            for (let i = 0; i < this.files.length; i++) {
                                const file = this.files[i];
                                
                                // Vérifier que c'est bien une image
                                if (!file.type.startsWith('image/')) continue;
                                
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const imgWrap = document.createElement('div');
                                    imgWrap.className = 'preview-item mb-3';
                                    
                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    img.className = 'img-thumbnail';
                                    img.style.maxHeight = '150px';
                                    
                                    imgWrap.appendChild(img);
                                    previewContainer.appendChild(imgWrap);
                                };
                                
                                reader.readAsDataURL(file);
                            }
                        }
                    });
                }
            }
        },
        
        // Étape 7: Documents
        step7: {
            init: function() {
                // Code spécifique à l'étape 7
                ColisWizard.steps.step7.setupFileType();
            },
            
            // Afficher le type de fichier
            setupFileType: function() {
                const fileInput = document.getElementById('document_file');
                const fileTypeDisplay = document.getElementById('file-type-display');
                
                if (fileInput && fileTypeDisplay) {
                    fileInput.addEventListener('change', function() {
                        if (this.files && this.files.length > 0) {
                            const file = this.files[0];
                            let fileTypeIcon = 'file';
                            
                            // Déterminer l'icône en fonction du type de fichier
                            if (file.type.includes('pdf')) {
                                fileTypeIcon = 'file-pdf';
                            } else if (file.type.includes('word') || file.type.includes('doc')) {
                                fileTypeIcon = 'file-word';
                            } else if (file.type.includes('excel') || file.type.includes('sheet') || file.type.includes('xls')) {
                                fileTypeIcon = 'file-excel';
                            }
                            
                            fileTypeDisplay.innerHTML = `
                                <div class="alert alert-info">
                                    <i class="fas fa-${fileTypeIcon} me-2"></i>
                                    Fichier sélectionné: ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                                </div>
                            `;
                        } else {
                            fileTypeDisplay.innerHTML = '';
                        }
                    });
                }
            }
        },
        
        // Étape 8: Révision
        step8: {
            init: function() {
                // Code spécifique à l'étape de révision
            }
        }
    }
};

// Initialiser le wizard lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    ColisWizard.init();
    
    // Déterminer l'étape actuelle et initialiser le code spécifique à cette étape
    const currentStepElem = document.querySelector('[data-current-step]');
    if (currentStepElem) {
        const currentStep = parseInt(currentStepElem.getAttribute('data-current-step'));
        const stepInitFunction = ColisWizard.steps[`step${currentStep}`]?.init;
        
        if (typeof stepInitFunction === 'function') {
            stepInitFunction();
        }
    }
});