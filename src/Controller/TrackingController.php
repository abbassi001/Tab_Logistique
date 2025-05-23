<?php

namespace App\Controller;

use App\Repository\ColisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrackingController extends AbstractController
{
    #[Route('/suivi', name: 'app_tracking')]
    public function index(Request $request, ColisRepository $colisRepository): Response
    {
        $search = $request->query->get('search');
        $colis = null;
        $error = null;

        if ($search) {
            // Rechercher le colis par code de tracking
            $colis = $colisRepository->findOneBy(['codeTracking' => $search]);
            
            if (!$colis) {
                $error = "Aucun colis trouvé avec le numéro de suivi : " . $search;
            }
        }

        return $this->render('tracking/index.html.twig', [
            'colis' => $colis,
            'search' => $search,
            'error' => $error
        ]);
    }
}