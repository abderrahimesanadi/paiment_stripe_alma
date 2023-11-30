
var purchaseAmount = 100000;
var installmentsCount = 3;

const inPage = Alma.InPage.initialize({
    merchantId: "11xqxN9ZKoQTTrQWaJIfCz1Hr9Ew1kyvQd",//il faut prévoir de le mettre dans le fichier d'environnment d'angular
    amountInCents: purchaseAmount, // en cent
    installmentsCount: 3, // En 3/4 fois
    selector: "#alma-in-page",
    // Optionnels
    environment: "TEST",
    locale: 'FR',
    onIntegratedPayButtonClicked: () => {
        fetch("/payment-almapay", {
            method: "POST",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ purchaseAmount: purchaseAmount })
        }).then(response => {
            return response.json()
        }).then(function (MON_PAYMENT_ID) {
            inPage.startPayment({
                paymentId: MON_PAYMENT_ID
            });
        }).catch(function (error) {
            window.replace("http://localhost:8080/error");
        })
    }
});



/*onIntegratedPayButtonClicked: () => {
    // J'appelle l'API pour créer le paiement, si l'api retourne un code de succès :
    fetch("/payment-almapay", {
        method: "POST",
    }).then(MON_PAYMENT_ID => {
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
    })
}
});*/
