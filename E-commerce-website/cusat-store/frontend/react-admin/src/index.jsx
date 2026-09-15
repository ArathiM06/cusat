import React, { useState, useEffect } from "react";
import { Link, useSearchParams } from "react-router-dom";

function Home() {
  const [searchParams, setSearchParams] = useSearchParams();

  const selectedCategory =
    searchParams.get("category") || "All";

  const searchQuery =
    searchParams.get("search") || "";

  const [products, setProducts] = useState([]);
  const [backendOffline, setBackendOffline] =
    useState(false);

  const categories = [
    "All",
    "Apparel",
    "Textbooks",
    "Tech",
    "Stationery",
  ];

  useEffect(() => {
    fetchProducts();
  }, [selectedCategory, searchQuery]);

  const fetchProducts = async () => {
    try {
      let apiUrl =
        "http://localhost:8000/api/products";

      if (selectedCategory !== "All") {
        apiUrl +=
          "?category=" +
          encodeURIComponent(selectedCategory);
      }

      const response = await fetch(apiUrl);

      if (!response.ok) {
        throw new Error();
      }

      let data = await response.json();

      if (searchQuery.trim() !== "") {
        data = data.filter(
          (product) =>
            product.name
              .toLowerCase()
              .includes(searchQuery.toLowerCase()) ||
            product.description
              .toLowerCase()
              .includes(searchQuery.toLowerCase())
        );
      }

      setProducts(data);
      setBackendOffline(false);
    } catch (error) {
      setBackendOffline(true);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();

    const form = e.target;

    setSearchParams({
      category: selectedCategory,
      search: form.search.value,
    });
  };

  const addToCart = (product) => {
    let cart =
      JSON.parse(
        localStorage.getItem("cart")
      ) || [];

    const existing = cart.find(
      (item) => item.id === product.id
    );

    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({
        ...product,
        quantity: 1,
      });
    }

    localStorage.setItem(
      "cart",
      JSON.stringify(cart)
    );

    alert(
      `${product.name} added to cart`
    );
  };

  return (
    <main className="container page-main">

      {/* Hero Section */}

      <div className="hero">
        <span className="hero-tag">
          OFFICIAL STORE
        </span>

        <br />
        <br />

        <h1 className="hero-title">
          Wear Your Pride, Learn in Style
        </h1>

        <br />

        <p className="hero-subtitle">
          Get official Cochin University
          merchandise, textbooks,
          stationery, and lab essentials.
          Designed for CUSATians, by
          CUSATians.
        </p>

        <br />
        <br />

        <a
          href="#store-section"
          className="hero-btn"
        >
          Shop Collection ↓
        </a>
      </div>

      {/* Filter Bar */}

      <div
        id="store-section"
        className="store-filter-bar"
      >

        <div
          style={{
            display: "flex",
            justifyContent:
              "space-between",
            alignItems: "center",
          }}
        >

          <div>

            <b>Categories:</b>

            {categories.map((cat) => (
              <button
                key={cat}
                className={`category-link-btn ${
                  selectedCategory === cat
                    ? "active-cat"
                    : ""
                }`}
                onClick={() =>
                  setSearchParams({
                    category: cat,
                    search: searchQuery,
                  })
                }
              >
                {cat}
              </button>
            ))}

          </div>

          <form
            onSubmit={handleSearch}
          >
            <input
              type="text"
              name="search"
              defaultValue={
                searchQuery
              }
              placeholder="Search..."
              className="search-input-field"
            />

            <button
              className="search-submit-btn"
            >
              Search
            </button>
          </form>

        </div>

      </div>

      <br />
      <br />

      {/* Backend Error */}

      {backendOffline && (
        <div className="backend-error-box">

          <h2>
            🛑 FastAPI Backend is
            Offline 🛑
          </h2>

          <p>
            Please start the backend
            server on port 8000.
          </p>

          <textarea
            readOnly
            className="error-terminal-code"
            value="uvicorn main:app --reload --port 8000"
          />

        </div>
      )}

      {/* Products */}

      {!backendOffline && (
        <>
          {products.length === 0 ? (

            <div className="empty-products-view">

              <h3>
                🔍 No Products Found
              </h3>

              <p>
                We couldn't find any
                products matching your
                selection.
              </p>

              <Link to="/">
                Clear All Filters
              </Link>

            </div>

          ) : (

            <div className="products-container">

              <div
                style={{
                  display: "grid",
                  gridTemplateColumns:
                    "repeat(3,1fr)",
                  gap: "20px",
                }}
              >

                {products.map(
                  (product) => (
                    <div
                      key={product.id}
                      className="product-card-cell"
                    >

                      <div className="product-image-box">

                        <span className="product-card-category-badge">
                          {
                            product.category
                          }
                        </span>

                        <img
                          src={
                            product.image_url
                          }
                          alt={
                            product.name
                          }
                          className="catalog-product-img"
                        />

                      </div>

                      <h3 className="catalog-product-title">
                        {product.name}
                      </h3>

                      <p className="catalog-product-desc">
                        {
                          product.description
                        }
                      </p>

                      <hr />

                      <div
                        style={{
                          display:
                            "flex",
                          justifyContent:
                            "space-between",
                          alignItems:
                            "center",
                        }}
                      >

                        <b className="catalog-product-price">
                          ₹
                          {Number(
                            product.price
                          ).toFixed(
                            2
                          )}
                        </b>

                        <button
                          className="add-to-cart-action-btn"
                          onClick={() =>
                            addToCart(
                              product
                            )
                          }
                        >
                          Add to Cart 🛒
                        </button>

                      </div>

                    </div>
                  )
                )}

              </div>

            </div>

          )}
        </>
      )}

    </main>
  );
}

export default Home;