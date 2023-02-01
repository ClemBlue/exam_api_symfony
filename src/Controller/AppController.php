<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Films;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppController extends AbstractController
{

    private $doctrine;

    //construct
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/create', name: 'create_film', methods: ['POST'])]
    public function create(Request $request)
    {
        $films = new Films();
        $films->setNom($request->request->get('nom'));
        $films->setSynopsis($request->request->get('synopsis'));
        $films->setType($request->request->get('type'));
        $films->setDateCreation(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($films);
        $em->flush();

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route('/getall', name: 'get_all_film', methods: ['GET'])]
    public function getAll():JsonResponse
    {
        $films = $this->doctrine->getRepository(Films::class)->findAll();

        $data = [];
        foreach ($films as $film) {
            $data[] = [
                'id' => $film->getId(),
                'nom' => $film->getNom(),
                'synopsis' => $film->getSynopsis(),
                'type' => $film->getType(),
                'date_creation' => $film->getDateCreation(),
            ];
        }
        if (!$data) {
            return new JsonResponse(['message' => 'Aucun films trouvé'], 404);
        }
    
        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    
    }

    #[Route('/get/{id}', name: 'get_film', methods: ['GET'])]
    public function getSingle($id): JsonResponse
    {
        $film = $this->doctrine->getRepository(Films::class)->find($id);
        if (!$film) {
            return new JsonResponse(['message' => 'Pas de film trouvé avec cet ID'], 404);
        }

        $data = [
            'id' => $film->getId(),
            'nom' => $film->getNom(),
            'synopsis' => $film->getSynopsis(),
            'type' => $film->getType(),
            'date_creation' => $film->getDateCreation(),
        ];
    
        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }
}
