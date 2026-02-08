ADMIN - instalace

Přihlášení je záměrně co nejjednodušší (bez databáze, bez hashování hesla).

1) Kam uložit přihlašovací údaje

Preferované:
- vytvořte soubor: config/admin.secret.php
- obsah (vrací pole):

  <?php
  return [
    'ADMIN_USER' => 'admin',
    'ADMIN_PASS' => 'nejakeHeslo'
  ];

Fallback:
- admin/.env.php (stejný formát)

2) URL administrace

/admin/login.php
/admin/

3) Poznámky
- Administrace používá PHP session.