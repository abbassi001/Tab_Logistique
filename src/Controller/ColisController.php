<?php

namespace App\Controller;

use App\Entity\Colis;
use App\Entity\Photo;
use App\Entity\Statut;
use App\Entity\DocumentSupport;
use App\Entity\ColisTransport;
use App\Form\ColisType;
use App\Form\PhotoType;
use App\Form\StatutType;
use App\Form\DocumentSupportType;
use App\Form\ColisTransportType;
use App\Repository\ColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Enum\StatusType; // Ensure the correct namespace for StatusType
use App\Entity\User; // Import the User entity

use Dompdf\Dompdf;
use Dompdf\Options;


#[Route('/colis')]
#[IsGranted('ROLE_USER')] // Ajoutez cette ligne
final class ColisController extends AbstractController
{
    private ColisRepository $colisRepository;

    public function __construct(ColisRepository $colisRepository)
    {
        $this->colisRepository = $colisRepository;
    }
    #[Route(name: 'app_colis_index', methods: ['GET'])]
    public function index(Request $request, ColisRepository $colisRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('search', '');
        
        $filters = [
            'search' => $search,
            'dateMin' => $request->query->get('dateMin'),
            'dateMax' => $request->query->get('dateMax'),
            'expediteur' => $request->query->get('expediteur'),
            'destinataire' => $request->query->get('destinataire'),
            'status' => $request->query->get('status')
        ];
        
        $order = [
            $request->query->get('sort', 'id') => $request->query->get('direction', 'DESC')
        ];
        
        $result = $colisRepository->findAllPaginated($page, 10, $filters, $order);
        
        return $this->render('colis/index.html.twig', [
            'colis' => $result['items'],
            'pagination' => [
                'currentPage' => $result['currentPage'],
                'totalPages' => $result['totalPages'],
                'totalItems' => $result['totalItems']
            ],
            'filters' => $filters,
            'order' => $order
        ]);
    }

    #[Route('/new', name: 'app_colis_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Rediriger vers le wizard de création de colis au lieu d'utiliser le formulaire simple
        return $this->redirectToRoute('app_colis_wizard_start');
    }
    /**
 * @Route("/print-selection", name="app_colis_print")
 */
#[Route('/print-selection', name: 'app_colis_print')]
public function printColis(Request $request): Response
{
    $ids = explode(',', $request->query->get('ids', ''));
    
    if (empty($ids)) {
        throw $this->createNotFoundException('Aucun colis sélectionné');
    }
    
    $colis = $this->colisRepository->findBy(['id' => $ids]);
    
    return $this->render('colis/print.html.twig', [
        'colis' => $colis
    ]);
}

/**
 * @Route("/labels", name="app_colis_labels")
 */
