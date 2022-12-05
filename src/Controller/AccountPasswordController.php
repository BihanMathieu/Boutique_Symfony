<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{
    #[Route('/compte/modifier-mon-mot-de-passe', name: 'account_password')]
    public function index(Request $request, UserPasswordHasherInterface $encoder, ManagerRegistry $doctrine): Response
    {
        $notification = null;


        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $oldPassword = $form->get('old_password')->getData();
            if($encoder->isPasswordValid($user,$oldPassword)){
                $newPassword = $form->get('new_password')->getData();
                $password = $encoder->hashPassword($user,$newPassword);

                $user->setPassword($password);
                $doctrine = $doctrine->getManager();
                $doctrine->persist($user);
                $doctrine->flush();
                $notification = "Votre mot de passe a bien été mis a jour.";
            } else {
                $notification = "Votre mot de passe actuelle n'est pas valide";
            }
        }

        return $this->render('account/password.html.twig',[
            'form' => $form->createView(),
            'notification' => $notification,
        ]);
    }
}
