<?php
include 'header.php';

// redirect away safely if visitor isn't an authorized store administrator
if ($is_admin == false) {
    header("Location: index.php");
    exit();
}

$orders = array();
$products = array();
$error_orders_msg = null;
$error_products_msg = null;
$admin_token = $_SESSION['admin_token'];

// fetch system customer logged order rows from core database via link api
$orders_url = "http://127.0.0.1:8000/api/orders";
try {
    $options = array(
        'http' => array(
            'header'  => "x-admin-token: " . $admin_token . "\r\n",
            'method'  => 'GET',
            'timeout' => 3.0,
            'ignore_errors' => true
        )
    );
    $context  = stream_context_create($options);
    $response = @file_get_contents($orders_url, false, $context);
    
    if (isset($http_response_header)) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        $status_code = intval($match[1]);
    } else {
        $status_code = 500;
    }

    if ($response === FALSE || $status_code != 200) {
        $error_orders_msg = "Could not fetch orders log. Please check if FastAPI backend is running.";
    } else {
        $orders = json_decode($response, true);
    }
} catch (Exception $e) {
    $error_orders_msg = "An error occurred while fetching orders.";
}

// pull current merchandise row items listings parameters catalog fields
$products_url = "http://127.0.0.1:8000/api/products";
try {
    $ctx = stream_context_create(['http' => ['timeout' => 3.0]]);
    $response = @file_get_contents($products_url, false, $ctx);
    if ($response === FALSE) {
        $error_products_msg = "Could not fetch catalog products.";
    } else {
        $products = json_decode($response, true);
    }
} catch (Exception $e) {
    $error_products_msg = "An error occurred while fetching products.";
}
?>

