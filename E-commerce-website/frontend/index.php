<?php
include 'header.php';

// get the category and search from url
$selected_category = 'All';
if (isset($_GET['category'])) {
    $selected_category = $_GET['category'];
}

$search_query = '';
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// backend endpoint link stuff
$api_url = "http://127.0.0.1:8000/api/products";
if ($selected_category != 'All') {
    $api_url = $api_url . "?category=" . urlencode($selected_category);
}

$products = array();
$backend_offline = false;

// fetching data from fastapi
try {
    $ctx = stream_context_create(array(
        'http' => array(
            'timeout' => 2.0
        )
    ));
    $response = @file_get_contents($api_url, false, $ctx);
    
    if ($response == false) {
        $backend_offline = true;
    } else {
        $products = json_decode($response, true);
        
        // filter array if user typed search query
        if ($search_query != '') {
            $filtered = array();
            foreach ($products as $p) {
                if (stristr($p['name'], $search_query) == true || stristr($p['description'], $search_query) == true) {
                    $filtered[] = $p;
                }
            }
            $products = $filtered;
        }
    }
} catch (Exception $e) {
    $backend_offline = true;
}

$categories = array('All', 'Apparel', 'Textbooks', 'Tech', 'Stationery');
?>

<main class="container page-main">
    
    <div class="hero">
        <div class="hero-bg-slide slide-1"></div>
        <div class="hero-bg-slide slide-2"></div>
        <div class="hero-container-inner">
            <div class="hero-text-side">
                <span class="hero-tag">OFFICIAL STORE</span>
                <h1 class="hero-title">Wear Your Pride, Learn in Style</h1>
                <p class="hero-subtitle">Get official Cochin University merchandise, textbooks, stationery, and lab essentials. Designed for CUSATians, by CUSATians.</p>
                <a href="#store-section" class="hero-btn">Shop Collection</a>
            </div>
            <div class="hero-image-side">
                <div class="admin-card">
                    <img src="assets/cusat_admin_cropped.png" alt="CUSAT Administrative Center" class="admin-card-img">
                </div>
            </div>
        </div>
    </div>

    <div id="store-section" class="store-filter-bar">
        <table width="100%" border="0">
            <tr>
                <td>
                    <b>Categories:</b> &nbsp;
                    <?php foreach ($categories as $cat) { ?>
                        <a href="index.php?category=<?php echo urlencode($cat); ?>&search=<?php echo urlencode($search_query); ?>#store-section" 
                           class="category-link-btn <?php if($selected_category == $cat) { echo 'active-cat'; } ?>">
                            <?php echo $cat; ?>
                        </a>
                    <?php } ?>
                </td>
                
                <td align="right">
                    <form method="GET" action="index.php#store-section" class="search-form-inline">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                        <b>Find Item:</b> 
                        <input type="text" name="search" placeholder="Type here to search..." value="<?php echo htmlspecialchars($search_query); ?>" class="search-input-field">
                        <input type="submit" value="Search" class="search-submit-btn">
                    </form>
                </td>
            </tr>
        </table>
    </div>
    <br><br>

    <?php if ($backend_offline == true) { ?>
        <div class="backend-error-box">
            <h2 class="error-heading">🛑 FastAPI Backend is Offline 🛑</h2>
            <p>CUSAT Store requires the FastAPI backend to load products and handle checkouts. Please make sure the backend server is running locally on port 8000.</p>
            <br>
            <b>Run this startup command in your terminal:</b><br><br>
            <textarea readonly class="error-terminal-code">uvicorn main:app --reload --port 8000</textarea>
        </div>
        <br><br>
    <?php } ?>

    <?php if ($backend_offline == false) { ?>
        
        <?php if (count($products) == 0) { ?>
            
            <div class="empty-products-view">
                <h3>🔍 No Products Found</h3>
                <p>We couldn't find any products matching your selection.</p>
                <br>
                <a href="index.php" class="clear-filters-link">[ Clear All Filters ]</a>
            </div>

        <?php } else { ?>
            
            <div class="products-container">
                
                <table width="100%" border="0" cellpadding="10" cellspacing="15">
                    <?php 
                    $counter = 0;
                    foreach ($products as $prod) { 
                        // rows split every 3 items
                        if ($counter % 3 == 0) {
                            if ($counter > 0) { echo "</tr>"; }
                            echo "<tr>";
                        }
                    ?>
                        
                        <td width="33%" valign="top" class="product-card-cell">
                            
                            <div class="product-image-box">
                                <span class="product-card-category-badge">
                                    <?php echo htmlspecialchars($prod['category']); ?>
                                </span>
                                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="img" class="catalog-product-img">
                            </div>
                            
                            <br>
                            <h3 class="catalog-product-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                            <p class="catalog-product-desc"><?php echo htmlspecialchars($prod['description']); ?></p>
                            
                            <hr class="product-card-divider">
                            
                            <table width="100%">
                                <tr>
                                    <td>
                                        <b class="catalog-product-price">₹<?php echo number_format($prod['price'], 2); ?></b>
                                    </td>
                                    <td align="right">
                                        <button onclick="addToCart(<?php echo $prod['id']; ?>, '<?php echo addslashes($prod['name']); ?>', <?php echo $prod['price']; ?>, '<?php echo addslashes($prod['image_url']); ?>')" 
                                                class="add-to-cart-action-btn">
                                            Add to Cart 🛒
                                        </button>
                                    </td>
                                </tr>
                            </table>

                        </td>

                    <?php 
                        $counter++;
                    } 
                    if ($counter > 0) { echo "</tr>"; }
                    ?>
                </table>

            </div>

        <?php } ?>
    <?php } ?>

</main>

<br><br>
<hr>

<footer class="global-page-footer">
    <center>
        <p class="footer-brand-text"><b>CUSAT Store Catalog View</b></p>
        <p class="footer-copyright-text">&copy; 2026 Cochin University of Science and Technology. All rights reserved.</p>
    </center>
</footer>

<script src="app.js"></script>
</body>
</html>