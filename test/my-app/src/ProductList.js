import React from 'react';

export default function ProductList({ products, onBuy }) {
  return (
    <div className="product-list">
      <h2>Daftar Produk</h2>
      <div className="product-grid">
        {products.map((p, idx) => (
          <div className="product-card" key={p.id}>
            {/* Contoh badge diskon untuk produk tertentu */}
            {(idx % 2 === 0) && (
              <div className="discount-badge">Diskon 10%</div>
            )}
            <img src={p.image} alt={p.name} className="product-img" />
            <div className="product-info">
              <div className="product-name">{p.name}</div>
              <div className="product-price">Rp{p.price.toLocaleString()}</div>
              <button className="buy-btn" onClick={() => onBuy(p)}>
                Beli
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}