<main class="container page-main">

    <div class="admin-dashboard-title-row-alignment">
        <h2 class="admin-panel-main-heading">Merchant & Admin Control Panel</h2> 
        <span class="admin-verification-pill-badge">
            Authorized Admin Account
        </span>
    </div>
    <hr class="title-underline-hr">
    <br>

    <div class="admin-grid-layout-split">
        
        <div class="admin-management-column-left">
            
            <div class="admin-card-container-wrapper shadow-sm">
                <h3 class="admin-card-section-header-title">Add New Store Product</h3>
                <br>
                
                <form id="add-product-form" onsubmit="handleAddProduct(event, '<?php echo $admin_token; ?>')">
                    
                    <div class="form-group">
                        <label class="form-label">Product Title</label>
                        <input type="text" id="prod_name" name="name" class="form-input" placeholder="e.g. CUSAT Polo T-Shirt" required>
                    </div>

                    <div class="admin-form-row-flex-grid">
                        <div class="flex-field-half-width">
                            <label class="form-label">Price (INR)</label>
                            <input type="number" id="prod_price" name="price" step="0.01" class="form-input" placeholder="e.g. 399.00" required>
                        </div>
                        <div class="flex-field-half-width">
                            <label class="form-label">Category</label>
                            <select id="prod_cat" name="category" class="form-input dropdown-select-height" required>
                                <option value="Apparel">Apparel</option>
                                <option value="Textbooks">Textbooks</option>
                                <option value="Tech">Tech</option>
                                <option value="Stationery">Stationery</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Image URL</label>
                        <input type="url" id="prod_img" name="image_url" class="form-input" placeholder="e.g. http://example.com/image.jpg">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Description</label>
                        <textarea id="prod_desc" name="description" rows="4" class="form-input text-area-no-resize" placeholder="Detail materials, size guide, syllabus data..." required></textarea>
                    </div>

                    <br>
                    <input type="submit" class="btn btn-teal full-width-input-btn" value="Add Product to Catalog">
                </form>
            </div>

            <div class="admin-card-container-wrapper shadow-sm">
                <h3 class="admin-card-section-header-title">Store Catalog Management</h3>
                <br>
                
                <?php if ($error_products_msg != null) { ?>
                    <p class="admin-error-text-line"><?php echo $error_products_msg; ?></p>
                <?php } else { ?>
                    
                    <div class="admin-scrollable-log-windowbox">
                        <?php foreach ($products as $prod) { ?>
                            
                            <div class="admin-catalog-list-row-item-cell">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="55px" valign="middle">
                                            <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" class="admin-list-thumbnail-img" alt="thumb">
                                        </td>
                                        <td valign="middle">
                                            <div class="admin-list-product-bold-title"><?php echo htmlspecialchars($prod['name']); ?></div>
                                            <div class="admin-list-product-muted-subdata">₹<?php echo number_format($prod['price'], 2); ?> | <?php echo htmlspecialchars($prod['category']); ?></div>
                                        </td>
                                        <td width="70px" align="right" valign="middle">
                                            <button class="btn btn-danger admin-small-action-delete-btn" onclick="deleteProduct(<?php echo $prod['id']; ?>, '<?php echo $admin_token; ?>')">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                        <?php } ?>
                    </div>

                <?php } ?>
            </div>

        </div>


        <div class="admin-orders-column-right">
            
            <div class="admin-card-container-wrapper shadow-sm">
                <h3 class="admin-card-section-header-title">Incoming Customer Orders</h3>
                <br>
                
                <?php if ($error_orders_msg != null) { ?>
                    <div class="admin-alert-banner-error-box">
                        <b>Error Notice:</b> <?php echo htmlspecialchars($error_orders_msg); ?>
                    </div>
                <?php } else { ?>

                    <?php if (count($orders) == 0) { ?>
                        <div class="admin-empty-state-placeholder-box">
                            <p class="admin-empty-muted-text">No orders have been placed in the store yet.</p>
                        </div>
                    <?php } else { ?>
                        
                        <div class="admin-order-cards-stack-scroller">
                            <?php foreach ($orders as $order) { ?>
                                
                                <div class="admin-order-report-card-container">
                                    
                                    <table width="100%" class="admin-order-header-data-row-table" cellpadding="6" cellspacing="0">
                                        <tr>
                                            <td>
                                                <span class="admin-order-highlight-id-lbl">#CUSAT-<?php echo htmlspecialchars($order['id']); ?></span>
                                            </td>
                                            <td align="center">
                                                <span class="admin-order-timestamp-muted-txt"><?php echo htmlspecialchars($order['created_at']); ?></span>
                                            </td>
                                            <td align="right">
                                                <span class="status-badge <?php if($order['status'] == 'Pending') { echo 'status-pending'; } else { echo 'status-completed'; } ?>">
                                                    <?php echo htmlspecialchars($order['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    <br>

                                    <table width="100%" class="admin-order-address-grid-layout-table" border="0" cellspacing="0" cellpadding="4">
                                        <tr>
                                            <td width="50%" valign="top" class="admin-address-cell-border-right">
                                                <div class="admin-column-lowercase-small-lbl">CUSTOMER</div>
                                                <div class="admin-address-bold-title-text"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <div><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                                <div>Ph: <?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                            </td>
                                            <td width="50%" valign="top" style="padding-left:15px;">
                                                <div class="admin-column-lowercase-small-lbl">CAMPUS ADDRESS</div>
                                                <div><b>Dept:</b> <?php echo htmlspecialchars($order['department']); ?></div>
                                                <div><b>Roll:</b> <?php echo htmlspecialchars($order['roll_number']); ?></div>
                                                <div><b>Dest:</b> <?php echo htmlspecialchars($order['delivery_address']); ?></div>
                                            </td>
                                        </tr>
                                    </table>
                                    <br>

                                    <div class="admin-order-items-listing-subwindow">
                                        <div class="admin-column-lowercase-small-lbl">ORDERED ITEMS</div>
                                        <ul class="admin-order-nested-items-bullet-list">
                                            <?php foreach ($order['items'] as $item) { ?>
                                                <li>
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="2">
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['product_name']); ?> <span class="admin-item-qty-count-lbl">x<?php echo $item['quantity']; ?></span></td>
                                                            <td align="right"><b>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></b></td>
                                                        </tr>
                                                    </table>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <br>

                                    <div class="admin-order-total-amount-footer-panel">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td><span>Total Amount (COD):</span></td>
                                                <td align="right"><span class="admin-order-footer-total-price-tag">₹<?php echo number_format($order['total_amount'], 2); ?></span></td>
                                            </tr>
                                        </table>
                                    </div>

                                </div>

                            <?php } ?>
                        </div>

                    <?php } ?>
                <?php } ?>

            </div>
        </div>

    </div>

    <div class="clearfix-block"></div>

</main>

<br><br>
<hr>
<footer class="global-page-footer">
    <center>
        <p class="footer-brand-text"><b>CUSAT Store Admin Dashboard Panel</b></p>
        <p class="footer-copyright-text">&copy; 2026 Cochin University of Science and Technology. All rights reserved.</p>
    </center>
</footer>

<script src="app.js"></script>
</body>
</html>