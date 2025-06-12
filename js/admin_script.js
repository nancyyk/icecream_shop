document.addEventListener('DOMContentLoaded', function() {
  // Mendapatkan data profil dari PHP session
  const fetchProfileData = async () => {
    try {
      // Dalam implementasi sebenarnya, ini bisa dilakukan dengan AJAX
      // Untuk contoh ini, kita akan menggunakan data yang sudah ada di halaman
      const profileCard = document.querySelector('.profile-card');
      if (profileCard) {
        return {
          name: profileCard.querySelector('.profile-name').textContent,
          image: profileCard.querySelector('img').src
        };
      }
      return {
        name: "Admin",
        image: "../uploaded_files/default_avatar.png"
      };
    } catch(error) {
      console.error("Error fetching profile data:", error);
      return {
        name: "Admin",
        image: "../uploaded_files/default_avatar.png"
      };
    }
  };

  // Membuat elemen icon profil di top-bar
  const createProfileIcon = async () => {
    const topBar = document.querySelector('.top-bar');
    const profileData = await fetchProfileData();
    
    // Buat container untuk icon profile di sebelah kanan
    const profileContainer = document.createElement('div');
    profileContainer.className = 'profile-icon-container';
    profileContainer.style.marginLeft = 'auto';
    profileContainer.style.position = 'relative';
    profileContainer.style.cursor = 'pointer';
    
    // Buat elemen icon profile
    const profileIcon = document.createElement('div');
    profileIcon.className = 'profile-icon';
    profileIcon.innerHTML = `<i class='bx bxs-user' style='color: #ff69b4; font-size: 24px;'></i>`;
    
    // Tambahkan event listener untuk toggle dropdown
    profileIcon.addEventListener('click', toggleProfileDropdown);
    
    // Tambahkan ke container
    profileContainer.appendChild(profileIcon);
    
    // Buat dropdown menu (awalnya tersembunyi)
    const dropdown = document.createElement('div');
    dropdown.className = 'profile-dropdown';
    dropdown.id = 'profileDropdown';
    dropdown.style.position = 'absolute';
    dropdown.style.right = '0';
    dropdown.style.top = '30px';
    dropdown.style.backgroundColor = 'white';
    dropdown.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
    dropdown.style.borderRadius = '15px';
    dropdown.style.padding = '15px';
    dropdown.style.width = '220px';
    dropdown.style.textAlign = 'center';
    dropdown.style.zIndex = '1000';
    dropdown.style.display = 'none';
    
    // Tambahkan style untuk tombol
    const buttonStyle = `
      <style>
        .profile-btn {
          display: inline-block;
          background-color: white;
          border: 2px solid #ff69b4;
          color: #ff69b4;
          padding: 8px 20px;
          border-radius: 25px;
          font-size: 14px;
          text-decoration: none;
          transition: all 0.3s ease;
          cursor: pointer;
          margin: 0 5px;
        }
        
        .profile-btn:hover, .profile-btn:active {
          background-color: #ff69b4;
          color: white;
        }
      </style>
    `;
    
    // Isi dropdown dengan data profil
    dropdown.innerHTML = `
      ${buttonStyle}
      <img src="${profileData.image}" alt="Profile" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 8px; border: 3px solid #ff69b4;">
      <p style="font-size: 16px; font-weight: bold; color: #444; margin-bottom: 8px;">${profileData.name}</p>
      <div style="display: flex; justify-content: space-around; margin-top: 10px;">
        <a href="profile.php" class="profile-btn">Profile</a>
        <a href="../components/admin_logout.php" class="profile-btn" onclick="return confirm('Logout from this website?');">Logout</a>
      </div>
    `;
    
    // Tambahkan dropdown ke container
    profileContainer.appendChild(dropdown);
    
    // Tambahkan container ke top-bar
    topBar.appendChild(profileContainer);
    
    // Tambahkan event listener untuk menutup dropdown ketika mengklik di luar
    document.addEventListener('click', function(event) {
      if (!profileContainer.contains(event.target)) {
        dropdown.style.display = 'none';
      }
    });
  };
  
  // Fungsi untuk toggle dropdown
  function toggleProfileDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown.style.display === 'none' || dropdown.style.display === '') {
      dropdown.style.display = 'block';
    } else {
      dropdown.style.display = 'none';
    }
  }
  
  // Jalankan fungsi pembuatan icon profil
  createProfileIcon();
});