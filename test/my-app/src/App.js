import React, { useState } from 'react';
import ProductList from './ProductList';
import Cart from './Cart';
import Header from './Header';
import './App.css';

const initialProducts = [
  { id: 1, name: "Produk A", price: 50000, image: "https://via.placeholder.com/120x120?text=Produk+A" },
  { id: 2, name: "Produk B", price: 75000, image: "https://via.placeholder.com/120x120?text=Produk+B" },
  { id: 3, name: "Produk C", price: 120000, image: "https://via.placeholder.com/120x120?text=Produk+C" },
  { id: 4, name: "Produk D", price: 90000, image: "https://via.placeholder.com/120x120?text=Produk+D" },
  { id: 5, name: "Produk E", price: 150000, image: "https://via.placeholder.com/120x120?text=Produk+E" },
];

function App() {
  const [products] = useState(initialProducts);
  const [cart, setCart] = useState([]);
  const [search, setSearch] = useState('');

  const handleBuy = (product) => {
    setCart([...cart, product]);
  };

  const handleRemove = (id) => {
    setCart(cart.filter((item, idx) => idx !== id));
  };

  const handleSearch = (e) => {
    setSearch(e.target.value);
  };

  const filteredProducts = products.filter(p =>
    p.name.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div>
      <Header cartCount={cart.length} search={search} onSearch={handleSearch} />
      <div className="main-container">
        <ProductList products={filteredProducts} onBuy={handleBuy} />
        <Cart cart={cart} onRemove={handleRemove} />
      </div>
    </div>
  );
}

export default App;