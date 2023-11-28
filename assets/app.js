jQuery("#lokipays-transaction-status-check").on("click", function() {
  var btn = document.getElementById('lokipays-transaction-status-check'); // Replace with the actual ID of your button
  var btnHtml = btn.innerHTML;
  
  // Assuming you're triggering this script from an event, you can access the data-id attribute like this:
  var transactionId = btn.dataset.id;
  
  // URL for the AJAX request
  var url = "/wp-json/lokipays/v1/transaction/status?id=" + transactionId;
  
  // Set up the request
  var request = new Request(url, {
      method: 'GET',
      headers: new Headers({
          'Content-Type': 'application/json',
      }),
  });
  
  // Disable the button and update its text
  btn.disabled = true;
  btn.innerHTML = "Loading...";
  
  // Clear the transaction status
  document.getElementById('lokipays-transaction-status').innerHTML = "";
  
  // Make the AJAX request
  fetch(request)
      .then(response => {
          if (!response.ok) {
              throw new Error("Network response was not ok");
          }
          return response.json();
      })
      .then(data => {
          // Handle success
          document.getElementById('lokipays-transaction-status').innerHTML = JSON.stringify(data);
      })
      .catch(error => {
          // Handle error
          document.getElementById('lokipays-transaction-status').innerHTML = "Something went wrong.";
      })
      .finally(() => {
          // Re-enable the button and restore its original text
          btn.disabled = false;
          btn.innerHTML = btnHtml;
      });
});
