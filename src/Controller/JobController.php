<?php

namespace App\Controller;
use App\Entity\Image;
use App\Entity\Job;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Candidature;
use App\Form\JobType;
use Symfony\Component\HttpFoundation\Session\Session;
class JobController extends AbstractController
{
    #[Route('/job', name: 'job')]
    public function index(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $job = new Job();
        $job->setType('Architecte');
        $job->setCompany('IDEV');
        $job->setDescription('Software');
        $job->setExpiresAt(new \DateTimeImmutable());
        $job->setEmail('ihebabdelmoumen3@gmail.com');
        $image = new Image();
        $image->setUrl("https://cdn.pixabay.com/photo/2015/10/30/10/03/gold-1013618_960_720.jpg");
        $image->setAlt('job de rêves');
        $job->setImage($image);
        $candidature1 = new Candidature();
        $candidature1 -> setCandidat("iheb");
        $candidature1 -> setContenu(" fromation J2EE");
        $candidature1 -> setDateC(new \DateTime());
        $candidature2 = new Candidature();
        $candidature2 -> setCandidat("ahlem");
        $candidature2 ->setContenu(" fromation ANGULAR");
        $candidature2 -> setDateC(new \DateTime());
        $candidature1->setJob($job);
        $candidature2->setJob($job);
        $entityManager->persist($job);
        $entityManager->persist($candidature1);
        $entityManager->persist($candidature2);

        $entityManager->flush();

        return $this->render('job/index.html.twig', [
            'id'=>$job->getId(),
            //'controller_name' => 'JobController',
        ]);
    }

    /**
     * @Route("/job/{id}", name="job_show")
     */
    public function show($id, Request $request)
    {
        $job = $this->getDoctrine()
            ->getRepository(Job::class)
            ->find($id);

        $em = $this->getDoctrine()->getManager();
        $listeCandidatures = $em->getRepository(Candidature::class)
            ->findBy(['Job' => $job]);
        $publicPath = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath().'/uploads/jobs/';
        if (!$job) {
            throw $this->createNotFoundException(
                'No job found for id '.$id
            );
        }
        return $this->render('job/show.html.twig', [
            'listeCandidatures' => $listeCandidatures,
            'job' =>$job,
            'publicPath' =>$publicPath
        ]);
    }
    /**
     * @Route("/", name="home")
     */
    public function home(Request $request){

        $form=$this->createFormBuilder()
            ->add("critere",TextType::class)
            ->add("valider",SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Candidature::class);
        $lesCandidats=$repo->findAll();

        if($form->isSubmitted()){
            $data= $form->getData();
            $lesCandidats=$repo->recherche($data['critere']);
        }
        return $this->render('job/home.html.twig',[
           'lesCandidats'=>$lesCandidats,
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/ajouter", name="ajouter")
     */
    public function ajouter(Request $request){
        $candidat = new Candidature();
        $fb= $this->createFormBuilder($candidat)
            ->add('candidat', TextType::class)
            ->add('contenu', TextType::class, array("label"=>"contenu"))
            ->add('dateC', DateType::class)

            ->add('job', EntityType::class,[
                'class'=>Job::class,
                'choice_label'=>'type',
            ])
            ->add('valider',SubmitType::class);
        $form=$fb->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($candidat);
            $em->flush();
            $session= new Session();
            $session->getFlashBag()->add('notice', 'candidat ajouté avec success');
            return $this->redirectToRoute('home');
        }
        return $this->render('job/ajouter.html.twig',
        ['f'=>$form->createView()]);
    }


    // add job
    /**
     * @Route("/add", name="add")
     */
    public function add(Request $request){
        $publicPath="uploads/jobs/";
        $job= new Job();
        $form= $this->createForm("App\Form\JobType",$job);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            /*
             * @var UploadFile $image
             */
            $image=$form->get('image')->getData();
            $em=$this->getDoctrine()->getManager();
            if ($image){
                $imageName=$job->getDescription().'.'.$image->guessExtension();
                $image->move($publicPath,$imageName);
                $job->setImage($imageName);
            }
            $em->persist($job);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('job/ajouter.html.twig',
            ['f'=>$form->createView()]);

    }


    //delete candidature
    /**
     * @Route ("/supp/{id}", name="cand_delete")
     */
    public function delete(Request $request, $id): Response
    {
        $c=$this->getDoctrine()->getRepository(Candidature::class)
            ->find($id);
        if(!$c){
            throw $this->createNotFoundException(
                'no canddiat found for id' .$id
            );
        }

            $entityManager=$this->getDoctrine()->getManager();
            $entityManager->remove($c);

            $entityManager->flush();
            return $this->redirectToRoute('home');
    }


    //update candidature
    /**
     * @Route("/editU/{id}", name="edit_user")
     * Method({"GET","POST"})
     */
    public function edit(Request $request, $id)
    { $candidat = new Candidature();
        $candidat = $this->getDoctrine()
            ->getRepository(Candidature::class)
            ->find($id);
        if (!$candidat) {
            throw $this->createNotFoundException(
                'No candidat found for id '.$id
            );
        }
        $fb = $this->createFormBuilder($candidat)
            ->add('candidat', TextType::class)
            ->add('contenu', TextType::class, array("label" => "Contenu"))
            ->add('dateC', DateType::class)
            ->add('job', EntityType::class, [
                'class' => Job::class,
                'choice_label' => 'type',
            ])
            ->add('Valider', SubmitType::class);
        // générer le formulaire à partir du FormBuilder
        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('job/ajouter.html.twig',
            ['f' => $form->createView()] );
    }

    /**
     * @Route("/listejob", name="listejob")
     */

    public function afficherList(Request $request){

        $form=$this->createFormBuilder()
            ->add("critere",TextType::class)
            ->add("valider",SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Job::class);
        $lesJobs=$repo->findAll();

        if($form->isSubmitted()){
            $data= $form->getData();
            $lesJobs=$repo->recherche($data['critere']);
        }
        return $this->render('job/liste.html.twig',[
            'lesJobs'=>$lesJobs,
            'form1'=>$form->createView()
        ]);
    }

    /**
     * @Route ("/delete/{id}", name="job_delete")
     */
    public function deleteJob(Request $request, $id): Response
    {
        $c=$this->getDoctrine()->getRepository(Job::class)
            ->find($id);
        if(!$c){
            throw $this->createNotFoundException(
                'no job found for id' .$id
            );
        }

        $entityManager=$this->getDoctrine()->getManager();
        $entityManager->remove($c);

        $entityManager->flush();
        return $this->redirectToRoute('listejob');
    }

    /**
     * @Route("/editU/{id}", name="edit_job")
     * Method({"GET","POST"})
     */
    public function editjob(Request $request, $id)
    { $job = new Job();
        $job = $this->getDoctrine()
            ->getRepository(Job::class)
            ->find($id);
        if (!$job) {
            throw $this->createNotFoundException(
                'No job found for id '.$id
            );
        }
        $fb = $this->createFormBuilder($job)
            ->add('job', TextType::class)
            ->add('type', TextType::class, array("label" => "Type"))
            ->add('company', DateType::class)
            ->add('description', DateType::class)
            ->add('expires_at', DateType::class)
            ->add('email', DateType::class)
            ->add('image', DateType::class)


            ->add('Valider', SubmitType::class);
        // générer le formulaire à partir du FormBuilder
        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('job/ajouter.html.twig',
            ['fo' => $form->createView()] );
    }


}
