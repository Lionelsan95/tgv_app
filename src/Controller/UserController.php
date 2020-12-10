<?php

namespace App\Controller;

use App\Entity\PriceHistory;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\Security as UserSecurity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 *
 */
class UserController extends AbstractController
{
    public function __construct()
    {
    }

    /**
     * @Route("/user", name="user_index", methods={"GET"})
     */
    public function admin(UserRepository $userRepository): Response
    {

        return $this->isGranted('ROLE_ADMIN') ?
                $this->render('user/index.html.twig', [
                'users' => $userRepository->findAll(),
                ])
                :
                ($this->isGranted('IS_AUTHENTICATED_FULLY')  ?
                    $this->render('user/accueil.html.twig', [
                        'user' => $this->getUser(),
                        'now' => date('H:i')
                    ]):
                    $this->render('security/login.html.twig', [
                        'user' => $this->getUser(),
                        'now' => date('H:i')
                    ])
                );
    }

    /**
     * @Route("/user/register", name="user_register", methods={"GET","POST"})
     */
    public function register(Request $request, GuardAuthenticatorHandler $authenticatorHandler, LoginFormAuthenticator $formAuthenticator, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        if($this->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('user_index');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Encoding the password
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // login after registration
            return $authenticatorHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $formAuthenticator,
                'main'
            );
            //return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/user/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'now' => date('d/m H:i')
        ]);
    }

    /**
     * @Route("/admin/user/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $em = $this->getDoctrine()->getManager();
        $actions = $em->getRepository('App:Actions')->findAll();
        $return = null;
        $users = [];
        foreach($em->getRepository('App:User')->findAll() as $user)
        {
            if(!in_array('ROLE_ADMIN', $user->getRoles()))
                $users[]=$user;
        }

        if ($request->getMethod() == 'GET')
            $return = $return ?? $this->render('user/edit.html.twig', [
                'user' => $user,
                'users'=>$users,
                'actions' => $actions,
            ]);

        $solde = (float) $request->request->get('solde') ?? 0;
        $commentaire = $request->request->get('commentaire') ?? '';
        $idAction = $request->request->get('action') ?? 0;
        $error = $solde <= 0 ? 'Solde incorrect' : ($solde == $user->getSolde() ? 'Solde inchangé':'');

        if(trim($error)=='' && $idAction > 0)
        {
            $action = $em->getRepository('App:Actions')->find($idAction);
            $history = new PriceHistory();
            $history->setUser($user);
            $history->setOldPrice($user->getSolde());
            $history->setCommentaire($commentaire);
            $history->setAction($action);

            $user->setSolde($solde);
            $user->addPriceHistory($history);

            $em->persist($history);

            try {
                $em->flush();
                $return = $return ?? $this->redirectToRoute('user_show',['id'=>$user->getId()]);
            } catch (\Exception $e) {
                $error = 'Unable to record in database';
            }
        }

        $return = $return ?? $this->render('user/edit.html.twig', [
            'user' => $user,
            'users' => $users,
            'actions' => $actions,
            'error' => $error
        ]);

        return  $return;
    }

    /**
     * @Route("/admin/user/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route(
     *     path="/user/passwordforgotten",
     *     name="user_passwordforgotten",
     *     methods={"GET", "POST"}
     * )
     */
    public function forgot(Request $request, UserSecurity $appSecurity)
    {
        if(!$this->isGranted('ROLE_USER'))
        {
            if ($request->getMethod() == "GET")
                return $this->render('user/forgot_pwd.html.twig');
            else if ($request->getMethod() == "POST" && $email = $request->request->get('email'))
            {
                $em = $this->getDoctrine()->getManager();

                $user = $em->getRepository('App:User')->findOneBy([
                    'email' => $email,
                ]);

                if ($user) {

                    $appSecurity->recoverPassword($user); //Generating and saving recover password

                    $appSecurity->sendMail([
                        "subject"=>"Password recovery",
                        "from"=>"noreply@thegreatvillage.com",
                        "to"=>$user->getEmail(),
                        "body"=>$this->renderView('user/password_recover_mail.html.twig',[
                            'name' => $user->getFirstname(). " ". $user->getLastname(),
                            'link'=> $request->getSchemeAndHttpHost().$this->generateUrl('user_changepassword', [
                                    'code'=>$user->getRecoverHash(),
                                    UrlGeneratorInterface::ABSOLUTE_PATH
                                ])
                        ])
                    ]);

                    $this->addFlash('success', "Email sent ! ");
                } else
                    return $this->render('user/forgot_pwd.html.twig', [
                        'error' => 'Unable to find this user!'
                    ]);
            }
            else
                $this->addFlash('error', "unable to find all params !");
        }
        else
            $this->addFlash('error', 'You are not allowed to access this page !');
        return $this->redirectToRoute('index');
    }

    /**
     * @Route(
     *     path="/resetpassword/user/{code}",
     *     name="user_changepassword",
     *     methods={"GET", "POST"}
     * )
     */
    public function changePassword(Request $request, $code, UserSecurity $appSecurity)
    {

        if(!$this->isGranted('ROLE_USER')) {
            $em = $this->getDoctrine()->getManager();

            if ($request->getMethod() == "GET") {
                $user = $em->getRepository('App:User')->findOneBy([
                    "recoverHash" => $code,
                    "enabled" => false,
                ]);

                if ($user) {
                    // @TODO Test if the link has not expired
                    return $this->render('user/reset.html.twig', [
                        "email" => $user->getEmail()
                    ]);
                } else
                    $this->addFlash('error', "Wrong link or already desactived -- $code!");

            } else if ($request->getMethod() == "POST") {
                // @TODO change the password
                $user = $em->getRepository('App:User')->findOneBy([
                    "email"=>$request->request->get('email')
                ]);

                if($user && $appSecurity->changePassword($user, $request->request->get('password') ?? ''))
                {
                    $this->addFlash('success', 'Password successfully modified !');
                    $this->redirectToRoute('app_login');
                }else
                {
                    $this->addFlash('error', "Error during updating process!");

                    // @TODO Test if the link has not expired
                    return $this->render('user/reset.html.twig', [
                        "email" => $user->getEmail()
                    ]);
                }


            } else
                $this->addFlash('error', 'You are not allowed to access this page !');

        }
        return $this->redirectToRoute('index');
    }

    /**
     * @Route(
     *     path="/admin/user/block/{id}",
     *     name="user_block",
     *     methods={"POST"}
     * )
     * @param Request $request
     */
    public function block(Request $request, User $user)
    {
        $return = 0;
        if($this->isCsrfTokenValid('block'.$user->getId(), $request->request->get('_token'))){
            $entityManager = $this->getDoctrine()->getManager();
            $user->setEnabled(!$user->getEnabled());
            $entityManager->flush();
            $return=1;
        }
        return $this->json($return);
    }



    /**
     * @Route(
     *     path="/user/edit/{id}",
     *     name="user_edit_profile",
     *     methods={"GET", "POST"}
     * )
     * @param Request $request
     */
    public function editUser(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $return = null;
        if ($request->getMethod() == 'GET')
            $return = $return ?? $this->render('user/modif.html.twig', [
                    'user' => $user,
                ]);

        $firstname = $request->request->get('firstname') ?? false;
        $lastname = $request->request->get('lastname') ?? false;
        $email = $request->request->get('email') ?? false;
        $organisation = $request->request->get('organisation') ?? false;

        if($firstname && $lastname && $email && $organisation)
        {
            $user->setEmail($email);
            $user->setFirstName($firstname);
            $user->setLastName($lastname);
            $user->setEmail($email);
            $user->setOrganisation($organisation);

            try {
                $em->flush();
                $return = $return ?? $this->redirectToRoute('user_index');
            } catch (\Exception $e) {
                $error = 'Unable to record in database';
            }
        }
        else
        {
            $error = 'Invalid form';
        }

        $return = $return ?? $this->render('user/modif.html.twig', [
                'user' => $user,
                'error' => $error
            ]);

        return  $return;
    }



    /**
     * @Route("/admin/new/user", name="admin_user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserSecurity $appSecurity): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //Setting new user role
            $role[] = $request->request->get('role') ?? 'ROLE_USER';
            $user->setRoles($role);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            if ($user) {
                $appSecurity->recoverPassword($user); //Generating and saving recover password
                $appSecurity->sendMail([
                    "subject"=>"Welcome greater",
                    "from"=>"noreply@thegreatvillage.com",
                    "to"=>$user->getEmail(),
                    "body"=>$this->renderView('user/welcome_user_mail.html.twig',[
                        'name' => $user->getFirstname(). " ". $user->getLastname(),
                        'link'=> $request->getSchemeAndHttpHost().$this->generateUrl('user_changepassword', [
                                'code'=>$user->getRecoverHash(),
                                UrlGeneratorInterface::ABSOLUTE_PATH
                            ])
                    ])
                ]);

                $this->addFlash('success', "Email sent ! ");
            }

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/newUser.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/list/user", name="user_list", methods={"POST", "GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function userList(Request $request)
    {
        $users = [];
        $i=0;
        foreach($this->getDoctrine()->getManager()->getRepository('App:User')->findAll() as $user)
            $users[]= [
                'id' => ++$i,
                'firstName' => '<a href="'.$this->generateUrl('user_show',['id'=>$user->getId()], UrlGeneratorInterface::ABSOLUTE_URL).'">'.$user->getFirstName().'</a>',
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'organization' => $user->getOrganisation(),
                'balance' => $user->getSolde()
                ];

        return $this->json($users);
    }
}
