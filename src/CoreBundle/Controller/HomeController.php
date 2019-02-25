<?php
/**
 * Created by PhpStorm.
 * User: Hicham
 * Date: 17/09/2018
 * Time: 10:22
 */

namespace CoreBundle\Controller;

use CoreBundle\Entity\CompanyProduct;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

//use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function indexAction(Request $request)
    {
        $error = $request->query->get("error");



        $em = $this->getDoctrine()->getManager();
        $companies = $em->getRepository('CoreBundle:Company')->findAll();

        /*
        $product = $em->getRepository('CoreBundle:Product')->find(2);
        $company = $em->getRepository('CoreBundle:Company')->find(2);

        $companyProduct = new CompanyProduct();
        $em->persist($companyProduct);
        $companyProduct->setCompany($company);
        $companyProduct->setProduct($product);
        $companyProduct->setStockQuantity(12);

        $em->flush();
        */
        return $this->render('CoreBundle:Home:index.html.twig',array('companies' => $companies, 'error' => $error));
    }


}
