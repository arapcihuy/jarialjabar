import React from 'react';

export default function Cart({ cart, onRemove }) {
  const total = cart.reduce((sum, item) => sum + item.price, 0);

  const handleCheckout = () => {
    if (cart.length === 0) {
      alert('Keranjang masih kosong!');
      return;
    }
    alert('Checkout berhasil!\nTerima kasih sudah berbelanja di Tokopedia Lite.');
  };

  return (
    <div className="cart">
      <h2>Keranjang Belanja</h2>
      {cart.length === 0 ? (
        <p>Keranjang kosong.</p>
      ) : (
        <ul className="cart-list">
          {cart.map((item, idx) => (
            <li className="cart-item" key={idx}>
              <img src={item.image} alt={item.name} className="cart-img" />
              <div className="cart-info">
                <span>{item.name}</span>
                <span>Rp{item.price.toLocaleString()}</span>
              </div>
              <button className="remove-btn" onClick={() => onRemove(idx)}>
                Hapus
              </button>
            </li>
          ))}
        </ul>
      )}
      <hr />
      <strong>Total: Rp{total.toLocaleString()}</strong>
      <button className="checkout-btn" onClick={handleCheckout}>
        Checkout
      </button>
    </div>
  );
}
