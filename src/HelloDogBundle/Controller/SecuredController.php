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
use Symfony\Component\HttpFoundation\JsonResponse;



class SecuredController extends Controller
{
    /**
    * @Route("/profile/account", name="profile_secured_account")
    * @Method("GET")
    * @Template()
    */
    public function accountAction(Request $request){
        //Asignar el FLAG
        if(!$request->getSession()->get('flag'))
        {$request->getSession()->set('flag',-1);}

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);

        //Cargar Datos Personales Usuario
        $id = $this->getUser() -> getId();
        $personal = $this->getDoctrine()->getRepository('HelloDogBundle:User')->find($id);

        return array(
            'flag' => $flag,
            'dataU' => $personal
        );
    }

    /**
     * @Route("/profile/password", name="profile_secured_password")
     * @Method("GET")
     * @Template()
     */
    public function passwordAction(Request $request){
        //Asignar el FLAG
        if(!$request->getSession()->get('flag'))
        {$request->getSession()->set('flag',-1);}

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);
    	
        return array(
            'flag' => $flag
        );
    }

    /**
     * @Route("/resend", name="secured_resend")
     * @Route("/profile/resend", name="profile_secured_resend")
     * @Method("GET")
     * @Template()
     */
    public function resendAction(Request $request)
    {
        if(!$request->getSession()->get('flag'))
        {$request->getSession()->set('flag',-1);}

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);
             
        return array( 'flag' => $flag);
    }


    /**
     * @Route("/restore/{key}", name="secured_restore")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function restoreAction($key, Request $request){
        if(!$request->getSession()->get('flag'))
        {$request->getSession()->set('flag',-1);}

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);

        if($request->isMethod('POST')){
            try{
                $email = $request->get('email');
                $em = $this->getDoctrine()->getManager();

                if(password_verify($email, $key))
                {   
                    $em = $this->getDoctrine()->getManager();
                    if($user = $em->getRepository('HelloDogBundle:User')->findOneBy(array('email'=>$email)))
                    {
                        $user->setPassword($request->get('password'));
                        $em->flush();
                        //ENVIO DE EMAIL
                        //$this->sendEmail($user->getUsername(),$user->getEmail());
                        $request->getSession()->set('flag',1);
                    }
                }else{
                    //NO COINCIDEN EMAIL WHIT KEY
                    $request->getSession()->set('flag',-4);
                }
            }catch(Exception $e){
                $request->getSession()->set('flag',-2);
            }
        }
             
        return array( 'flag'=>$flag, 'key'=>$key );
    }

/* POST */
    /**
    * @Route("/profile/account", name="edit_account")
    * @Method("POST")
    * @Template()
    */
    public function editAccountAction(Request $request){
        try{
            $id = $this->getUser()->getId();
            $email = $request->get('email');
            $username = $request->get('username');

            if( $this->verify_email($email,$this->getUser()->getEmail()) ){
                $em = $this->getDoctrine()->getManager();
                if($user = $em->getRepository('HelloDogBundle:User')->find($id))
                {
                    $user->setUsername($username);
                    $user->setEmail($email);

                    $em->flush();
                    $request->getSession()->set('flag',1);
                }else{
                    $request->getSession()->set('flag',-2);//NO EXISTE USUARIO
                }
            }else{
                $request->getSession()->set('flag',-5);//EMAIL NO DISPONIBLE
            }
        }catch(Exception $e){
            $request->getSession()->set('flag',-2);
        }

        return new RedirectResponse($this->generateUrl('profile_secured_account'));
    }

    /**
    * @Route("/profile/password", name="edit_password")
    * @Method("POST")
    */
    public function editPasswordAction(Request $request){
        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        try{
            $id = $this->getUser()->getId();
            $em = $this->getDoctrine()->getManager();
            if($user = $em->getRepository('HelloDogBundle:User')->find($id))
            {
                $passwordActual = $request->get('password');

                if(password_verify($passwordActual, $user->getPassword()))
                {
                    $user->setPassword($request->get('newPassword'));
                    $em->flush();

                    //Se envia correo Informando del cambio de Contraseña
                    //$this->sendEmail($this->getUser()->getUsername(),$this->getUser()->getEmail());

                    $request->getSession()->set('flag',1);
                    return new Response(1);
                }else{
                   $request->getSession()->set('flag',-3);//password es incorrecta
                   return new Response(-2);
                }
            }else{
               $request->getSession()->set('flag',-2);//No se encuentra usuario
               return new Response(-2);
            }
        }catch(Exception $e){
            $request->getSession()->set('flag',-2);//Error de procedimiento
        }
        return new Response(-2);
        //return new RedirectResponse($this->generateUrl('profile_secured_password'));
    }

    /**
    * @Route("/profile/resend", name="edit_resend")
    * @Route("/resend", name="edit_resend_public")
    * @Method("POST")
    * @Template()
    */
    public function editResendAction(Request $request){
        try{
            $email = $request->get('email');
            $em = $this->getDoctrine()->getManager();
            if($user = $em->getRepository('HelloDogBundle:User')->findBy(array('email'=>$email)))
            {
                //generar codigo
                $emailSecured =  password_hash( $email,PASSWORD_BCRYPT);
                if(strpos($emailSecured,'/') !== false){
                    $exist= true;
                    while($exist){
                        $emailSecured =  password_hash( $email, PASSWORD_BCRYPT);
                        if(strpos($emailSecured,'/') === false){
                            //ya no se ecuentra
                            $exist =false;
                        }
                    }
                }
                //enviar codigo de activacion
                //$this->sendEmailResend($email,$emailSecured);
                $request->getSession()->set('flag',2);
            }else{
                $request->getSession()->set('flag',-4);
            }
        }catch(Exception $e){
            $request->getSession()->set('flag',-2);
        }
        if($this->getUser())
            return new RedirectResponse($this->generateUrl('profile_secured_resend'));
        else
            return new RedirectResponse($this->generateUrl('secured_resend'));
    }


    //ENVIO DE EMAIL PASSWORD
    private function sendEmail($name, $email){
        $message = \Swift_Message::newInstance()
        ->setSubject('HelloDog - Contraseña')
        ->setFrom('contacto@hellodog.cl')
        ->setTo($email)
        ->setBody(
            $this->renderView(
                'HelloDogBundle:Email:email_password.html.twig',
                array('name'=> $name,'email'=>$email)
            ),'text/html'
        );
        $this->get('mailer')->send($message);
    }

    //ENVIO DE EMAIL CODIGO
    private function sendEmailResend($email, $codigo){
        $message = \Swift_Message::newInstance()
        ->setSubject('HelloDog - Restablecer Contraseña')
        ->setFrom('contacto@hellodog.cl')
        ->setTo($email)
        ->setBody(
            $this->renderView(
                'HelloDogBundle:Email:email_resend.html.twig',
                array('email'=>$email,'codigo'=>$codigo)
            ),'text/html'
        );
        $this->get('mailer')->send($message);
    }

    //CHECK SI  EXISTE EMAIL
    private function verify_email($email,$email_now){
        if($email != $email_now){
            $em = $this->getDoctrine()->getManager();
            if($user = $em->getRepository('HelloDogBundle:User')->findBy(array('email'=>$email)))
            {
                return false;
            }
        }
        return true;
    }

}