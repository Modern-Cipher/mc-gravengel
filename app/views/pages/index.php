<?php require APPROOT . '/views/includes/header.php'; ?>

<header class="top-nav">
  <div class="nav-container"> 
    <div class="logo-container">
      <img src="<?php echo URLROOT; ?>/img/gravengel.png" alt="Gravengel Logo" class="logo">
      <span class="site-title-nav">PLARIDEL PUBLIC CEMETERY</span>
    </div>

    <nav class="nav-links">
      <a href="#">Home</a>
      <a href="#about">About</a>
      <a href="#features">Features</a>
      <a href="#contact">Contact</a>
      <a href="<?php echo URLROOT; ?>/auth/login" class="login-button">
        <ion-icon name="log-in-outline"></ion-icon> Login
      </a>
    </nav>

    <div class="menu-toggle" id="menu-toggle">
      <ion-icon name="menu-outline"></ion-icon>
    </div>
  </div>
</header>

<div class="sidebar" id="sidebar">
  <div class="close-sidebar-container">
    <a href="#" class="close-sidebar" id="close-sidebar">
      <ion-icon name="close-outline"></ion-icon>
    </a>
  </div>
  <div class="sidebar-header">
    <img src="<?php echo URLROOT; ?>/img/gravengel.png" alt="Gravengel Logo" class="sidebar-logo">
    <div class="sidebar-brand">PLARIDEL PUBLIC CEMETERY</div>
  </div>
  <a href="#"><ion-icon name="home-outline"></ion-icon><span>Home</span></a>
  <a href="#about"><ion-icon name="information-circle-outline"></ion-icon><span>About</span></a>
  <a href="#features"><ion-icon name="grid-outline"></ion-icon><span>Features</span></a>
  <a href="#contact"><ion-icon name="mail-outline"></ion-icon><span>Contact</span></a>
  <a href="<?php echo URLROOT; ?>/auth/login" class="login-button"><span>Login</span></a>
</div>

<section class="hero-section">
  <div class="hero-stack">
    <img src="<?php echo URLROOT; ?>/img/ggs.png" alt="Gravengel System Logo" class="hero-main-logo">
    
    <p class="hero-tagline">Smart Records. Sacred Grounds.</p>
    <a href="#about" class="cta-button cta-button--light">Explore the System</a>
  </div>
</section>

<section id="about" class="section section-light">
  <div class="container">
    <div class="section-title">
      <h2>About the Project</h2>
    </div>
    <div class="about-grid">
      <div class="about-media">
        <img src="<?php echo URLROOT; ?>/img/G.png" alt="Gravengel Mark">
      </div>
      <div class="about-text">
        <p>
          Gravengel is a digital system created to modernize and make the management of
          Plaridel Public Cemetery efficient. It offers a centralized database for all
          burial records and transactions.
        </p>
        <div class="about-features-list">
            <div class="feature-item">
                <ion-icon name="search-circle-outline"></ion-icon>
                <span>Centralized, searchable records</span>
            </div>
            <div class="feature-item">
                <ion-icon name="shield-checkmark-outline"></ion-icon>
                <span>Secure access and audit trail</span>
            </div>
            <div class="feature-item">
                <ion-icon name="qr-code-outline"></ion-icon>
                <span>Faster on-site verification via QR</span>
            </div>
        </div>
        <a href="#features" class="cta-button cta-button--light">See Features</a>
      </div>
    </div>
  </div>
</section>

<section id="features" class="section section-dark">
  <div class="container">
    <div class="section-title">
      <h2>Key Features</h2>
    </div>
    <div class="feature-card-container">
      <div class="feature-card">
        <ion-icon name="map-outline"></ion-icon>
        <h3>Interactive Cemetery Map</h3>
        <p>Easily find burial plots using the digital map. It speeds up searching and navigation.</p>
      </div>
      <div class="feature-card">
        <ion-icon name="document-text-outline"></ion-icon>
        <h3>Digital Burial Records</h3>
        <p>Prevent loss of records. All information is securely stored in a centralized database.</p>
      </div>
      <div class="feature-card">
        <ion-icon name="qr-code-outline"></ion-icon>
        <h3>QR Code on Each Plot</h3>
        <p>Scan the QR code to immediately view burial details and information of the deceased.</p>
      </div>
    </div>
  </div>
</section>

<section id="contact" class="section section-light">
  <div class="container">
    <div class="section-title">
      <h2>Contact Us</h2>
    </div>
    <div class="contact-grid">
      <form class="contact-form" method="post" action="#">
        <div class="form-row">
          <div class="form-field">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Juan Dela Cruz" required>
          </div>
          <div class="form-field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="juan@example.com" required>
          </div>
        </div>
        <div class="form-field">
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" placeholder="Inquiry">
        </div>
        <div class="form-field">
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="6" placeholder="Type your message here…" required></textarea>
        </div>
        <button type="submit" class="cta-button cta-button--light">Send Message</button>
      </form>
      <div class="contact-map">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2964.7259579037627!2d120.85002063898197!3d14.886813688591243!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3396547d455fc33f%3A0x1e3b748eddaf0bb1!2sPlaridel%20Public%20Cemetery!5e1!3m2!1sen!2sph!4v1758296980591!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </div>
</section>

<?php require APPROOT . '/views/includes/footer.php'; ?>