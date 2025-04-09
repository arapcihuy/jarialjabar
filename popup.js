// Popup form handling

document.addEventListener('DOMContentLoaded', function() {
  // Get popup elements
  const openPopupBtn = document.getElementById('openPopup');
  const closePopupBtn = document.getElementById('closePopup');
  const popupForm = document.getElementById('popupForm');
  const popupProgramSelect = document.getElementById('popupProgramSelect');
  
  // Open popup when clicking "Try Class" button
  if (openPopupBtn && popupForm) {
    openPopupBtn.addEventListener('click', function() {
      console.log("Opening popup form"); // Debug log
      popupForm.style.display = 'flex';
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    });
  }
  
  // Close popup when clicking X button
  if (closePopupBtn && popupForm) {
    closePopupBtn.addEventListener('click', function() {
      console.log("Closing popup form"); // Debug log
      popupForm.style.display = 'none';
      document.body.style.overflow = 'auto'; // Restore background scrolling
    });
  }
  
  // Close popup when clicking outside
  window.addEventListener('click', function(event) {
    if (event.target === popupForm) {
      console.log("Closing popup form by clicking outside"); // Debug log
      popupForm.style.display = 'none';
      document.body.style.overflow = 'auto'; // Restore background scrolling
    }
  });
  
  // Initialize the popup program select
  if (popupProgramSelect) {
    popupProgramSelect.addEventListener('change', function() {
      console.log("Program selected:", this.value); // Debug log
      updatePopupJadwal();
    });
  }
  
  // Handle popup form submission
  const popupRegistrationForm = document.getElementById('popupRegistrationForm');
  if (popupRegistrationForm) {
    popupRegistrationForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      // Debug log form data
      console.log("Form submitted with data:");
      for (const [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
      }
      
      // Show success message
      alert('Terima kasih! Kami akan menghubungi Anda untuk jadwal kelas gratis Anda.');
      
      // Close popup
      popupForm.style.display = 'none';
      document.body.style.overflow = 'auto'; // Restore background scrolling
      
      // Reset form
      this.reset();
    });
  }
});

// Function to update popup jadwal options based on selected program
function updatePopupJadwal() {
  console.log("updatePopupJadwal called");
  const programSelect = document.getElementById('popupProgramSelect');
  const jadwalSelect = document.getElementById('popupJadwalSelect');
  
  if (!programSelect || !jadwalSelect) {
    console.log("Program select or jadwal select not found");
    return;
  }
  
  console.log("Selected program:", programSelect.value);
  
  // Clear existing options
  jadwalSelect.innerHTML = '<option value="">Pilih Jadwal Les</option>';
  
  if (programSelect.value === 'baca-tulis') {
    // Add schedules for Baca Tulis
    addPopupOption(jadwalSelect, 'senin-rabu', 'Senin & Rabu (15:00 - 16:30)');
    addPopupOption(jadwalSelect, 'selasa-kamis', 'Selasa & Kamis (15:00 - 16:30)');
    addPopupOption(jadwalSelect, 'jumat-sabtu', 'Jumat & Sabtu (10:00 - 11:30)');
    console.log("Added Baca Tulis schedules");
  } else if (programSelect.value === 'jari-aljabar') {
    // Add schedules for Jari Aljabar
    addPopupOption(jadwalSelect, 'senin-rabu', 'Senin & Rabu (16:30 - 18:00)');
    addPopupOption(jadwalSelect, 'selasa-kamis', 'Selasa & Kamis (16:30 - 18:00)');
    addPopupOption(jadwalSelect, 'jumat-sabtu', 'Jumat & Sabtu (13:00 - 14:30)');
    console.log("Added Jari Aljabar schedules");
  }
}

// Helper function to add options to select element
function addPopupOption(selectElement, value, text) {
  const option = document.createElement('option');
  option.value = value;
  option.textContent = text;
  selectElement.appendChild(option);
  console.log("Added option:", text);
}