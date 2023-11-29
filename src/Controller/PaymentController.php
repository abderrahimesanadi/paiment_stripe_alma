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

    public const ERROR_PAYMENT = "une erreur s'est produite lors du paiement";

    /**
     * @Route("/checkout", name="app_checkout")
     */
    public function checkout(): Response
    {
        return $this->render('payment/checkout.html.twig');
    }

    /**
     * @Route("/stripe-monitor", name="app_monitor")
     */
    public function showMonitorFormSTripe(): Response
    {
        return $this->render('payment/account-monitor.html.twig');
    }

    /**
     * @Route("/payment-stripe", name="app_payment_stripe")
     */
    public function paymentStripe(Request $request): Response
    {
        try {
            $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);
            // il faut essayer de verifier (retrieve)si le produit existe via son id sinon on le cree
            try {
                // todo à remplacer par un test sur la colum stripe_id
                $product = $stripe->products->retrieve(
                    'prod_P5cIaq337X7uBA',
                    []
                );
                $product_price = $product->default_price;
            } catch (\Throwable $th) {
                $product = $stripe->products->create([
                    'name' => '18 Heure d\'heure de conduite',
                    'default_price_data' => ['unit_amount_decimal' => '80000', 'currency' => 'EUR']
                ]);
                //add column stripe_id 
            }
            $product_price = $product->default_price;
            $checkout_session = $stripe->checkout->sessions->create([
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
     *@Route("/payment-almapay", name="app_payment_almapay")
     */
    public function paymentAlmapay(Request $request): Response
    {
        try {
            $jsoncontent = json_decode($request->getContent());

            $purchaseAmount = $jsoncontent->purchaseAmount;
            $alma = new \Alma\API\Client($_ENV['ALMA_SECRET_KEY'], ['mode' => \Alma\API\Client::TEST_MODE]);
            $payment = $alma->payments->createPayment(
                [
                    'origin'   => 'online_in_page',
                    'payment'  =>
                    [
                        'return_url'         => $_ENV['APP_DOMAIN'] . '/success',
                        'failure_return_url' => $_ENV['APP_DOMAIN'] . '/error',
                        'purchase_amount'    => $purchaseAmount,
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
                        'first_name' => 'Mike',
                        'last_name'  => 'bono',
                        'email'      => 'john-doe@yopmail.fr',
                        'phone'      => '06 12 34 56 78'
                    ],
                ]
            );

            $paiment_id = parse_url($payment->return_url, PHP_URL_QUERY);
            $paiment_id = explode('=', $paiment_id);
            // il faut prévoir d'enregistrer le paiment id dans la bd
            return new Response(json_encode($paiment_id[1]));
        } catch (\Alma\API\RequestError $error) {
            header("HTTP/1.1 500 Internal Server Error");
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

    /**
     * @Route("/create-account", name="app_account_stripe")
     */
    public function createStripeAccount(Request $request): Response
    {
        try {

            $jsoncontent = json_decode($request->getContent());

            $account_token = $jsoncontent->tokenAccount;
            $person_token = $jsoncontent->tokenPerson;

            $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);
            $account = $stripe->accounts->create([
                'country' => 'FR',
                'type' => 'custom',
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
                'account_token' => $account_token,
            ]);


            $stripe->accounts->createPerson(
                $account->id,
                [
                    'person_token' => $person_token,
                ]
            );
        } catch (\Throwable $th) {

            return new Response(json_encode($th->getMessage()));
        }
        return new Response(json_encode($account->id));
    }

    /**
     * @Route("/stripe-transfert", name="app_transfert_stripe")
     */
    public function transfertToStripeAccount(Request $request): Response
    {
        try {

            $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

            $transfert = $stripe->transfers->create([
                'amount' => 4000, // calculable
                'currency' => 'eur',
                'destination' => 'acct_1OHT8mGdLyXH3aC0', // sous compte stripe du moniteur on le recupére de la bd
                'transfer_group' => 'Enseignant',
            ]);
            // si le transerfe s'est bien passé ($transfert->id) il faire faire une mise à jour de la base de donné

        } catch (\Throwable $th) {

            return new Response(json_encode($th->getMessage()));
        }
        return new Response(json_encode("Transfert Réussie"));
    }
}
