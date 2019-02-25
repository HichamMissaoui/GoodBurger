<?php
/**
 * Created by PhpStorm.
 * User: Hicham
 * Date: 23/09/2018
 * Time: 11:49
 */

namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    public function cartAction(Request $request)
    {
        //$session = $request->getSession();
        //$session->invalidate(); //here we can now clear the session.
        return $this->render('CoreBundle:Cart:cart.html.twig');

    }

    public function addAction(Request $request)
    {
        $productId = $request->request->get('productId');
        $companyId = $request->request->get('companyId');
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('CoreBundle:Product')->find($productId);
        $company = $em->getRepository('CoreBundle:Company')->find($companyId);
        $companyProduct = $em->getRepository('CoreBundle:CompanyProduct')->findOneBy(array('company' => $company,'product' =>$product));
        $quantity = (int)$request->request->get('quantity');

        if($companyProduct->getStockQuantity() < $quantity){
            $request->getSession()->getFlashBag()->add('message', 'There is only '.$companyProduct->getStockQuantity().' products in the company stock !');
        }else{
            $companyError = 0;
            $basket = $request->getSession()->get('basket', []);
            $basketPrice = $request->getSession()->get('basketPrice');
            if(count($basket) > 0){
                if($basket[0]['company'] != $company->getCity()){
                    $companyError = 1;
                }
            }

            if($companyError == 0){
                $basketPrice += $product->getPrice() * $quantity;
                $existInBasket = 0;
                foreach ($basket as $key =>$b){
                    if($b['id'] == $productId){
                        $basket[$key]['quantity'] += $quantity;
                        $existInBasket = 1;
                    }
                }
                if($existInBasket == 0){
                    array_push($basket, [
                        'id'     => $product->getId(),
                        'name' => $product->getName(),
                        'price'  => $product->getPrice(),
                        'quantity'  => $quantity,
                        'company'   => $company->getCity()
                    ]);
                }

                $request->getSession()->set('basket', $basket);
                $request->getSession()->set('basketPrice', $basketPrice);

                $request->getSession()->getFlashBag()->add('message', 'Product added to cart !');

            }else{
                $request->getSession()->getFlashBag()->add('message', 'You cannot purchase from more than one city at the time !');
            }
        }



        return $this->redirectToRoute('product_detail_route',array("productId" => $productId,"companyId" => $companyId));

        //return new JsonResponse($basket);
    }

    public function deleteProductAction(Request $request)
    {
        $productId = $request->query->get("productId");
        $basket = $request->getSession()->get('basket', []);
        $basketPrice = $request->getSession()->get('basketPrice');
        foreach ($basket as $key =>$b){
            if($b['id'] == $productId){
                unset($basket[$key]);
                $basketPrice -= $b['price'] * $b['quantity'];
            }
        }
         $request->getSession()->set('basket', $basket);
        $request->getSession()->set('basketPrice', $basketPrice);

        return $this->render('CoreBundle:Cart:cart.html.twig');

    }

    public function changeProductQuantityAction(Request $request)
    {
        $productId = $request->request->get("productId");
        $companyCity = $request->request->get("companyCity");
        $newQuantity = $request->request->get("newQuantity");
        $cartQuantity = $request->request->get("cartQuantity");


        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('CoreBundle:Product')->find($productId);
        $company = $em->getRepository('CoreBundle:Company')->findOneByCity($companyCity);
        $companyProduct = $em->getRepository('CoreBundle:CompanyProduct')->findOneBy(array('company' => $company,'product' =>$product));

        if($companyProduct->getStockQuantity() < $newQuantity){
            $request->getSession()->getFlashBag()->add('message', 'There is only '.$companyProduct->getStockQuantity().' products in the company stock !');
        }else{
            $basket = $request->getSession()->get('basket', []);
            $basketPrice = $request->getSession()->get('basketPrice');
            foreach ($basket as $key =>$b){
                if($b['id'] == $productId){
                    unset($basket[$key]);
                    $basketPrice -= $b['price'] * $b['quantity'];

                    $basket[$key] = [
                        'id'     => $product->getId(),
                        'name' => $product->getName(),
                        'price'  => $product->getPrice(),
                        'quantity'  => $newQuantity,
                        'company'   => $company->getCity()
                    ];
                    $basketPrice += $product->getPrice() * $newQuantity;
                }
            }
            $request->getSession()->set('basket', $basket);
            $request->getSession()->set('basketPrice', $basketPrice);
        }



        return $this->render('CoreBundle:Cart:cart.html.twig');

    }

}