require('dotenv').config();

// Import necessary Firebase modules using ES modules
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getAuth, signInWithEmailAndPassword } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js';
import { getFirestore } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js';

// Secure configuration loading
const loadConfig = () => {
  try {
    return {
      admin: {
        email: process.env.ADMIN_EMAIL || 'admin@jarialjabar.com',
        password: process.env.ADMIN_PASSWORD,
        hostingUrl: 'https://jarialjabar.web.app'
      },
      firebase: {
        apiKey: process.env.FIREBASE_API_KEY,
        authDomain: process.env.FIREBASE_AUTH_DOMAIN,
        projectId: process.env.FIREBASE_PROJECT_ID,
        storageBucket: process.env.FIREBASE_STORAGE_BUCKET,
        messagingSenderId: process.env.FIREBASE_MESSAGING_SENDER_ID,
        appId: process.env.FIREBASE_APP_ID
      }
    };
  } catch (error) {
    console.error('Error loading configuration:', error);
    throw new Error('Failed to load configuration');
  }
};

// Initialize Firebase
const initializeFirebase = (config) => {
  try {
    const app = initializeApp(config.firebase);
    const auth = getAuth(app);
    const db = getFirestore(app);
    return { app, auth, db };
  } catch (error) {
    console.error('Error initializing Firebase:', error);
    throw new Error('Failed to initialize Firebase');
  }
};

// Menangani proses login
const handleLogin = async (auth, adminConfig) => {
  const form = document.getElementById('loginForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email')?.value;
    const password = document.getElementById('password')?.value;

    if (!email || !password) {
      showError('Mohon masukkan email dan password');
      return;
    }

    try {
      const loginButton = form.querySelector('button[type="submit"]');
      if (loginButton) {
        loginButton.disabled = true;
        loginButton.textContent = 'Sedang masuk...';
      }

      const userCredential = await signInWithEmailAndPassword(
        auth,
        email,
        password
      );

      if (userCredential.user) {
        // Cek role admin
        const userDoc = await db.collection('users').doc(userCredential.user.uid).get();
      
        if (userDoc.exists && userDoc.data().role === 'admin') {
          window.location.href = '/admin/dashboard.html';
        } else {
          throw new Error('Akses ditolak: Bukan admin');
        }
      }
    } catch (error) {
      console.error('Error login:', error);
      showError(getErrorMessage(error));
    } finally {
      if (loginButton) {
        loginButton.disabled = false;
        loginButton.textContent = 'Masuk';
      }
    }
  });
};

// Pesan error dalam bahasa Indonesia
const getErrorMessage = (error) => {
  switch (error.code) {
    case 'auth/invalid-email':
      return 'Alamat email tidak valid';
    case 'auth/user-disabled':
      return 'Akun ini telah dinonaktifkan';
    case 'auth/user-not-found':
    case 'auth/wrong-password':
      return 'Email atau password salah';
    default:
      return 'Terjadi kesalahan saat login';
  }
};

// Menampilkan pesan error ke pengguna
const showError = (message) => {
  const errorDiv = document.getElementById('error-message');
  if (errorDiv) {
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
      errorDiv.style.display = 'none';
    }, 3000);
  }
};

// Inisialisasi panel admin
const initializeAdmin = async () => {
  try {
    const config = await loadConfig();
    const { auth } = initializeFirebase(config);
    await handleLogin(auth, config.admin);
  } catch (error) {
    console.error('Gagal menginisialisasi panel admin:', error);
    showError('Gagal memuat panel admin');
  }
};

// Start the application
document.addEventListener('DOMContentLoaded', initializeAdmin);

// Export for testing purposes
export { loadConfig, initializeFirebase, handleLogin, getErrorMessage };

// Logout handler
document.getElementById('logoutBtn')?.addEventListener('click', () => {
  auth.signOut()
    .then(() => {
      window.location.href = '/admin/login.html';
    })
    .catch((error) => {
      console.error('Logout error:', error);
    });
});

// Auth state observer
auth.onAuthStateChanged((user) => {
  const adminPages = ['/admin/dashboard.html', '/admin/users.html'];
  const currentPath = window.location.pathname;
  
  if (!user && adminPages.includes(currentPath)) {
    // Redirect ke login jika belum login
    window.location.href = '/admin/login.html';
  }
});
