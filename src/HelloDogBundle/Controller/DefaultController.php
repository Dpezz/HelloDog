<?php

namespace HelloDogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use HelloDogBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/codigo", name="demo_codigo")
     * @Method("GET")
     * @Template()
     */
    public function codigoAction(Request $request){
        //Asignar el FLAG
        if(!$request->getSession()->get('flag'))
            $request->getSession()->set('flag',-1);

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);

        return array('flag'=>$flag);
    }

    /**
     * @Route("/bienvenido", name="demo_welcome")
     * @Method("GET")
     * @Template()
     */
    public function welcomeAction(Request $request){
        //Asignar el FLAG
        if(!$request->getSession()->get('flag'))
            $request->getSession()->set('flag',-1);

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);

        return array('flag'=>$flag);
    }

    /**
     * @Route("/", name="demo_login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction(Request $request){
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }
        return array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
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
    public function codigoCheck(Request $request)
    {
        $codigo = $request->get('codigo');

        foreach ($this->getJsonKey() as $key => $value) {  
            foreach ($value['keys'] as $clave => $valor) {
                if($valor['clave'] == $codigo){
                    $request->getSession()->set('codigo',$codigo);
                    $request->getSession()->set('flag',1);
                    $this->editJsonKey($codigo);
                    return $this->redirect($this->generateUrl('demo_welcome'));
                }
            }
        }
        $request->getSession()->set('flag',-2);
        return $this->redirect($this->generateUrl('demo_codigo'));
    }

    /**
     * @Route("/create_user", name="create")
     * @Method("POST")
     */
    public function createUser(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $flag = 0;
        try{
            $email = $request->get('email');
            $em = $this->getDoctrine()->getManager();
            if(! $data = $em->getRepository('HelloDogBundle:User')->findBy(array('email'=>$email))){
                $user = new User();

                $username = $request->get('username');

                $user->setUsername($username);
                $user->setNamedog($request->get('namedog'));
                $user->setEmail($email);
                $user->setPassword($request->get('password'));
                $user->setCreateAt(new \DateTime('now'));
                $user->setRole("ROLE_USER");

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->sendEmail($username,$email);//enviar email usuario
                //$this->sendEmailAdmin($username,$email,$fono);//enviar email admin

                // Poner el nombre del firewall de tu aplicación
                $firewall = 'demo_secured_area';  
          
                //puedes sacar así los roles del user  
                $roles = $user->getRoles();  
          
                // Finalmente logueamos al usuario  
                $token = new UsernamePasswordToken($user, null, $firewall, $roles); 
                $this->get('security.context')->setToken($token);  
                $session = $this->get('session');
                $session->set('_security_'.$firewall,serialize($token));

                $request->getSession()->set('flag',2);
                return new Response(1);
            }else{
                $request->getSession()->set('flag',-2);
                return new Response(0);
            }
        }catch(Exception $e){
            $request->getSession()->set('flag',-2);
        }
        return new Response(0);
    }

    private function getJsonKey(){
        try{
            $url = "keys/keys.json";
            if(file_exists($url))
            {
                $file = file_get_contents($url);
                $json = json_decode($file,true);
                return $json['key'];
            }
        }catch(exception $e){
        }
        return null;
    }

    private function editJsonKey($data){
        $url = "keys/keys.json";
        if(file_exists($url)){
            $file = file_get_contents($url);
            $json = json_decode($file,true);

            foreach ($json['key'] as $key => $value) {
                foreach ($value['keys'] as $clave => $valor) {
                    if($valor['clave'] == $data){
                        $json['key'][$key]['keys'][$clave]['active'] = 1; 
                    }
                }
            }
            $json = json_encode($json,true);
            file_put_contents($url, $json);
        }
    }
}
