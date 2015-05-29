<?php

namespace HelloDogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/profile/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="demo_admin")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {    
        return array();
    }

    /**
     * @Route("/keys", name="demo_key")
     * @Method("GET")
     * @Template()
     */
    public function keyAction(Request $request)
    {    
    	//Asignar el FLAG
        if(!$request->getSession()->get('flag'))
        {$request->getSession()->set('flag',-1);}

        $flag = $request->getSession()->get('flag');
        $request->getSession()->set('flag',-1);

        return array('flag'=>$flag,'keys'=>$this->getJsonKey());
    }

    /**
     * @Route("/bootic", name="demo_bootic")
     * @Method("GET")
     * @Template()
     */
    public function booticAction(Request $request)
    {    
        return array();
    }

     /**
     * @Route("/delete_keys")
     * @Method("POST")
     */
    public function deleteKey(Request $request){
        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        try{
            $position = $request->get('position');
            $this->deleteJsonKey($position);
            $request->getSession()->set('flag',1);
            return new Response(1);
        }catch(Exception $e){
            $request->getSession()->set('flag',-2);
            return new Response(0);
        }
    }
    /**
     * @Route("/generate_keys", name="generate_key")
     * @Method("POST")
     */
    public function generteKey(Request $request)
    {   
    	$cant = $request->get('cantidad');
  		
  		if(intval($cant) > 0){
	  		if($this->jsonKey(intval($cant))){
	  			$request->getSession()->set('flag',1);
	  		}else{
	  			$request->getSession()->set('flag',-2);
	  		}
	  	}
    	return $this->redirect($this->generateUrl('demo_key'));
    }

    private function jsonKey($cant){
    	try{
	    	$caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-+*';
	    	$keys = array();
	    	for ($i=0; $i < $cant; $i++) { 
		        $key = '';
		        for($j=0; $j<8; $j++){
		            $key .= $caracteres[rand(0, strlen($caracteres)-1)];
		        }
		        $keys[] = array('clave'=>$key,'active'=>0);
		    }

		    #create folder and file json
	    	if(!file_exists("keys")){
	            mkdir('keys',0755);
	        }
	        $url = "keys/keys.json";
	        if(!file_exists($url)){
	            $json = json_encode(
	                array('key'=>array(array(
	                    'fecha'=> date('d-m-Y'),
	                    'keys'=>$keys,
	                    ))));
	            $fh = fopen("keys/keys.json", 'w');
	            fwrite($fh, $json);
	            fclose($fh);
	        }else{
	            $file = file_get_contents($url);
	            $json = json_decode($file,true);

	            
        	    $json['key'][count($json['key'])] = array(
                	'fecha'=> date('d-m-Y'),
                	'keys'=>$keys,
                	);

	            $json = json_encode($json,true);
	            file_put_contents($url, $json);
	        }
	        return true;
	    }catch(exception $e){
	    	return false;
	    }
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
	    	//....
	    }
	    return null;
    }

    private function deleteJsonKey($id){
        $url = "keys/keys.json";
        if(file_exists($url)){
            $file = file_get_contents($url);
            $json = json_decode($file,true);

            unset($json['key'][$id]); 

            $json = json_encode($json,true);
            file_put_contents($url, $json);
        }
    }
}