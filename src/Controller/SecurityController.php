<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('app_dashboard');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $pageInfo = [
            'title' => 'Connexion',
        ];

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'pageInfo' => $pageInfo,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path:'/oubli_mdp', name:'app_forgotten_password')]
    public function forgottenPassword(Request $request, UserRepository $userRepository, MailerInterface $mailer, JWTService $jwt):Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('app_dashboard');
        }
        
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Adresse mail',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ],
            ])
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-custom my-4',
                ],
                'label' => 'Envoyer',
            ])
            ->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user = $userRepository->findOneByEmail($form->get('email')->getData());
            if($user) 
            {
                if(!$user->isActive())
                {
                    $this->addFlash('danger', 'Votre compte est inactif, contactez l\'administrateur');
                    return $this->redirectToRoute('app_login');
                }
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
                    ->to($form->get('email')->getData())
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
                    return $this->redirectToRoute('app_login');
                } catch (TransportExceptionInterface $e)
                {
                    $this->addFlash('danger', 'Une erreur s\'est produite lors de l\'envoi du mail (détail : '. $e->getMessage().')');
                }
            } else
            {
                $this->addFlash('danger', 'Il n\'y a pas de compte associé à cette adresse mail');
            }
        }
        
        $pageInfo = [
            'title' => 'Mot de passe oublié',
        ];

        return $this->render('security/forgottenPassword.html.twig', [
            'pageInfo' => $pageInfo,
            'form' => $form,
        ]);
    }

    #[Route(path:'nouveau_mdp/{token}', name:'app_password_renewal')]
    public function passwordRenewal(string $token, Request $request, UserRepository $userRepository, EntityManagerInterface $em, JWTService $jwt, UserPasswordHasherInterface $hasher):Response
    {
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->signatureCheck($token, $this->getParameter('app.jwtreset'))) {

            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);
            
            
        } else {
            $this->addFlash('danger', 'Lien invalide');   
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createFormBuilder()
            ->add('plainPassword', PasswordType::class, [
                'attr'=>[
                    'class' => 'form-control mb-2'
                ],
                'label' => 'Nouveau mot de passe *',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire'
                    ]),
                    new PasswordStrength([
                        'minScore' => PasswordStrength::STRENGTH_WEAK,
                        'message' => 'Mot de passe trop faible'
                    ])
                ]
            ])
            ->add('confirmPassword', PasswordType::class, [
                'attr'=>[
                    'class' => 'form-control mb-2'
                ],
                'label' => 'Confirmation du mot de passe *',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire'
                    ])
                ]
            ])
            ->add('change', SubmitType::class, [
                'attr'=>[
                    'class' => 'btn btn-lg btn-custom my-4'
                ],
                'label' => 'Valider',
            ])
            ->getForm();
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) 
            {
                if($user)
                {
                    if($form->get('plainPassword')->getData() !== $form->get('confirmPassword')->getData())
                    {
                        $this->addFlash('warning', 'Les deux mots de passe ne sont pas identiques');                
                    } else {
                        $user->setPassword(
                            $hasher->hashPassword(
                                $user, 
                                $form->get('plainPassword')->getData()
                            )
                        );

                        $em->persist($user);

                        try
                        {
                            $em->flush();
                            $this->addFlash('success', 'Mot de passe modifié, vous pouvez vous reconnecter');
                            return $this->redirectToRoute('app_login');
                        } catch(Exception $e)
                        {
                            $this->addFlash('danger', 'Une erreur s\'est produite (détail : '.$e->getMessage().')');
                        }
                    }
                } else {
                    $this->addFlash('warning', 'Compte inexistant');
                    return $this->redirectToRoute('app_login');
                }
            }
        
        $pageInfo = [
            'title' => 'Réinitialisation de mot de passe',
        ];

        return $this->render('security/passwordRenewal.html.twig', [
            'pageInfo' => $pageInfo,
            'form' => $form,
        ]);
    }
}
