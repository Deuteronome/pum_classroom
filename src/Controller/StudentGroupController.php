<?php

namespace App\Controller;

use App\Entity\StudentGroup;
use App\Form\StudentGroupType;
use App\Repository\StudentGroupRepository;
use App\Service\RegistrationCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StudentGroupController extends AbstractController
{
    #[Route('/enseignant/nouvelle_classe', name: 'app_new_group')]
    public function newGroup(Request $request, RegistrationCodeService $registrationCodeService, EntityManagerInterface $em): Response
    {
        $code = $registrationCodeService->generateGroupCode();
        $group = new StudentGroup();
        $group -> setCode($code);
        
        $form = $this->createForm(StudentGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            
            if($form->get('endAt')->getData()!==null && $form->get('startAt')->getData()>$form->get('endAt')->getData())
            {
                $this->addFlash('danger', 'la date de clôture doit être postérieure à la date de démarrage');
            } else
            {
                $em->persist($group);
                try {
                    $em->flush();
                    $this->addFlash('success', 'la classe '.$group->getGroupName().' a été créée');
                    return $this->redirectToRoute('app_group_list');
                } catch (Exception $e) {
                    $this->addFlash('Danger', 'une erreur s\'est produite (détail : '.$e->getMessage().')');                    
                }                
            }    
            
            //return $this->redirectToRoute('app_new_group');
            
        }
        
        $pageInfo = [
            'title' => 'Création de classe'
        ];
        return $this->render('student_group/newGroup.html.twig', [
            'pageInfo' => $pageInfo,
            'code' => $code,
            'form' => $form,
        ]);
    }

    #[Route('/enseignant/classes', name: 'app_group_list')]
    public function groupManagement(StudentGroupRepository $studentGroupRepository):Response
    {
        $groups = $studentGroupRepository->findAll();
        
        $pageInfo = [
            'title' => 'Gestion des classes'
        ];
        return $this->render('student_group/groupManagement.html.twig', [
            'pageInfo' => $pageInfo,
            'groups' => $groups,            
        ]);
    }
}
