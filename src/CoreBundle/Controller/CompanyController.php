<?php
/**
 * Created by PhpStorm.
 * User: Hicham
 * Date: 18/09/2018
 * Time: 15:58
 */

namespace CoreBundle\Controller;

use CoreBundle\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CompanyController extends Controller
{
    public function homeAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

            $companyId = $request->query->get('companyId');
            $company = $em->getRepository('CoreBundle:Company')->find($companyId);
            $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('company' => $company));

            $products = array();

        if ($request->query->get('filter') == null || $request->query->get('filter') == "0"){
            foreach ($companyProducts as $cp ){
                //$prod = $em->getRepository('CoreBundle:Product')->find($cp->getProduct()->getId());
                $prod = $em->getRepository('CoreBundle:Product')->findOneby(['id'=>$cp->getProduct()->getId()]);
                $products[] = $prod;
            }
        }else{
            $filter = $request->query->get('filter');
            foreach ($companyProducts as $cp ){
                //$prod = $em->getRepository('CoreBundle:Product')->find($cp->getProduct()->getId());
                $prod = $em->getRepository('CoreBundle:Product')->findOneby(['id'=>$cp->getProduct()->getId(),'category'=> $filter]);
                $products[] = $prod;
            }
        }



        return $this->render('CoreBundle:Company:company.html.twig',array('products' => $products,'company' => $company));


    }

    public function deleteCompanyAction(Request $request)
    {
        $id = $request->query->get('id');

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('CoreBundle:Company')->find($id);

        $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('company' => $company));
        foreach ($companyProducts as $cp){
            $em->remove($cp);
        }

        $em->remove($company);
        $em->flush();

        return $this->redirectToRoute('admin_companies_route');


    }

    public function addAction(Request $request)
    {
        $city= $request->request->get('city');
        $company = new Company($city);
        $em=$this->getDoctrine()->getManager();
        $em->persist($company);
        $em->flush();

        return $this->redirectToRoute('admin_companies_route');


    }

    public function updateAction(Request $request)
    {
        $companyId= $request->request->get('companyId');
        $companyName= $request->request->get('companyName');
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('CoreBundle:Company')->find($companyId);
        $company->setCity($companyName);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_company_route', array('id' => $companyId)));


    }

    public function deleteProductAction(Request $request)
    {

        $productId= $request->query->get('productId');
        $companyId = $request->query->get('companyId');

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('CoreBundle:Company')->find($companyId);
        $product = $em->getRepository('CoreBundle:Product')->find($productId);


        $companyProduct = $em->getRepository('CoreBundle:CompanyProduct')->findOneBy(array('company' => $company, 'product' => $product));

        $em->remove($companyProduct);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_company_route', array('id' => $companyId)));





    }

}