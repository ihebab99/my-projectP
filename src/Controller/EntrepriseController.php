<?php

namespace App\Controller;

use App\Entity\Entreprise;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function index(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entreprise = new Entreprise();
        $entreprise->setTitre('My-company');
        $entreprise->setEmail('contact@mycompany.com');
        $entreprise->setSpecialite('developpement des affaires personelle');
        $entreprise->setCreatedAt(new \DateTimeImmutable());
        $entityManager->persist($entreprise);
        $entityManager->flush();
        return $this->render('entreprise/index.html.twig', [
            'id'=>$entreprise->getId(),
            //'controller_name' => 'EntrepriseController',
        ]);
    }
    /**
     * @Route("/entreprise/{id}", name="entreprise_show")
     */
    public function show($id){
        $entrperise=$this->getDoctrine()
            ->getRepository(Entreprise::class)
            ->find($id);

        if (!$entrperise) {
            throw $this->createNotFoundException(
                'No entreprise found for id '.$id
            );
        }
        return $this->render('entreprise/show.html.twig', [

            'entreprise' =>$entrperise
        ]);
}
}


