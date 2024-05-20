<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountEmailType;
use App\Form\AccountGroupType;
use App\Form\AccountRoleType;
use App\Form\UserType;
use App\Form\UserUpdateType;
use App\Repository\StudentGroupRepository;
use App\Repository\UserRepository;
use App\Security\Voter\UserAccountVoter;
use App\Service\JWTService;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/inscription', name: 'app_new_user')]
    public function newUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, StudentGroupRepository $studentGroupRepository, PictureService $pictureService): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('app_dashboard');
        }
        
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $group = $studentGroupRepository->findOneByCode($form->get('code')->getData());

            if($form->get('plainPassword')->getData() !== $form->get('passwordConfirm')->getData())
            {
                $this->addFlash('warning', 'Les deux mots de passe ne sont pas identiques');                
            } else if(!$group || !$group->isOpen())
            {
                $this->addFlash('danger', 'Le code de classe est invalide');
            } else
            {
                $user->setPassword(
                    $hasher->hashPassword(
                        $user, 
                        $form->get('plainPassword')->getData()
                    )
                );

                $user->setStudentGroup($group);

                if($form->get('avatar')->getData())
                {
                    $pictureName = $pictureService->addAvatar($form->get('avatar')->getData());
                    $user->setAvatar($pictureName);
                }
                
                $em->persist($user);
                try
                {
                    $em->flush();
                    $this->addFlash('success', 'Le compte a été créé');
                    return $this->redirectToRoute('app_login');
                } catch(Exception $e) {
                    $this->addFlash('Danger', 'une erreur s\'est produite (détail : '.$e->getMessage().')');
                }
            }
        }

        
        
        $pageInfo = [
            'title' => 'Inscription apprenant',
        ];
        return $this->render('user/newUser.html.twig', [
            'pageInfo' => $pageInfo,
            'form' => $form,            
        ]);
    }

    #[Route('/compte/{id}', name: 'app_account')]
    #[IsGranted(UserAccountVoter::VIEW, subject: 'user')]
    public function account(User $user):Response
    {

        $pageInfo = [
            'title' => 'Informations de compte',
        ];
        return $this->render('user/account.html.twig', [
            'pageInfo' => $pageInfo,
            'user' => $user,
        ]);
    }

    #[Route('/modification_compte/{id}', name: 'app_update_account')]
    #[IsGranted(UserAccountVoter::EDIT, subject: 'user')]    
    public function updateAccount(User $user,Request $request, UserRepository $userRepository, EntityManagerInterface $em, PictureService $pictureService, UserPasswordHasherInterface $hasher, JWTService $jwt, MailerInterface $mailer):Response
    {
        
        //if($this->getUser()->getRoles()[0])
        $form = $this->createForm(UserUpdateType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if($form->get('deleteAvatar')->getData()) {
                $pictureService->deleteAvatar($user->getAvatar());
                $user->setAvatar('default.png');
            }else if($form->get('avatar')->getData())
            {
                $pictureName = $pictureService->addAvatar($form->get('avatar')->getData());
                if($pictureName) {
                    $pictureService->deleteAvatar($user->getAvatar());
                    $user->setAvatar($pictureName);
                }
                
            }
            $em->persist($user);
            try
            {
                $em->flush();
                $this->addFlash('success', 'Informations modifiées');
            }catch(Exception $e)
            {
                $this->addFlash('danger', 'une erreur s\'est produite (détail : '.$e->getMessage().')');
            }            

            return $this->redirectToRoute('app_account',['id'=>$user->getId()]);
        }

        $groupForm = $this->createForm(AccountGroupType::class, $user);
        $groupForm->handleRequest($request);

        if($groupForm->isSubmitted() && $groupForm->isValid()) {
            if($groupForm->get('deleteGroup')->getData())
            {
                $user->setStudentGroup(null);
            }
            $em->persist($user);
            try
            {
                $em->flush();
                $this->addFlash('success', 'Groupe modifié');
            }catch(Exception $e)
            {
                $this->addFlash('danger', 'une erreur s\'est produite (détail : '.$e->getMessage().')');
            }            

            return $this->redirectToRoute('app_account',['id'=>$user->getId()]);
        }

        $roleForm = $this->createForm(AccountRoleType::class, $user);
        $roleForm['roles']->setData('ROLE_ADMIN');
        $roleForm->handleRequest($request);

        if($roleForm->isSubmitted() && $roleForm->isValid())
        {
            if($roleForm->get('roles')->getData() === 'ROLE_USER')
            {
                $user->setRoles([]);
            } else
            {
                $user->setRoles([$roleForm->get('roles')->getData()]);
            }
            
            $em->persist($user);
            try
            {
                $em->flush();
                $this->addFlash('success', 'Role modifié');
            }catch(Exception $e)
            {
                $this->addFlash('danger', 'une erreur s\'est produite (détail : '.$e->getMessage().')');
            }            

            return $this->redirectToRoute('app_account',['id'=>$user->getId()]);
        }

        $emailForm = $this->createForm(AccountEmailType::class, $user);
        $emailForm->handleRequest($request);

        if($emailForm->isSubmitted() && $emailForm->isValid())
        {
            
            
            if($hasher->isPasswordValid($user, $emailForm->get('plainPassword')->getData()))
            {
                if($userRepository->findOneByEmail($emailForm->get('email')->getData()))
                {
                    $this->addFlash('danger', 'email déjà utilisé');
                } else
                {
                    $em->persist($user);
                    try
                    {
                        $em->flush();
                        $this->addFlash('success', 'Email modifié');
                        return $this->redirectToRoute('app_account',['id'=>$user->getId()]);

                    }catch(Exception $e)
                    {
                        $this->addFlash('danger', 'une erreur s\'est produite (détail : '.$e->getMessage().')');
                    }
                }
            } else
            {
                $this->addFlash('danger', 'mot de passe incorrect');
            }
        }

        $passwordForm = $this->createFormBuilder()
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-custom my-4',
                ],
                'label' => 'Obtenir le lien',
            ])
            ->getForm();
        $passwordForm->handleRequest($request);

        if($passwordForm->isSubmitted() && $passwordForm->isValid())
        {
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtreset'));
            
            $initEmail = (new TemplatedEmail())
                ->from('admin@classroom.e2c-app-factory.fr')
                ->to($user->getEmail())
                ->subject('Ne pas répondre : Réinitialisation de mot de passe')
                ->htmlTemplate('email/passwordRenewal.html.twig')
                ->context([
                    'user' => $user,
                    'token' => $token,
            ]);

            try
            {
                $mailer->send($initEmail);
                $this->addFlash('success', 'Un mail de réinitialisation a été envoyé');
                return $this->redirectToRoute('app_account',['id'=>$user->getId()]);
            } catch (TransportExceptionInterface $e)
            {
                $this->addFlash('danger', 'Une erreur s\'est produite lors de l\'envoi du mail (détail : '. $e->getMessage().')');
            }
        }
        $pageInfo = [
            'title' => 'Modification de compte : '.$user->getDisplayedName(),
        ];
        return $this->render('user/updateAccount.html.twig', [
            'pageInfo' => $pageInfo,
            'user' => $user,
            'form' => $form,
            'groupForm' => $groupForm,
            'roleForm' => $roleForm,
            'emailForm' => $emailForm,
            'passwordForm' => $passwordForm,
        ]);
    }

    #[Route('/enseignant/liste_utilisateurs', name: 'app_user_list')]
    public function usersManagement(UserRepository $userRepository):Response
    {
        $usersOn = $userRepository->findAllActives();
        $usersOff = $userRepository->findAllInactives();

        $pageInfo = [
            'title' => 'Gestion des utilisateurs'
        ];
        return $this->render('user/userManagement.html.twig', [
            'pageInfo' => $pageInfo,
            'users' => $usersOn,
            'offUsers' => $usersOff,          
        ]);
    }
}
