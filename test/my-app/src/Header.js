import React from 'react';

export default function Header({ cartCount, search, onSearch }) {
  return (
    <header className="header">
      <div className="brand">
        <span className="brand-logo">🛒</span>
        <span className="brand-name">Tokopedia Lite</span>
      </div>
      <input
        className="search"
        type="text"
        placeholder="Cari produk..."
        value={search}
        onChange={onSearch}
      />
      <div className="cart-badge">
        <span>Keranjang</span>
        <span className="badge">{cartCount}</span>
      </div>
    </header>
  );
}
