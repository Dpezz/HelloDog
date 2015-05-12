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
 * @Route("/profile/shop")
 */
class ShopController extends Controller
{
    /**
     * @Route("/", name="shop_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {    
        return array();
    }

    /**
     * @Route("/buy", name="shop_buy")
     * @Method("GET")
     * @Template()
     */
    public function buyAction(Request $request)
    {    
        return array();
    }
}