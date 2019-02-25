<?php
/**
 * Created by PhpStorm.
 * User: Hicham
 * Date: 19/09/2018
 * Time: 12:55
 */

namespace CoreBundle\Controller;

use CoreBundle\Entity\CompanyProduct;
use CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    public function adminAction()
    {

        return $this->render('CoreBundle:Admin:admin.html.twig');


    }

    public function usersAction()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('CoreBundle:User');

        $usersList = $repository->findAll();


        return $this->render('CoreBundle:Admin:users.html.twig',array("users" => $usersList));


    }

    public function addAdminAction(Request $request)
    {
        $id = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:User')->find($id);
        $user->setRole("admin");
        $em->flush();

        return $this->redirectToRoute('admin_users_route');


    }

    public function removeAdminAction(Request $request)
    {
        $id = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:User')->find($id);
        $user->setRole("user");
        $em->flush();

        return $this->redirectToRoute('admin_users_route');


    }

    public function blockUserAction(Request $request)
    {
        $id = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:User')->find($id);
        $user->setBlocked(1);
        $em->flush();

        return $this->redirectToRoute('admin_users_route');


    }

    public function unBlockUserAction(Request $request)
    {
        $id = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:User')->find($id);
        $user->setBlocked(0);
        $em->flush();

        return $this->redirectToRoute('admin_users_route');


    }


    //Get all companies from db
    public function companiesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $companies = $em->getRepository('CoreBundle:Company')->findAll();


        return $this->render('CoreBundle:Admin:companies.html.twig',array('companies' => $companies));


    }

    //Get company products
    public function companyAction(Request $request)
    {
        $companyId = $request->query->get('id');

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('CoreBundle:Company')->find($companyId);

        $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('company' => $company));

        $products = array();

        foreach ($companyProducts as $cp ){
            $prod = $em->getRepository('CoreBundle:Product')->find($cp->getProduct()->getId());
            $products[] = $prod;
        }

        return $this->render('CoreBundle:Admin:company.html.twig',array('products' => $products,'company' => $company));


    }
    //GET all products and companies from DB
    public function productsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $companies = $em->getRepository('CoreBundle:Company')->findAll();

        $pageTitle = "All Products";

        if ($request->isMethod('post') && $request->request->get('companyId') != 0) {
            $companyId = $request->request->get('companyId');
            $company = $em->getRepository('CoreBundle:Company')->find($companyId);
            $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('company' => $company));
            $products = array();

            foreach ($companyProducts as $cp){
                $p = $em->getRepository('CoreBundle:Product')->find($cp->getProduct()->getId());
                $products[] = $p;
            }
            $pageTitle .= " - ".ucfirst($company->getCity());
        }else{
            $products = $em->getRepository('CoreBundle:Product')->findAll();
        }



        return $this->render('CoreBundle:Admin:products.html.twig',array('products' => $products,'companies' => $companies, 'title' => $pageTitle));


    }

    public function getProductDetailAction(Request $request)
    {
        $productId= $request->query->get('productId');
        $companyId = $request->query->get('companyId');

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('CoreBundle:Company')->find($companyId);
        $product = $em->getRepository('CoreBundle:Product')->find($productId);


        $companyProduct = $em->getRepository('CoreBundle:CompanyProduct')->findOneBy(array('company' => $company, 'product' => $product));


        return $this->render('CoreBundle:Admin:product.html.twig',array('product' => $product,'company' => $company, 'stock' => $companyProduct->getStockQuantity()));


    }

    public function changeProductStockAction(Request $request)
    {
        $productId= $request->request->get('productId');
        $companyId = $request->request->get('companyId');
        $stock = $request->request->get('stock_quantity');

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('CoreBundle:Company')->find($companyId);
        $product = $em->getRepository('CoreBundle:Product')->find($productId);

        $companyProduct = $em->getRepository('CoreBundle:CompanyProduct')->findOneBy(array('company' => $company, 'product' => $product));
        $companyProduct->setStockQuantity($stock);
        $em->flush();


        return $this->render('CoreBundle:Admin:product.html.twig',array('product' => $product,'company' => $company, 'stock' => $companyProduct->getStockQuantity()));


    }

    public function manageProductAction(Request $request)
    {
        $productId= $request->query->get('productId');

        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('CoreBundle:Product')->find($productId);

        $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('product' => $product));
        $companies = $em->getRepository('CoreBundle:Company')->findAll();



        return $this->render('CoreBundle:Admin:product_manage.html.twig',array('product' => $product,'companyProducts' => $companyProducts,'companies' => $companies));


    }

    public function updateProductAction(Request $request)
    {
        $productId= $request->request->get('productId');
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('CoreBundle:Product')->find($productId);
        if($request->files->get('productImage') != null){

            $file = $request->files->get('productImage');
            $extension = array('jpeg','jpg','png');
            if(in_array($file->guessExtension (),$extension)){
                try {
                    $fileName = 'images/products/' . md5(uniqid()) . '.' . $file->guessExtension();
                    $file->move(__dir__ . '/../../../web/images/products', $fileName);
                    unlink( __dir__.'/../../../web/' . $product->getPicture());
                    $product->setPicture($fileName);

                }catch (Exception $e){
                    return new Response("An error occurred while uploading the file !");
                }
            }else{
                return new Response("Bad image extension !");
            }

        }else{

            $name = $request->request->get('name');
            $price = $request->request->get('price');
            $description = $request->request->get('description');
            $category = $request->request->get('category');

            $em = $this->getDoctrine()->getManager();
            $product = $em->getRepository('CoreBundle:Product')->find($productId);

            $product->setName($name);
            $product->setPrice($price);
            $product->setDescription($description);
            $product->setCategory($category);
        }



        $em->persist($product);
        $em->flush();

        $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('product' => $product));

        $companies = $em->getRepository('CoreBundle:Company')->findAll();

        return $this->render('CoreBundle:Admin:product_manage.html.twig',array('product' => $product,'companyProducts' => $companyProducts,'companies' => $companies));

    }

    public function updateProductCompaniesAction(Request $request)
    {

        $checkedId = $request->get('company_list');



        $productId= $request->request->get('productId');

                $em = $this->getDoctrine()->getManager();
                $product = $em->getRepository('CoreBundle:Product')->find($productId);

                $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('product' => $product));
                foreach ($companyProducts as $cp){
                    $em->remove($cp);
                }
                $em->flush();
                $companies = $em->getRepository('CoreBundle:Company')->findAll();
        $newCompanyProducts = array();
        if($checkedId != null){
            $companiesId = array();
            foreach($checkedId as $id) {
                //Do something with the ID
                $companiesId[] = $id;
            }
            foreach($checkedId as $id){
                $company = $em->getRepository('CoreBundle:Company')->find($id);
                $cp = new CompanyProduct();
                $cp->setProduct($product);
                $cp->setCompany($company);
                $em->persist($cp);
                $em->flush();
                $newCompanyProducts[] = $cp;
            }
        }



        return $this->render('CoreBundle:Admin:product_manage.html.twig',array('product' => $product,'companyProducts' => $newCompanyProducts,'companies' => $companies));

    }


    public function deleteProductAction(Request $request)
    {
        $productId= $request->query->get('productId');

        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('CoreBundle:Product')->find($productId);
        unlink( __dir__.'/../../../web/' . $product->getPicture());

        $companyProducts = $em->getRepository('CoreBundle:CompanyProduct')->findBy(array('product' => $product));
        foreach ($companyProducts as $cp){
            $em->remove($cp);
        }
        $commandProducts = $em->getRepository('CoreBundle:CommandProduct')->findBy(array('product' => $product));
        foreach ($commandProducts as $cmdp){
            $em->remove($cmdp);
        }
        $em->remove($product);
        $em->flush();


        return $this->redirectToRoute('admin_products_route');


    }


    public function ordersAction()
    {

        $em = $this->getDoctrine()->getManager();
        $orders = $em->getRepository('CoreBundle:Command')->findAll();


        return $this->render('CoreBundle:Admin:orders.html.twig',array('orders' => $orders));


    }

}