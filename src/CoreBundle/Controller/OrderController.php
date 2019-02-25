<?php
/**
 * Created by PhpStorm.
 * User: Hicham
 * Date: 23/09/2018
 * Time: 20:43
 */

namespace CoreBundle\Controller;

use CoreBundle\Entity\Command;
use CoreBundle\Entity\CommandProduct;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller
{
    public function checkoutAction(Request $request)
    {
        if($request->getSession()->get('username') == null){
            $request->getSession()->getFlashBag()->add('message', 'You need to login first to checkout !');
        }elseif ($request->getSession()->get('blocked') == 1){
            $request->getSession()->getFlashBag()->add('message', 'You are blocked,please contact GoodBurger Support !');
        }else{

            return $this->redirectToRoute('checkout_information_route');

        }

        return $this->redirectToRoute('cart_route');
    }

    public function checkoutInformationAction(Request $request)
    {
        if($request->isMethod('get')){
            return $this->render('CoreBundle:Order:checkout.html.twig');
        }else{
            $deliveryCity = $request->request->get('city');
            $basket = $request->getSession()->get('basket', []);
            $companyCity = $basket[0]['company'];
            if(strtolower($companyCity) != strtolower($deliveryCity)){
                $request->getSession()->getFlashBag()->add('message', 'We do not deliver outside the choosed company city !');
                return $this->render('CoreBundle:Order:checkout.html.twig');
            }else{
                $userId = $request->getSession()->get('userId');

                $em = $this->getDoctrine()->getManager();
                $company = $em->getRepository('CoreBundle:Company')->findOneByCity($companyCity);

                $user = $em->getRepository('CoreBundle:User')->find($userId);
                $command = new Command();
                $command->setPrice($request->getSession()->get('basketPrice'));
                $command->setUser($user);
                $command->setCompany($company);

                $em->persist($command);



                foreach ($basket as $b){
                    $commandProduct = new CommandProduct();
                    $productId = $b['id'];
                    $product = $em->getRepository('CoreBundle:Product')->find($productId);

                    $commandProduct->setCommand($command);
                    $commandProduct->setProduct($product);
                    $commandProduct->setQuantity($b['quantity']);

                    $companyProduct = $em->getRepository('CoreBundle:CompanyProduct')->findOneBy(array('company'=>$company,'product' => $product));
                    $stockQuantity = $companyProduct->getStockQuantity() - $b['quantity'];
                    $companyProduct->setStockQuantity($stockQuantity);
                    $em->persist($commandProduct);
                    $em->persist($companyProduct);
                }
                $em->flush();

                $request->getSession()->remove('basket');
                $request->getSession()->remove('basketPrice');

                return $this->redirectToRoute('checkout_confirmation_route');

            }
        }
    }

    public function checkoutConfirmationAction()
    {
        return $this->render('CoreBundle:Order:orderConfirmation.html.twig');
    }
}