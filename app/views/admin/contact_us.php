<?php
// header include
if (file_exists(APPROOT . '/views/includes/admin_header.php')) {
    require APPROOT . '/views/includes/admin_header.php';
}
// IMPORTANT: set page title for active sidebar highlight
$data['title'] = 'Contact Us';
?>

<style>
/* Page container */
.contact-hero{
  max-width:1200px;margin:20px auto 0;padding:24px 20px;
  background:#fff;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.06);
}
/* LOGO ONLY (no text) */
.brand{display:flex;justify-content:center;align-items:center;margin-top:6px;}
.brand img.logo{width:400px;height:auto;display:block;}
/* Divider */
.hr{height:2px;background:#c7c7c7;margin:22px auto 0;max-width:95%;border-radius:2px;}
/* Title */
.section-title{ text-align:center;font-size:28px;margin:24px 0 8px;color:#a92a2a;font-weight:800; }
/* Cards */
.team-wrap{ max-width:1200px;margin:22px auto;display:grid;gap:20px;grid-template-columns:repeat(3,1fr); }
.card{ background:#f0efef;border-radius:14px;padding:22px 18px;text-align:center;box-shadow:0 6px 18px rgba(0,0,0,.05);border:1px solid #e5e5e5; }
.avatar{ width:160px;height:160px;border-radius:50%;object-fit:cover;background:#fff;display:block;margin:0 auto 12px;border:8px solid #fff;box-shadow:0 2px 10px rgba(0,0,0,.06); }
.name{ font-weight:800;color:#a92a2a;margin:10px 0 6px;font-size:19px; }
.role{ color:#6f6f6f;font-size:13px;line-height:1.35; }
/* Footer copy */
.contact-box{ max-width:900px;margin:6px auto 32px;text-align:center;color:#444;font-size:14px; }
/* Responsive */
@media (max-width: 992px){ .team-wrap{grid-template-columns:1fr 1fr;} }
@media (max-width: 640px){ .team-wrap{grid-template-columns:1fr;} }
</style>

<section class="contact-hero">
  <!-- LOGO ONLY -->
  <div class="brand">
    <img class="logo" src="<?php echo URLROOT; ?>/public/img/tri.png" alt="Logo">
  </div>
  <div class="hr"></div>

  <h2 class="section-title">Meet Our Team</h2>

  <div class="team-wrap">
    <!-- CARD 1 -->
    <div class="card">
      <img class="avatar" src="<?php echo URLROOT; ?>/public/img/Orion.png" alt="Orion Seal R.">
      <div class="name">Cano, Orion Seal R.</div>
      <div class="role">Full-Stack Developer &amp; Project Manager<br>Manager</div>
    </div>

    <!-- CARD 2 -->
    <div class="card">
      <img class="avatar" src="<?php echo URLROOT; ?>/public/img/Juana.png" alt="Juana Marie E.">
      <div class="name">Vinta, Juana Marie E.</div>
      <div class="role">System Analyst &amp; UI/UX</div>
    </div>

    <!-- CARD 3 -->
    <div class="card">
      <img class="avatar" src="<?php echo URLROOT; ?>/public/img/Althea.png" alt="Althea DC.">
      <div class="name">Marcelino, Althea DC.</div>
      <div class="role">Document Specialist</div>
    </div>
  </div>

  <div class="contact-box">
    For inquiries, feel free to reach out via your preferred channel or add a contact form here later.
  </div>
</section>

<?php
// footer include
if (file_exists(APPROOT . '/views/includes/admin_footer.php')) {
    require APPROOT . '/views/includes/admin_footer.php';
}
?>
