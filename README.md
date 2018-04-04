# Ipaymu (Payment Gateway Indonesia)
[![Latest Stable Version](https://poser.pugx.org/steevenz/ipaymu/v/stable)](https://packagist.org/packages/steevenz/ipaymu) [![Total Downloads](https://poser.pugx.org/steevenz/ipaymu/downloads)](https://packagist.org/packages/steevenz/ipaymu) [![Latest Unstable Version](https://poser.pugx.org/steevenz/ipaymu/v/unstable)](https://packagist.org/packages/steevenz/ipaymu) [![License](https://poser.pugx.org/steevenz/ipaymu/license)](https://packagist.org/packages/steevenz/ipaymu)

[Ipaymu][11] API PHP Class Library berfungsi untuk melakukan request API [Ipaymu][11].

Instalasi
---------
Cara terbaik untuk melakukan instalasi library ini adalah dengan menggunakan [Composer][7]
```
composer require steevenz/ipaymu
```

Penggunaan
----------
```php
use Steevenz\Ipaymu;

/*
 * --------------------------------------------------------------
 * Inisiasi Class Ipaymu
 * --------------------------------------------------------------
 */
 // Untuk menggunakan API Ipaymu Account saja.
 $ipaymu = new Ipaymu();
 $ipaymu->setApiKey('API_KEY_ANDA');
 
 // Untuk menggunakan API Ipaymu Account dan Webstore.
 $ipaymu = new Ipaymu([
    'apiKey' => 'API_KEY_ANDA',
    
    // Konfigurasi Url diperlukan untuk melakukan transaksi ke ipaymu
    'url' => [
        'return' => 'http://www.domainanda.com/terimakasih.html'
        'notify' => ' http://www.domainanda.com/notify-ipaymu.php'
        'cancel' => 'http://www.domainanda.com/batal.html'
    ]
 ]);
 
/*
 * --------------------------------------------------------------
 * Mendapatkan informasi akun Ipaymu
 * 
 * @return array
 * --------------------------------------------------------------
 */
$account = $ipaymu->getAccount();

/*
 * --------------------------------------------------------------
 * Cek Saldo Akun
 * Untuk mengecek jumlah saldo terakhir Anda.
 *
 * @return int
 * --------------------------------------------------------------
 */
$balance = $ipaymu->checkAccountBalance();

/*
 * --------------------------------------------------------------
 * Cek Status Akun
 * Untuk mengecek status akun iPaymu.
 *
 * @return string
 * --------------------------------------------------------------
 */
$status = $ipaymu->checkAccountStatus();

/*
 * --------------------------------------------------------------
 * Cek Transaksi
 *
 * @param string $trxId Kode Unik Transaksi.
 * @return array|bool Returns FALSE if failed.
 * --------------------------------------------------------------
 */
$transaction = $ipaymu->checkTransaction('IDX-1234567890');

/*
 * --------------------------------------------------------------
 * Melakukan transaksi pembayaran dengan single produk
 *
 * @return array|bool   Returns FALSE if failed or returns array contains
 *                      Ipaymu transaction Url.
 * --------------------------------------------------------------
 */
$ipaymu->addTransaction([
   'id' => 'INV-1234567890',
   'product' => [
       'name' => 'Shoes'
       'price' => 10000,
       'quantity' => 1,
       'description' => 'Amazing Shoes'
   ]
]);

/*
 * --------------------------------------------------------------
 * Melakukan transaksi pembayaran PayPal dengan single produk
 * 
 * @return array|bool   Returns FALSE if failed or returns array contains
 *                      Ipaymu transaction Url.
 * --------------------------------------------------------------
 */
$ipaymu->addTransaction([
   'id' => 'INV-1234567890',
   'product' => [
       'name' => 'Shoes'
       'price' => 10000,
       'price_usd' => 1, // Wajib menyertakan harga dalam kurs USD
       'quantity' => 1,
       'description' => 'Amazing Shoes'
   ]
], 'akunpaypalku@domain.com');

/*
 * --------------------------------------------------------------
 * Melakukan transaksi pembayaran dengan multi produk
 * 
 * @return array|bool   Returns FALSE if failed or returns array contains
 *                      Ipaymu transaction Url.
 * --------------------------------------------------------------
 */
 $ipaymu->addTransaction([
      'id' => 'INV-1234567890',
      'products' => [
          [
              'name' => 'Shoes',
              'price' => 10000,
              'quantity' => 1,
              'description' => 'Amazing Shoes'
          ],
          [
              'name' => 'Bag',
              'price' => 5000,
              'quantity' => 2,
              'description' => 'Amazing Bag'
          ]
      ]
 ]);
 
 /*
  * --------------------------------------------------------------
  * Melakukan transaksi pembayaran PayPal dengan multi produk
  * 
  * @return array|bool   Returns FALSE if failed or returns array contains
  *                      Ipaymu transaction Url.
  * --------------------------------------------------------------
  */
  $ipaymu->addTransaction([
       'id' => 'INV-1234567890',
       'products' => [
           [
               'name' => 'Shoes',
               'price' => 10000,
               'price_usd' => 1, // Wajib menyertakan harga dalam kurs USD
               'quantity' => 1,
               'description' => 'Amazing Shoes'
           ],
           [
               'name' => 'Bag',
               'price' => 5000,
               'price_usd' => 1, // Wajib menyertakan harga dalam kurs USD
               'quantity' => 2,
               'description' => 'Amazing Bag'
           ]
       ]
  ], 'akunpaypalku@domain.com');

/*
 * --------------------------------------------------------------
 * Mendapatkan original response object.
 * --------------------------------------------------------------
 */
 $response = $ipaymu->getResponse();
 
/*
 * --------------------------------------------------------------
 * Mendapatkan informasi error.
 * --------------------------------------------------------------
 */
 $errors = $ipaymu->getErrors();
```

Untuk keterangan lebih lengkap dapat dibaca di [Wiki](https://github.com/steevenz/ipaymu/wiki)

Ide, Kritik dan Saran
---------------------
Jika anda memiliki ide, kritik ataupun saran, anda dapat mengirimkan email ke [steevenz@stevenz.com][3]. 
Anda juga dapat mengunjungi situs pribadi saya di [steevenz.com][1]

Bugs and Issues
---------------
Jika anda menemukan bugs atau issue, anda dapat mempostingnya di [Github Issues][6].

Requirements
------------
- PHP 5.6+
- [Composer][9]
- [O2System Curl][10]

Referensi
---------
Untuk mengetahui lebih lanjut mengenai RajaOngkir API, lihat di [Dokumentasi API Ipaymu][12].

[1]: http://steevenz.com
[2]: http://steevenz.com/blog/ipaymu-api
[3]: mailto:steevenz@steevenz.com
[4]: http://github.com/steevenz/ipaymu
[5]: http://github.com/steevenz/ipaymu/wiki
[6]: http://github.com/steevenz/ipaymu/issues
[7]: https://packagist.org/packages/steevenz/ipaymu
[9]: https://getcomposer.org
[10]: http://github.com/o2system/curl
[11]: http://ipaymu.com
[12]: http://ipaymu.com/dokumentasi-api
