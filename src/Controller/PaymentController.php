<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Payment;

class PaymentController extends AbstractController
{
    private $passerelle;

    public const ERROR_PAYMENT = "une erreur s'est produite lors du paiement";

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->passerelle = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);;

        $this->manager = $manager;
    }

    /**
     * @Route("/checkout", name="app_checkout")
     */
    public function checkout(): Response
    {
        return $this->render('payment/checkout.html.twig');
    }

    /**
     * @Route("/payment-stripe", name="app_payment_stripe")
     */
    public function paymentStripe(Request $request): Response
    {
        try {
            // il faut essayer de verifier (retrieve)si le produit existe via son id sinon on le cree
            $product = $this->passerelle->products->create([
                'name' => '15 Heure d\'heure de conduite',
                'default_price_data' => ['unit_amount_decimal' => '70000', 'currency' => 'EUR']
            ]);
            $product_id = $product['id'];
            $product_price = $product['default_price'];
            $checkout_session = $this->passerelle->checkout->sessions->create([
                'ui_mode' => 'embedded',
                'line_items' => [[
                    'price' => $product_price,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'return_url' =>  $_ENV['APP_DOMAIN'] . '/success?session_id={CHECKOUT_SESSION_ID}',
            ]);
            // enregistrer les information du paiment dans la base donnée

            if ($checkout_session->client_secret) {
                return new Response(json_encode(array('clientSecret' => $checkout_session->client_secret)));
            } else {
                return new Response(json_encode(array('error_payment' => SELF::ERROR_PAYMENT)));
            }
        } catch (\Throwable $th) {

            return new Response(json_encode($th->getMessage()));
        }
    }

    /**
     * @Route("/payment-almapay", name="app_payment_almapay")
     */
    public function paymentAlmapay(Request $request): Response
    {
        $alma = new \Alma\API\Client($_ENV['ALMA_SECRET_KEY'], ['mode' => \Alma\API\Client::TEST_MODE]);
        try {
            $payment = $alma->payments->createPayment(
                [
                    'origin'   => 'online',
                    'payment'  =>
                    [
                        'return_url'         => $_ENV['APP_DOMAIN'] . '/success',
                        'failure_return_url' => $_ENV['APP_DOMAIN'] . '/error',
                        'purchase_amount'    => 10000,
                        'installments_count' => 3,
                        'locale'             => 'fr',
                        'billing_address'    =>
                        [
                            'first_name'  => 'John',
                            'last_name'   => 'Doe',
                            'email'       => 'john-doe@yopmail.fr',
                            'line1'       => '1 rue de Rome',
                            'postal_code' => '75001',
                            'city'        => 'Paris',
                            'country'     => 'FR',
                        ],
                    ],
                    'customer' =>
                    [
                        'first_name' => 'John',
                        'last_name'  => 'Doe',
                        'email'      => 'john-doe@yopmail.fr',
                        'phone'      => '06 12 34 56 78'
                    ],
                ]
            );

            $paiment_id = parse_url($payment->return_url, PHP_URL_QUERY);
            $paiment_id = explode('=', $paiment_id);
            return new Response(json_encode($paiment_id[1]));
        } catch (\Alma\API\RequestError $error) {
            header("HTTP/1.1 500 Internal Server Error");
            //die($error->getMessage());
            return new Response(json_encode($error->getMessage()));
        }

        /*foreach ($eligibilities as $eligibility) {
            if (!$eligibility->isEligible()) {
                die('cart is not eligible');
            }
        }*/
    }

    /**
     * @Route("/success", name="app_payment_success")
     */
    //Page de succès de la transaction
    public function success(Request $request): Response
    {
        return $this->render(
            'payment/success.html.twig'
        );

        /* } else {
        return $this->render(
            'payment/success.html.twig',
            [
                'message' => 'l\'utilisateur a annulé son paiement'
            ]
        );
        // }*/
    }



    //Page d'error de la transaction
    /**
     * @Route("/error", name="app_payment_error")
     */
    public function error(): Response
    {
        return $this->render(
            'payment/error.html.twig',
            [
                'message' => 'le paiement a échoué'
            ]
        );
    }
}
