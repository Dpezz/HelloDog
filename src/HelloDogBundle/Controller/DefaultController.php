<?php

namespace HelloDogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use HelloDogBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="demo_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        //Asignar el FLAG
        if(!$request->getSession()->get('flag'))
            $request->getSession()->set('flag',-1);

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);

        return array('flag'=>$flag);
    }

    /**
     * @Route("/login", name="demo_login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        //Asignar el FLAG
        if(!$request->getSession()->get('flag'))
            $request->getSession()->set('flag',-1);

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);

        //return new Response($request->getSession()->get('codigo'));
        if($request->getSession()->get('codigo')){
            if($request->getSession()->get('username'))
            {return new RedirectResponse($this->generateUrl('demo_profile'));}
        }else{
            return new RedirectResponse($this->generateUrl('demo_index'));
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }
        return array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            'flag' =>   $flag,
        );
    }


    /**
     * @Route("/profile/login_check", name="_demo_security_check")
     */
    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/profile/logout", name="_demo_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/check_codigo", name="check_codigo")
     * @Method("POST")
     */
    public function codigoAction(Request $request)
    {
        $codigos = array('abc123','abc120','abc121','abc122','HDMI1234-B');

        $codigo = $request->get('codigo');
        if(in_array($codigo,$codigos)){
            $request->getSession()->set('codigo',$codigo);
            $request->getSession()->set('flag',1);
            return $this->redirect($this->generateUrl('demo_login'));
        }
        $request->getSession()->set('flag',-2);
        return $this->redirect($this->generateUrl('demo_index'));
    }

    /**
     * @Route("/create", name="create")
     * @Method("POST")
     */
    public function createUser(Request $request)
    {
        $flag = 0;
        try{
            $email = $request->get('email');
            $em = $this->getDoctrine()->getManager();
            if(! $data = $em->getRepository('HelloDogBundle:User')->findBy(array('email'=>$email))){
                $user = new User();

                $username = $request->get('username');

                $user->setUsername($username);
                $user->setEmail($email);
                $user->setPassword($request->get('password'));

                $user->setCreateAt(new \DateTime('now'));
                $user->setRole("ROLE_USER");

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                //$this->sendEmail($username,$email);//enviar email usuario
                //$this->sendEmailAdmin($username,$email,$fono);//enviar email admin
                $request->getSession()->set('flag',1);
            }else{
                $request->getSession()->set('flag',-2);
            }
        }catch(Exception $e){
            $request->getSession()->set('flag',-2);
        }
        return $this->redirect($this->generateUrl('demo_login'));
    }
}
