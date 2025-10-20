/cafe-ordering/                     <-- root project
  /public/
    index.php            -- landing (scan QR?table=1)
    menu.php             -- daftar menu
    add_cart.php         -- aksi tambah ke cart
    cart.php             -- lihat + ubah qty
    checkout.php         -- pilih pembayaran
    pay_qris.php         -- inisiasi transaksi Midtrans (sandbox)
    midtrans_notify.php  -- webhook/notification handler
    success.php          -- halaman sukses
    cancel.php           -- halaman batal
    assets/
      tailwind.css (optional build) or use CDN in head
      images/
  /app/
    helpers.php
  /config/
    config.php           -- DB + midtrans config
  /admin/
    login.php
    dashboard.php
    orders.php
    order_detail.php
    logout.php
  composer.json (optional)
  sql/
    schema.sql
  README.md