#[Route('/labels', name: 'app_colis_labels')]
public function generateLabels(Request $request): Response
{
    $ids = explode(',', $request->query->get('ids', ''));
    
    if (empty($ids)) {
        throw $this->createNotFoundException('Aucun colis sélectionné');
    }
    
    $colis = $this->colisRepository->findBy(['id' => $ids]);
    
    // For now, just use the same template as print
    // Later, you can create a dedicated labels.html.twig template
    return $this->render('colis/print.html.twig', [
        'colis' => $colis,
        'mode' => 'labels'
    ]);
}

    #[Route('/{id}', name: 'app_colis_show', methods: ['GET'])]
    public function show(Colis $coli): Response
    {
        // Vérifier si le code de tracking est défini, sinon le générer
        if ($coli->getCodeTracking() === null) {
            // Générer le code de tracking
            $currentYear = (new \DateTime())->format('Y');
            $codeTracking = sprintf('TAB-%06d-%s', $coli->getId(), $currentYear);
            
            // Mettre à jour le colis
            $coli->setCodeTracking($codeTracking);
            $entityManager = $this->container->get('doctrine')->getManager();
            $entityManager->persist($coli);
            $entityManager->flush();
        }
        
        // Création des formulaires vides pour les actions rapides
        $photo = new Photo();
        $photo->setColis($coli);
        $photoForm = $this->createForm(PhotoType::class, $photo, [
            'action' => $this->generateUrl('app_colis_add_photo', ['id' => $coli->getId()]),
        ]);
        
        $document = new DocumentSupport();
        $document->setColis($coli);
        $documentForm = $this->createForm(DocumentSupportType::class, $document, [
            'action' => $this->generateUrl('app_colis_add_document', ['id' => $coli->getId()]),
        ]);
        
        $statut = new Statut();
        $statut->setColis($coli);
        $statutForm = $this->createForm(StatutType::class, $statut, [
            'action' => $this->generateUrl('app_colis_add_statut', ['id' => $coli->getId()]),
        ]);
        
        $transport = new ColisTransport();
        $transport->setColis($coli);
        $transportForm = $this->createForm(ColisTransportType::class, $transport, [
            'action' => $this->generateUrl('app_colis_add_transport', ['id' => $coli->getId()]),
        ]);
        
        return $this->render('colis/show.html.twig', [
            'coli' => $coli,
            'photo_form' => $photoForm->createView(),
            'document_form' => $documentForm->createView(),
            'statut_form' => $statutForm->createView(),
            'transport_form' => $transportForm->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_colis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
    {
        // Rediriger vers le wizard en mode édition avec l'ID du colis
        return $this->redirectToRoute('app_colis_wizard_edit', ['id' => $coli->getId()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_colis_delete', methods: ['POST'])]
    public function delete(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $coli->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($coli);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_colis_index', [], Response::HTTP_SEE_OTHER);
    }

    // Nouvelles méthodes pour les actions rapides

    #[Route('/{id}/add-photo', name: 'app_colis_add_photo', methods: ['POST'])]
    public function addPhoto(Request $request, Colis $coli, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $photo = new Photo();
        $photo->setColis($coli);
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement de l'image si nécessaire
            $photoFile = $form->get('file')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );

                    $photo->setUrlPhoto($newFilename);
                } catch (FileException $e) {
                    // Gestion de l'erreur
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo.');
                }
            }

            $photo->setDateUpload(new \DateTime());
            $entityManager->persist($photo);
            $entityManager->flush();

            $this->addFlash('success', 'La photo a été ajoutée avec succès.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
    }

    #[Route('/{id}/add-document', name: 'app_colis_add_document', methods: ['POST'])]
    public function addDocument(Request $request, Colis $coli, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $document = new DocumentSupport();
        $document->setColis($coli);
        $form = $this->createForm(DocumentSupportType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement du document si nécessaire
            $docFile = $form->get('file')->getData();

            if ($docFile) {
                $originalFilename = pathinfo($docFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $docFile->guessExtension();

                try {
                    $docFile->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );

                    $document->setCheminStockage($newFilename);
                } catch (FileException $e) {
                    // Gestion de l'erreur
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du document.');
                }
            }

            $document->setDateUpload(new \DateTime());
            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash('success', 'Le document a été ajouté avec succès.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
    }

    #[Route('/{id}/add-statut', name: 'app_colis_add_statut', methods: ['POST'])]
    public function addStatut(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
    {
        $statut = new Statut();
        $statut->setColis($coli);
        $form = $this->createForm(StatutType::class, $statut);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statut->setDateStatut(new \DateTime());
            $entityManager->persist($statut);
            $entityManager->flush();

            $this->addFlash('success', 'Le statut a été mis à jour avec succès.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', message: $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
    }

    #[Route('/{id}/update-statut', name: 'app_colis_update_statut', methods: ['POST'])]
public function updateStatut(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
{
    // Créer un nouveau statut
    $statut = new Statut();
    $statut->setColis($coli);
    $statut->setDateStatut(new \DateTime());

    // Récupérer les données du formulaire
    $statutType = $request->request->get('statut_type');
    $localisation = $request->request->get('localisation');
    $userId = $request->request->get('user_id');

    try {
        // Vérifier et convertir manuellement le type de statut
        $enumStatutType = match($statutType) {
            'EN_ATTENTE' => StatusType::EN_ATTENTE,
            'RECU' => StatusType::RECU,
            'EN_TRANSIT' => StatusType::EN_TRANSIT,
            'EN_LIVRAISON' => StatusType::EN_LIVRAISON,
            'LIVRE' => StatusType::LIVRE,
            'RETOURNE' => StatusType::RETOURNE,
            'BLOQUE_DOUANE' => StatusType::BLOQUE_DOUANE,
            default => throw new \InvalidArgumentException('Type de statut invalide: ' . $statutType)
        };

        $statut->setTypeStatut($enumStatutType);
        $statut->setLocalisation($localisation);

        // Si un utilisateur est sélectionné, l'associer au statut
        if ($userId) {
            $user = $entityManager->getRepository(User::class)->find($userId);
            if ($user) {
                $statut->setUser($user);
            }
        }

        // Récupérer l'utilisateur connecté si possible
        $user = $this->getUser();
        if ($user instanceof User && !$statut->getUser()) {
            $statut->setUser($user);
        }

        // Sauvegarder dans la base de données
        $entityManager->persist($statut);
        $entityManager->flush();

        $this->addFlash('success', 'Le statut du colis a été mis à jour avec succès.');
    } catch (\Exception $e) {
        $this->addFlash('error', 'Erreur lors de la mise à jour du statut : ' . $e->getMessage());
    }

    // Rediriger vers la page de détail du colis
    return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
}


    #[Route('/{id}/add-transport', name: 'app_colis_add_transport', methods: ['POST'])]
    public function addTransport(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
    {
        $transport = new ColisTransport();
        $transport->setColis($coli);
        $form = $this->createForm(ColisTransportType::class, $transport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transport->setDateAssociation(new \DateTime());
            $entityManager->persist($transport);
            $entityManager->flush();

            $this->addFlash('success', 'Le transport a été ajouté avec succès.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
    }



/**
 * @Route("/export/{format}", name="app_colis_export")
 */


 #[Route("/export/{format}", "app_colis_export")]
 public function export(string $format): Response
{
    $colis = $this->colisRepository->findAll();
    
    switch ($format) {
        case 'csv':
            return $this->exportCSV($colis);
        case 'excel':
            return $this->exportExcel($colis);
        case 'pdf':
            return $this->exportPDF($colis);
        default:
            throw new \Exception('Format non supporté');
    }
}

private function exportExcel(array $colis): Response
{
    // Placeholder implementation for exporting to Excel
    // $content = "Excel export is not yet implemented.";
    $content = "ID\tCode Tracking\tExpéditeur\tDestinataire\tStatut\tDate Création\n";
    foreach ($colis as $item) {
        // Get the latest status if available
        $lastStatus = null;
        if ($item->getStatuts() && !$item->getStatuts()->isEmpty()) {
            $lastStatus = $item->getStatuts()->last();
        }
        
        $content .= $item->getId() . "\t" .
                    ($item->getCodeTracking() ?? 'N/A') . "\t" .
                    ($item->getExpediteur() ? $item->getExpediteur()->getNomEntrepriseIndividu() : 'N/A') . "\t" .
                    ($item->getDestinataire() ? $item->getDestinataire()->getNomEntrepriseIndividu() : 'N/A') . "\t" .
                    ($lastStatus ? $lastStatus->getTypeStatut()->name : 'N/A') . "\t" .
                    ($item->getDateCreation() ? $item->getDateCreation()->format('d/m/Y') : 'N/A') . "\n";
    }
    // Set the response headers for Excel

    
    $response = new Response($content);
    $response->headers->set('Content-Type', 'application/vnd.ms-excel');
    $response->headers->set('Content-Disposition', 'attachment; filename="colis-export.xlsx"');
    
    return $response;
}

/**
 * @Route("/export/pdf", name="app_colis_export_pdf")
 */

    #[Route("/export/pdf", name: "app_colis_export_pdf")]
private function exportPDF(array $colis): Response
{

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Liste des Colis</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
            }
            h1 {
                color: #0d6efd;
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 1px solid #dee2e6;
                padding-bottom: 10px;
            }
            .header-info {
                text-align: right;
                font-size: 12px;
                color: #6c757d;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th {
                background-color: #f8f9fa;
                color: #495057;
                font-weight: bold;
                text-align: left;
                padding: 8px;
                border: 1px solid #dee2e6;
            }
            td {
                padding: 8px;
                border: 1px solid #dee2e6;
                font-size: 12px;
            }
            tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .status-badge {
                display: inline-block;
                padding: 3px 6px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
                color: white;
            }
            .status-en_attente { background-color: #ffc107; color: #212529; }
            .status-recu { background-color: #0dcaf0; }
            .status-en_transit { background-color: #0d6efd; }
            .status-en_livraison { background-color: #6610f2; }
            .status-livre { background-color: #198754; }
            .status-retourne { background-color: #dc3545; }
            .status-bloque_douane { background-color: #dc3545; }
            .footer {
                text-align: center;
                font-size: 10px;
                color: #6c757d;
                margin-top: 30px;
                border-top: 1px solid #dee2e6;
                padding-top: 10px;
            }
            .page-number {
                text-align: right;
                font-size: 10px;
                color: #6c757d;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <h1>Liste des Colis</h1>
        
        <div class="header-info">
            <p>Généré le: ' . (new \DateTime())->format('d/m/Y H:i') . '</p>
            <p>Total: ' . count($colis) . ' colis</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code Tracking</th>
                    <th>Expéditeur</th>
                    <th>Destinataire</th>
                    <th>Statut</th>
                    <th>Date Création</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($colis as $item) {
        // Get the latest status if available
        $lastStatus = null;
        $statusClass = '';
        $statusName = 'N/A';
        
        if ($item->getStatuts() && !$item->getStatuts()->isEmpty()) {
            $lastStatus = $item->getStatuts()->last();
            $statusName = $lastStatus->getTypeStatut()->name;
            $statusClass = strtolower($statusName);
        }
        
        $html .= '<tr>
                    <td>' . $item->getId() . '</td>
                    <td>' . ($item->getCodeTracking() ?? 'N/A') . '</td>
                    <td>' . ($item->getExpediteur() ? $item->getExpediteur()->getNomEntrepriseIndividu() : 'N/A') . '</td>
                    <td>' . ($item->getDestinataire() ? $item->getDestinataire()->getNomEntrepriseIndividu() : 'N/A') . '</td>
                    <td><span class="status-badge status-' . $statusClass . '">' . $statusName . '</span></td>
                    <td>' . ($item->getDateCreation() ? $item->getDateCreation()->format('d/m/Y') : 'N/A') . '</td>
                </tr>';
    }

    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>TAB Logistique - Système de Gestion des Colis</p>
        </div>
        
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
                $font = $fontMetrics->get_font("Arial", "normal");
                $size = 10;
                $color = array(0.5, 0.5, 0.5);
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width) - 30;
                $y = $pdf->get_height() - 35;
                $pdf->page_text($x, $y, $text, $font, $size, $color);
            }
        </script>
    </body>
    </html>';

    // Use Dompdf to generate PDF
    $dompdf = new \Dompdf\Dompdf();
    $options = $dompdf->getOptions();
    $options->setIsRemoteEnabled(true);
    $dompdf->setOptions($options);
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Return PDF as response
    $pdfContent = $dompdf->output();
    
    $response = new Response($pdfContent);
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', 'attachment; filename="colis-export-' . date('Y-m-d') . '.pdf"');
    
    return $response;
}
private function exportCSV(array $colis): Response
{
    $csv = "ID;Code Tracking;Expéditeur;Destinataire;Statut;Date Création\n";
    
    foreach ($colis as $item) {
        // Get the latest status if available
        $lastStatus = null;
        if ($item->getStatuts() && !$item->getStatuts()->isEmpty()) {
            $lastStatus = $item->getStatuts()->last();
        }
        
        $csv .= $item->getId() . ";" .
                ($item->getCodeTracking() ?? 'N/A') . ";" .
                ($item->getExpediteur() ? $item->getExpediteur()->getNomEntrepriseIndividu() : 'N/A') . ";" .
                ($item->getDestinataire() ? $item->getDestinataire()->getNomEntrepriseIndividu() : 'N/A') . ";" .
                ($lastStatus ? $lastStatus->getTypeStatut()->name : 'N/A') . ";" .
                ($item->getDateCreation() ? $item->getDateCreation()->format('d/m/Y') : 'N/A') . "\n";
    }
    
    $response = new Response($csv);
    $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="colis-export.csv"');
    
    return $response;
}


//  #[Route("/print", name: "app_colis_print")]
// public function printSelection(Request $request): Response
// {
//     $ids = explode(',', $request->query->get('ids', ''));
//     $colis = $this->colisRepository->findBy(['id' => $ids]);
    
//     return $this->render('colis/print.html.twig', [
//         'colis' => $colis,
//     ]);
// }


// #[Route("/labels", name: "app_colis_labels")]
// public function generateLabels(Request $request): Response
// {
//     $ids = explode(',', $request->query->get('ids', ''));
//     $colis = $this->colisRepository->findBy(['id' => $ids]);
    
//     return $this->render('colis/labels.html.twig', [
//         'colis' => $colis,
//     ]);
// }

// #[Route('/print', name: 'app_colis_print')]


}
