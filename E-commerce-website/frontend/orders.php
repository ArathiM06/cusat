<?php
include 'header.php';

// STEP 1: VERIFY SECURITY LOGGED IN STATUS
if ($is_logged_in == false) {
    header("Location: login.php");
    exit();
}

$orders = array();
$error_msg = null;

// STEP 2: SET UP THE URL LINK TO GRAB USER ORDERS
$api_url = "http://127.0.0.1:8000/api/orders/user/" . urlencode($user_id);

try {
    $ctx = stream_context_create(array(
        'http' => array(
            'timeout' => 3.0
        )
    ));
    $response = @file_get_contents($api_url, false, $ctx);
    
    if ($response == false) {
        $error_msg = "Could not fetch your order history. Make sure the FastAPI backend is running.";
    } else {
        $orders = json_decode($response, true);
    }
} catch (Exception $e) {
    $error_msg = "An error occurred while loading order history.";
}
?>

<main class="container" style="padding: 10px; font-family: sans-serif;">
    
    <h2 style="font-size: 30px; margin-bottom: 10px;">My Orders</h2>
    <hr>
    <br>

    <?php if ($error_msg != null) { ?>
        <div style="background-color: #ffcccc; border-left: 5px solid red; color: maroon; padding: 12px; margin-bottom: 20px;">
            <b>Error Alert:</b> <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php } ?>

    <?php if ($error_msg == null) { ?>
        
        <?php if (count($orders) == 0) { ?>
            
            <div style="border: 2px dashed gray; padding: 40px; text-align: center; background: #fafafa;">
                <h3 style="color: gray;">No Orders Placed Yet</h3>
                <p>You haven't ordered anything from CUSAT Store yet. Explore our products and place your first order!</p>
                <br>
                <a href="index.php" style="background: teal; color: white; padding: 10px 20px; text-decoration: none; font-weight: bold;">Start Shopping Now</a>
            </div>

        <?php } else { ?>
            
            <div class="orders-list-holder">
                <?php foreach ($orders as $order) { ?>
                    
                    <div style="border: 2px solid #555555; padding: 15px; margin-bottom: 25px; background-color: #ffffff;">
                        
                        <table width="100%" bgcolor="#e6f2ff" cellpadding="8" style="border-bottom: 1px solid #333;">
                            <tr>
                                <td>
                                    <font size="2" color="gray"><b>ORDER NUMBER</b></font><br>
                                    <b style="font-size: 16px; color: blue;">#CUSAT-<?php echo htmlspecialchars($order['id']); ?></b>
                                </td>
                                <td align="center">
                                    <font size="2" color="gray"><b>DATE ORDERED</b></font><br>
                                    <span><b><?php echo htmlspecialchars($order['created_at']); ?></b></span>
                                </td>
                                <td align="right">
                                    <font size="2" color="gray"><b>STATUS</b></font><br>
                                    <span style="font-weight: bold; padding: 2px 5px; border: 1px solid black; background: white;
                                        <?php if($order['status'] == 'Pending') { echo 'color: orange;'; } else { echo 'color: green;'; } ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                        <br>

                        <div style="padding: 5px;">
                            <span style="font-size: 12px; color: #666;"><b>ITEMS ORDERED LIST:</b></span>
                            <br><br>
                            
                            <table width="100%" border="0" cellspacing="0" cellpadding="4" style="font-size: 14px;">
                                <?php foreach ($order['items'] as $item) { ?>
                                    <tr style="border-bottom: 1px dotted #ccc;">
                                        <td>
                                            <b><?php echo htmlspecialchars($item['product_name']); ?></b> 
                                            <font color="gray">x <?php echo $item['quantity']; ?></font>
                                        </td>
                                        <td align="right">
                                            <b>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></b>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                        
                        <br>
                        <hr style="border: none; border-top: 1px dashed #666;">
                        
                        <table width="100%">
                            <tr>
                                <td><b>Total Paid (COD):</b></td>
                                <td align="right">
                                    <span style="font-size: 20px; font-weight: bold; color: red;">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                </td>
                            </tr>
                        </table>

                    </div> <?php } ?>
            </div>

        <?php } ?>
    <?php } ?>

</main>
<br><br><br>
<footer>
    <div style="text-align: center; padding: 15px; background-color: #333; color: white; font-size: 12px;">
        <p><b>CUSAT Store User Dashboard Component</b></p>
        <p>&copy; 2026 Cochin University of Science and Technology. All rights reserved.</p>
    </div>
</footer>

<script src="app.js"></script>
</body>
</html>
//athira