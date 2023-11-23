// Create a Checkout Session as soon as the page loads
async function initializeAlma() {
    const response = await fetch("/payment-almapay", {
        method: "POST",
    });

    const { MON_PAYMENT_ID } = await response.json();

    return MON_PAYMENT_ID;
}

const inPage = Alma.InPage.initialize({
    merchantId: "11xqxN9ZKoQTTrQWaJIfCz1Hr9Ew1kyvQd",//il faut prévoir de le mettre dans le fichier d'environnment d'angular
    amountInCents: 10000, // 100 euros
    installmentsCount: 3, // En 3 fois
    selector: "#alma-in-page",

    // Optionnels
    environment: "TEST",
    locale: 'FR',

    onIntegratedPayButtonClicked: () => {
        // J'appelle l'API pour créer le paiement, si l'api retourne un code de succès :
        MON_PAYMENT_ID = initializeAlma();

        inPage.startPayment({
            paymentId: MON_PAYMENT_ID, // J'utilise le payment ID généré au dessus.
            onPaymentSucceeded: () => {
                console.log("succeeded");
            },
            onPaymentRejected: () => {
                console.log("rejected");
            },
            onUserCloseModal: () => {
                console.log("user closed modal");
            }
        });
    }
});