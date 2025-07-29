if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('/service-worker.js')
      .then(reg => console.log('SW registered', reg))
      .catch(err => console.error('SW registration failed:', err));
  });
}

document.addEventListener("DOMContentLoaded", () => {

  // PROFILE CODE (Run only if relevant elements exist)
  const profileIcon = document.querySelector('.profile');
  const profileSection = document.getElementById('profileSection');
  const profilePicDiv = document.getElementById('profilePic');
  const profileImage = document.getElementById('profileImage');
  const imageInput = document.getElementById('imageInput');

  if (profileIcon && profileSection) {
    profileIcon.addEventListener('click', () => {
      profileSection.classList.toggle('active');
    });
  }

  if (profilePicDiv && imageInput && profileImage) {
    profilePicDiv.addEventListener('click', () => {
      imageInput.click();
    });

    imageInput.addEventListener('change', (event) => {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          profileImage.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }
});
