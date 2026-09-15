<?php
include 'header.php';

// check if redirecting from a successful checkout order
$order_placed = isset($_GET['success']) && $_GET['success'] == 1;
?>

<main class="container page-main">

    <?php if ($order_placed == true) { ?>
        
        <div class="success-box">
            
            <div class="success-box-header">
                <h1 class="success-alert-title">[ SUCCESS ]</h1>
                <h2 class="success-main-heading">Order Taken Successfully!</h2>
                <p class="success-sub-heading">Thank you for shopping at CUSAT Store</p>
            </div>
            <hr class="receipt-divider">
            
            <div class="receipt-body-padding">
                <h3 class="receipt-section-title"><u>Order Details</u></h3>
                <br>
                
                <table class="receipt-data-table" width="100%">
                    <tr>
                        <td><b>Order ID:</b></td>
                        <td><span class="receipt-id-text">#CUSAT-<?php echo htmlspecialchars($_GET['order_id']); ?></span></td>
                    </tr>
                    <tr>
                        <td><b>Customer Name:</b></td>
                        <td><?php echo htmlspecialchars($_GET['name']); ?></td>
                    </tr>
                    <tr>
                        <td><b>Date & Time:</b></td>
                        <td><?php echo htmlspecialchars($_GET['date']); ?></td>
                    </tr>
                    <tr>
                        <td><b>Total Amount:</b></td>
                        <td><b class="receipt-total-price">₹<?php echo number_format($_GET['total'], 2); ?></b></td>
                    </tr>
                </table>
                <br><br>

                <div class="next-steps-instruction-box">
                    <b>Next Steps:</b>
                    <ul class="instruction-bullet-list">
                        <li>Your order has been recorded in our store system.</li>
                        <li><b>No online payment is required.</b> You can settle the payment offline via cash/UPI upon delivery or pickup.</li>
                        <li>You will be contacted on your phone for verification or delivery instructions.</li>
                    </ul>
                </div>
                <br><br>

                <div class="receipt-actions-alignment">
                    <a href="index.php" class="btn btn-primary spacing-r">Continue Shopping</a>
                    
                    <?php if ($is_logged_in) { ?>
                        <a href="orders.php" class="btn btn-secondary">View My Orders</a>
                    <?php } ?>
                </div>

            </div>
        </div>

    <?php } else { ?>

        <h2 class="cart-page-main-title">Shopping Cart</h2>
        <hr class="title-underline-hr">
        <br>
        
        <div class="cart-layout-row-split">
            
            <div class="cart-items-column-left">
                <h3 class="column-header-title">Selected Items</h3>
                
                <div id="cart-items-list" class="cart-list-render-target">
                     <p class="loading-placeholder-text">Loading items from cart...</p>
                </div>
            </div>

            <div class="cart-checkout-column-right">
                
                <div class="checkout-form-card-wrapper">
                    <h3 class="checkout-card-header-title">Order & Delivery Details</h3>
                    
                    <?php if ($is_logged_in == false) { ?>
                        <div class="auth-warning-alert-box">
                            <b>Authentication Required</b><br>
                            You must be logged in to place an order. Please sign in or register to proceed.
                            <br><br>
                            <a href="login.php" class="auth-warning-link-btn primary-warn">Login</a> &nbsp;
                            <a href="register.php" class="auth-warning-link-btn secondary-warn">Register</a>
                        </div>
                    <?php } ?>

                    <form id="checkout-form" class="<?php if($is_logged_in == false) { echo 'form-state-disabled'; } ?>">
                        
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        <input type="hidden" id="order-items-input" name="items" value="">

                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-input" 
                                   value="<?php if($is_logged_in) { echo htmlspecialchars($_SESSION['user']['name']); } ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" id="customer_email" name="customer_email" class="form-input" 
                                   value="<?php if($is_logged_in) { echo htmlspecialchars($_SESSION['user']['email']); } ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" id="customer_phone" name="customer_phone" class="form-input" placeholder="e.g. +91 9876543210" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">CUSAT Department</label>
                            <input type="text" id="department" name="department" class="form-input" placeholder="e.g. School of Engineering (SOE)" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Roll Number</label>
                            <input type="text" id="roll_number" name="roll_number" class="form-input" placeholder="e.g. 20120045" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Hostel Room / Department Delivery Address</label>
                            <textarea id="delivery_address" name="delivery_address" rows="3" class="form-input text-area-no-resize" placeholder="e.g. Room 402, Sahara Hostel" required></textarea>
                        </div>
                        <br>

                        <div class="checkout-pricing-summary-box">
                            <table width="100%">
                                <tr>
                                    <td class="summary-label-text">Cart Subtotal:</td>
                                    <td align="right" id="summary-subtotal" class="summary-value-text">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="summary-label-text">Delivery Fee:</td>
                                    <td align="right" class="summary-delivery-free-tag">FREE (On Campus)</td>
                                </tr>
                                <tr class="summary-total-amount-row">
                                    <td>Total Amount:</td>
                                    <td align="right" id="summary-total" class="summary-total-highlight-price">₹0.00</td>
                                </tr>
                            </table>
                        </div>
                        <br>

                        <input type="submit" id="checkout-btn" class="btn btn-teal full-width-input-btn" value="Place Order (Cash on Delivery)" 
                               <?php if($is_logged_in == false) { echo 'disabled'; } ?>>
                    </form>

                </div>
            </div>

        </div>

        <div class="clearfix-block"></div>

    <?php } ?>

</main>

<br><br>
<hr>
<footer class="global-page-footer">
    <center>
        <p class="footer-brand-text"><b>CUSAT Store Component</b></p>
        <p class="footer-copyright-text">&copy; 2026 Cochin University of Science and Technology. All rights reserved.</p>
    </center>
</footer>

<script src="app.js"></script>
</body>
</html>