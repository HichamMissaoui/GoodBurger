<?php
/**
 * Created by PhpStorm.
 * User: Hicham
 * Date: 19/09/2018
 * Time: 22:29
 */

namespace CoreBundle\Controller;

use CoreBundle\Entity\CompanyProduct;
use CoreBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function addAction(Request $request)
    {
        $price = $request->request->get('price');
        if(!is_numeric($price)){
            return new Response("Please enter a valid price !");
        }

        $file = $request->files->get('productImage');
        $extension = array('jpeg','jpg','png');
        if(in_array($file->guessExtension (),$extension)){
            try{
                $fileName = 'images/products/' .md5(uniqid()). '.' . $file->guessExtension ();
                $file->move ( __dir__.'/../../../web/images/products', $fileName );
                $name = $request->request->get('name');
                $description = $request->request->get('description');
                $category = $request->request->get('category');
                $checkedId = $request->get('company_list');
                $companiesId = array();
                foreach($checkedId as $id) {
                    $companiesId[] = $id;
                }

                $product = new Product();
                $product->setName($name);
                $product->setDescription($description);
                $product->setPrice($price);
                $product->setCategory($category);
                $product->setPicture($fileName);

                $em = $this->getDoctrine()->getManager();
                $em->persist($product);

                foreach ($companiesId as $id){
                    $company = $em->getRepository('CoreBundle:Company')->find($id);
                    $companyProduct = new CompanyProduct();
                    $companyProduct->setCompany($company);
                    $companyProduct->setProduct($product);
                    $em->persist($companyProduct);
                    $em->flush();
                }

                return $this->redirectToRoute('admin_products_route');
            }catch(Exception $e){
                return new Response("An error occurred while uploading the file !");
            }


        }else{
            return new Response("Bad image extension !");

        }



    }

    public function detailAction(Request $request)
    {
        $productId= $request->query->get('productId');
        $companyId = $request->query->get('companyId');

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('CoreBundle:Company')->find($companyId);
        $product = $em->getRepository('CoreBundle:Product')->find($productId);


        $companyProduct = $em->getRepository('CoreBundle:CompanyProduct')->findOneBy(array('company' => $company, 'product' => $product));
        $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('company' => $company));

        $similarProducts = array();
        foreach ($companyProducts as $cp){
            if($cp->getProduct()->getCategory() == $product->getCategory() && $cp->getProduct()->getId() !=$product->getId()){
                $similarProducts[] = $cp->getProduct();
            }
        }


        return $this->render('CoreBundle:Product:product.html.twig',array('product' => $product,'company' => $company, 'stock' => $companyProduct->getStockQuantity(),'similarProducts' => array_slice($similarProducts, 0, 4)));


    }

}