<?php

namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InscriptionController extends AbstractController
{

    /**
     * @Route("/Acceuil", name="Acceuil")
     */
    public function number()
    {
        $number = random_int(0, 100);

        return $this->render('Inscription/accueil.html.twig',
            [ 'number' => $number, ]
        );

//        return new Response(
//            '<html><body>Ceci est une première page Symfony<br>
//                    Lucky number: '.$number.'</body></html>'
//        );
    }
}
?>

