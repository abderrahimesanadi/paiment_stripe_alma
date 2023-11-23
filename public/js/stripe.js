// This is your test publishable API key. il faut prévoir de le mettre dans le fichier d'envirennment d'angular
const stripe = Stripe("pk_test_51OEuBIGmGM3fW1jTIMn0ueDtcV5PYNdRjuG6RUlYxWC5MQ1ZiZEAjOb6eHMbYLan03NbvNA1CWbmRrbTrjNHzTXq00I1TgtotJ");

initialize();

// Create a Checkout Session as soon as the page loads
async function initialize() {
  const response = await fetch("/payment-stripe", {
    method: "POST",
  });

  const { clientSecret } = await response.json();

  const checkout = await stripe.initEmbeddedCheckout({
    clientSecret,
  });

  // Mount Checkout
  checkout.mount('#checkout');
}