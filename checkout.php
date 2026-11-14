<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("form").submit(function (event) {
            event.preventDefault(); // Prevent default form submission

            var formData = $(this).serialize(); // Get form data

            $.ajax({
                url: "submit_order.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        alert("Order placed successfully! Order ID: " + response.order_id);
                        $("form")[0].reset(); // Reset the form after submission
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function () {
                    alert("Something went wrong. Please try again.");
                }
            });
        });
    });
</script>
