// Script untuk aplikasi penyewaan
document.addEventListener("DOMContentLoaded", function () {
  // Autoclose untuk alert
  setTimeout(function () {
    let alerts = document.querySelectorAll(
      ".alert:not(.alert-warning, .alert-info)"
    );
    alerts.forEach(function (alert) {
      let closeBtn = document.createElement("button");
      closeBtn.type = "button";
      closeBtn.className = "close";
      closeBtn.setAttribute("data-dismiss", "alert");
      closeBtn.setAttribute("aria-label", "Close");
      closeBtn.innerHTML = '<span aria-hidden="true">&times;</span>';
      alert.appendChild(closeBtn);

      setTimeout(function () {
        $(alert).fadeOut("slow");
      }, 5000);
    });
  }, 500);

  // Format harga pada input
  const priceInputs = document.querySelectorAll('input[name="price_per_day"]');
  priceInputs.forEach(function (input) {
    input.addEventListener("input", function () {
      // Hapus semua karakter selain angka
      this.value = this.value.replace(/[^\d]/g, "");
    });
  });

  // Validasi tanggal pada form penyewaan
  const startDateInput = document.getElementById("start_date");
  const endDateInput = document.getElementById("end_date");

  if (startDateInput && endDateInput) {
    startDateInput.addEventListener("change", function () {
      endDateInput.min = this.value;
      if (
        endDateInput.value &&
        new Date(endDateInput.value) < new Date(this.value)
      ) {
        endDateInput.value = this.value;
      }
    });
  }

  // Preview foto sebelum upload
  const imageInputs = document.querySelectorAll('input[type="file"]');
  imageInputs.forEach(function (input) {
    input.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          const preview = document.createElement("img");
          preview.src = e.target.result;
          preview.className = "img-thumbnail mt-2";
          preview.style.maxWidth = "200px";
          preview.style.maxHeight = "200px";

          // Hapus preview sebelumnya jika ada
          const parent = input.parentElement;
          const oldPreview = parent.querySelector(".img-thumbnail");
          if (oldPreview) {
            parent.removeChild(oldPreview);
          }

          // Tambahkan preview baru
          parent.appendChild(preview);
        };
        reader.readAsDataURL(file);
      }
    });
  });

  // Toggle password visibility
  const passwordToggles = document.querySelectorAll(".toggle-password");
  passwordToggles.forEach(function (toggle) {
    toggle.addEventListener("click", function () {
      const target = document.getElementById(this.getAttribute("data-target"));
      if (target.type === "password") {
        target.type = "text";
        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
      } else {
        target.type = "password";
        this.innerHTML = '<i class="fas fa-eye"></i>';
      }
    });
  });
});
