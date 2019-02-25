<?php
/**
 * Created by PhpStorm.
 * User: Hicham
 * Date: 18/09/2018
 * Time: 16:13
 */

namespace CoreBundle\Controller;

use CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UserController extends Controller
{
    public function loginAction(Request $request)
    {
        $username= $request->request->get('username');
        $password= $request->request->get('password');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:User')->findOneBy(array('username' => $username));
        if($user){
            if(password_verify($password,$user->getPassword())){
                    $session = $request->getSession();
                    $session->set('userId',$user->getId());
                    $session->set('username',$user->getUsername());
                    $session->set('userRole',$user->getRole());
                    $session->set('blocked',$user->getBlocked());
                    return $this->redirectToRoute('core_homepage');

                }
        }
        $request->getSession()->getFlashBag()->add('error', 'Invalid username or password !');
        return $this->redirectToRoute('register_route');

    }

    public function logoutAction(Request $request)
    {
        $session = $request->getSession();
        $session->invalidate(); //here we can now clear the session.

        return $this->redirectToRoute('core_homepage');


    }

    public function registerAction(Request $request)
    {

        return $this->render('CoreBundle:User:register.html.twig');


    }

    public function signinAction(Request $request)
    {
        $username= $request->request->get('username');
        $password= $request->request->get('password');
        $confirmPassword= $request->request->get('confirmPassword');

        if($password == $confirmPassword){


            $em = $this->getDoctrine()->getManager();

            $rslt = $em->getRepository('CoreBundle:User')->findOneByUsername($username);
            if($rslt != null){
                $request->getSession()->getFlashBag()->add('signError', 'Username already exist !');
                return $this->redirectToRoute('register_route');
            }
            $password = password_hash($password,PASSWORD_DEFAULT);
            $user = new User();
            $user->setUsername($username);
            $user->setPassword($password);
            $em->persist($user);
            $em->flush();

            $session = $request->getSession();
            $session->set('username',$username);
            $session->set('userId',$user->getId());
            $session->set('userRole',$user->getRole());
            $session->set('blocked',$user->getBlocked());

            return $this->redirectToRoute('core_homepage');
        }
        else{
            $request->getSession()->getFlashBag()->add('signError', 'Password and confirmation does not match !');
            return $this->render('CoreBundle:User:register.html.twig',array('error' => 1));
        }


    }

    public function profileAction()
    {

        return $this->render('CoreBundle:User:profile.html.twig');


    }



    public function updatePersonalInfoAction(Request $request)
    {
        $newUsername= $request->request->get('username');
        $userId = $request->getSession()->get('userId');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:User')->find($userId);
        if($user){
            $user->setUsername($newUsername);
            $request->getSession()->set('username',$newUsername);
            $em->flush();

        }

        return $this->render('CoreBundle:User:profile.html.twig',array('error' => 1));


    }

    public function updateSecurityInfoAction(Request $request)
    {
        $oldPassword= $request->request->get('opassword');
        $newPassword= $request->request->get('npassword');
        $confirmPassword= $request->request->get('cpassword');

        $userId = $request->getSession()->get('userId');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:User')->find($userId);


        if(password_verify($oldPassword,$user->getPassword())){
            if($newPassword != $confirmPassword){
                return $this->render('CoreBundle:User:profile.html.twig',array('error' => 2));
            }
            $user->setPassword(password_hash($newPassword,PASSWORD_DEFAULT));
            $em->flush();
            return $this->render('CoreBundle:User:profile.html.twig',array('error' => 0));

        }
        else{
            return $this->render('CoreBundle:User:profile.html.twig',array('error' => 1));
        }

    }

    public function deleteUserAction(Request $request)
    {
        $id = $request->query->get('id');
        if($id == $request->getSession()->get('userId')){
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('CoreBundle:User')->find($id);

            $commands = $em->getRepository('CoreBundle:Command')->findby(array('user'=>$user));
            foreach ($commands as $cmd){
                $commandProducts = $em->getRepository('CoreBundle:CommandProduct')->findby(array('command'=>$cmd));
                foreach ($commandProducts as $cp){
                    $em->remove($cp);
                }
                $em->remove($cmd);
            }
            $em->remove($user);
            $em->flush();

            return $this->redirectToRoute('logout_route');
        }
            return new Response("Access Denied !");

    }



}