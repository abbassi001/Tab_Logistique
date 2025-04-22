document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le champ de date_creation avec la date actuelle
    const dateField = document.querySelector('input[name*="[date_creation]"]');
    if (dateField) {
        const now = new Date();
        dateField.value = now.toISOString().slice(0, 16);
    }

    // Traitement des dimensions (Step 1)
    const longueurField = document.querySelector('[data-dimension="longueur"]');
    const largeurField = document.querySelector('[data-dimension="largeur"]');
    const hauteurField = document.querySelector('[data-dimension="hauteur"]');
    const dimensionsDisplay = document.getElementById('dimensions-display');
    const hiddenDimensionsField = document.querySelector('.hidden-dimensions-field');
    
    // Fonction pour mettre à jour le champ dimensions
    if (longueurField && largeurField && hauteurField) {
        function updateDimensions() {
            // Récupérer les valeurs avec une valeur par défaut de 0
            const longueur = parseFloat(longueurField.value) || 0;
            const largeur = parseFloat(largeurField.value) || 0;
            const hauteur = parseFloat(hauteurField.value) || 0;
            
            // Mettre à jour l'affichage des dimensions au format L x l x h
            const dimensionsText = `${longueur.toFixed(2)} cm x ${largeur.toFixed(2)} cm x ${hauteur.toFixed(2)} cm`;
            
            // Mettre à jour le champ d'affichage si présent
            if(dimensionsDisplay) {
                dimensionsDisplay.value = dimensionsText;
            }
            
            // Mettre à jour le champ caché
            if(hiddenDimensionsField) {
                hiddenDimensionsField.value = dimensionsText;
            }
        }
        
        // Ajouter des écouteurs d'événements pour les champs de dimension
        [longueurField, largeurField, hauteurField].forEach(field => {
            field.addEventListener('input', updateDimensions);
        });
        
        // Initialiser les dimensions au chargement de la page
        updateDimensions();
    }
    
    // Initialisation de la validation Bootstrap
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Gestion des sélecteurs de pays
    const countrySelect = document.getElementById('country');
    if (countrySelect) {
        // Array of countries
        const countries = [
            "Afghanistan", "Afrique du Sud", "Albanie", "Algérie", "Allemagne", "Andorre", "Angola", "Antigua-et-Barbuda", "Arabie Saoudite", 
            "Argentine", "Arménie", "Australie", "Autriche", "Azerbaïdjan", "Bahamas", "Bahreïn", "Bangladesh", "Barbade", "Belgique", 
            "Belize", "Bénin", "Bhoutan", "Biélorussie", "Birmanie", "Bolivie", "Bosnie-Herzégovine", "Botswana", "Brésil", "Brunei", 
            "Bulgarie", "Burkina Faso", "Burundi", "Cambodge", "Cameroun", "Canada", "Cap-Vert", "Chili", "Chine", "Chypre", "Colombie", 
            "Comores", "Congo", "Corée du Nord", "Corée du Sud", "Costa Rica", "Côte d'Ivoire", "Croatie", "Cuba", "Danemark", "Djibouti", 
            "Dominique", "Égypte", "Émirats arabes unis", "Équateur", "Érythrée", "Espagne", "Estonie", "Eswatini", "États-Unis", "Éthiopie", 
            "Fidji", "Finlande", "France", "Gabon", "Gambie", "Géorgie", "Ghana", "Grèce", "Grenade", "Guatemala", "Guinée", "Guinée-Bissau", 
            "Guinée équatoriale", "Guyana", "Haïti", "Honduras", "Hongrie", "Îles Marshall", "Îles Salomon", "Inde", "Indonésie", "Irak", 
            "Iran", "Irlande", "Islande", "Israël", "Italie", "Jamaïque", "Japon", "Jordanie", "Kazakhstan", "Kenya", "Kirghizistan", 
            "Kiribati", "Koweït", "Laos", "Lesotho", "Lettonie", "Liban", "Liberia", "Libye", "Liechtenstein", "Lituanie", "Luxembourg", 
            "Macédoine du Nord", "Madagascar", "Malaisie", "Malawi", "Maldives", "Mali", "Malte", "Maroc", "Maurice", "Mauritanie", "Mexique", 
            "Micronésie", "Moldavie", "Monaco", "Mongolie", "Monténégro", "Mozambique", "Namibie", "Nauru", "Népal", "Nicaragua", "Niger", 
            "Nigeria", "Norvège", "Nouvelle-Zélande", "Oman", "Ouganda", "Ouzbékistan", "Pakistan", "Palaos", "Palestine", "Panama", 
            "Papouasie-Nouvelle-Guinée", "Paraguay", "Pays-Bas", "Pérou", "Philippines", "Pologne", "Portugal", "Qatar", 
            "République centrafricaine", "République démocratique du Congo", "République dominicaine", "République tchèque", "Roumanie", 
            "Royaume-Uni", "Russie", "Rwanda", "Saint-Kitts-et-Nevis", "Saint-Marin", "Saint-Vincent-et-les-Grenadines", "Sainte-Lucie", 
            "Salvador", "Samoa", "São Tomé-et-Principe", "Sénégal", "Serbie", "Seychelles", "Sierra Leone", "Singapour", "Slovaquie", 
            "Slovénie", "Somalie", "Soudan", "Soudan du Sud", "Sri Lanka", "Suède", "Suisse", "Suriname", "Syrie", "Tadjikistan", "Tanzanie", 
            "Tchad", "Thaïlande", "Timor oriental", "Togo", "Tonga", "Trinité-et-Tobago", "Tunisie", "Turkménistan", "Turquie", "Tuvalu", 
            "Ukraine", "Uruguay", "Vanuatu", "Vatican", "Venezuela", "Vietnam", "Yémen", "Zambie", "Zimbabwe"
        ];
        
        // Vérifier si le select est déjà peuplé (peut-être déjà géré par Symfony)
        if (countrySelect.options.length <= 1) {
            // Trier alphabétiquement
            countries.sort();
            
            // Si vide, ajouter l'option par défaut
            if (countrySelect.options.length === 0) {
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Sélectionnez un pays';
                defaultOption.selected = true;
                countrySelect.appendChild(defaultOption);
            }
            
            // Ajouter les pays
            countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country;
                option.textContent = country;
                countrySelect.appendChild(option);
            });
        }
    }
    
    // Prévisualisations des fichiers
    // Photos
    const fileInput = document.querySelector('input[type="file"][name*="[file]"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Si c'est dans l'étape 6 (photos)
                if (document.querySelector('.file-preview')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Si une prévisualisation existe déjà, la supprimer
                        const existingPreview = document.querySelector('.file-preview');
                        if (existingPreview) {
                            existingPreview.remove();
                        }
                        
                        // Créer un élément pour la prévisualisation
                        const preview = document.createElement('div');
                        preview.className = 'file-preview mt-2';
                        preview.innerHTML = `
                            <div class="card" style="max-width: 250px;">
                                <img src="${e.target.result}" class="card-img-top" alt="Prévisualisation" style="max-height: 150px; object-fit: contain;">
                                <div class="card-body p-2">
                                    <p class="card-text small text-muted mb-0">${file.name}</p>
                                    <p class="card-text small">${Math.round(file.size / 1024)} KB</p>
                                </div>
                            </div>
                        `;
                        
                        // Insérer la prévisualisation après l'input
                        fileInput.parentElement.parentElement.appendChild(preview);
                    };
                    reader.readAsDataURL(file);
                }
                
                // Si c'est dans l'étape 7 (documents)
                const previewContainer = document.getElementById('file-preview-container');
                if (previewContainer) {
                    // Afficher le conteneur de prévisualisation
                    previewContainer.classList.remove('d-none');
                    
                    // Mettre à jour les informations du fichier
                    document.getElementById('file-name').textContent = file.name;
                    document.getElementById('file-size').textContent = Math.round(file.size / 1024) + ' KB';
                    
                    // Détecter l'extension du fichier
                    const extension = file.name.split('.').pop().toLowerCase();
                    let iconClass = 'fas fa-file text-secondary';
                    
                    if (extension === 'pdf') {
                        iconClass = 'fas fa-file-pdf text-danger';
                    } else if (['doc', 'docx'].includes(extension)) {
                        iconClass = 'fas fa-file-word text-primary';
                    } else if (['xls', 'xlsx', 'csv'].includes(extension)) {
                        iconClass = 'fas fa-file-excel text-success';
                    }
                    
                    // Mettre à jour l'icône
                    const iconElement = document.getElementById('file-icon');
                    iconElement.className = iconClass + ' fa-3x mb-2';
                    
                    // Mettre à jour le nom du fichier dans le champ
                    const nomFichierInput = document.querySelector('input[name*="[nomFichier]"]');
                    if (nomFichierInput && !nomFichierInput.value) {
                        // Si le champ nom de fichier est vide, le remplir avec le nom du fichier sans extension
                        const fileName = file.name.split('.').slice(0, -1).join('.');
                        nomFichierInput.value = fileName;
                    }
                    
                    // Si le champ cheminStockage existe, le remplir avec le nom du fichier
                    const cheminStockageInput = document.querySelector('input[name*="[cheminStockage]"]');
                    if (cheminStockageInput) {
                        cheminStockageInput.value = file.name;
                    }
                }
            }
        });
    }
    
    // Animation pour les cartes d'actions rapides
    const actionCards = document.querySelectorAll('.hover-shadow');
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('shadow');
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('shadow');
            this.style.transform = 'translateY(0)';
        });
    });
});