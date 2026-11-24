<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Produk awal: nama diambil dari gambar katalog di website lama.
     * Harga dikosongkan (tampil "Tanya harga") sampai admin mengisi harga resmi.
     * Gambar masih diambil dari website lama, unggah ulang lewat Admin > Produk.
     */
    public function run(): void
    {
        if (Product::exists()) {
            return;
        }

        $old = 'https://www.mpk-herbattaubah.com/system/images/produk/';
        $products = [
            ['Nektar Pro', 'Herbal', 'Minuman madu alami berkualitas tinggi untuk menjaga daya tahan tubuh dan stamina.', '8e6be6a123e001452a4874d910c2d0db.jpg'],
            ['Kopi Ceria', 'Herbal', 'Kopi herbal yang diracik dari bahan pilihan, nikmat dan menyegarkan.', '08237cbe4bea3bd344400df228bf7a0b.jpg'],
            ['Susu Kambing Etawa', 'Herbal', 'Susu kambing etawa bubuk untuk membantu menjaga kesehatan tulang dan pernapasan.', 'f4801b0e3daf7e687822acf7595e00f9.jpg'],
            ['Susu Kambing MPK', 'Herbal', 'Susu kambing kemasan sachet, praktis untuk keluarga.', '65e065ebf8fa07cc09630ed16db75409.jpg'],
            ['Madu Anak Ceria', 'Madu', 'Madu herbal untuk anak, membantu nafsu makan dan daya tahan tubuh.', '7cb2d921992fd0aeade147418d49fe6f.jpg'],
            ['Sauda Prolis', 'Herbal', 'Perpaduan habbatussauda dan propolis.', 'b61cb485610878195d12b26250128929.jpeg'],
            ['Madu Hutan Premium', 'Madu', 'Madu hutan multiflora, bersarang di pohon.', '5ea572bde9b87caf1630fa7486291d4b.jpeg'],
            ['Royal Oud Elixir', 'Parfum', 'Parfum non-alkohol beraroma oud.', '7ad1adcc91ee86fccfe4cfd2e53cbb5b.jpeg'],
            ['Habasy', 'Herbal', 'Minyak / kapsul herbal habbatussauda.', 'e51cc759b6f4ea86facf89ff61061704.jpeg'],
            ['Gamat Emas', 'Herbal', 'Ekstrak teripang emas untuk membantu pemulihan.', '8a4243c0a442ebf22b6771437a2e00ab.jpg'],
            ['Labu Kuning Kurma', 'Herbal', 'Minuman herbal labu kuning dan kurma.', 'f02eae30d63922c4f410ec36e3c6c6d2.jpeg'],
            ['Spirulina Kapsul', 'Herbal', 'Spirulina dalam kapsul, sumber nutrisi alami.', '8326e96a04950c39ee13ed7c9be1be39.jpg'],
            ['Minyak Siaga Ceria', 'Herbal', 'Minyak herbal untuk keluarga.', '152161e4e67fd991b48cb3f2fe76f1c1.jpg'],
            ['Herba Mindset', 'Herbal', 'Minuman serbuk rasa jahe / stevia / apel hijau.', 'af52d27217e7605351bfe1f4d2118030.jpeg'],
            ['Cream Herba Ceria', 'Herbal', 'Krim herbal untuk perawatan kulit.', '9145a169ace71b3ba87338a4d76baa56.jpg'],
            ['Madu Kuning Premium', 'Madu', 'Madu dengan kunyit pilihan.', 'eb29b7dd57d4dd1d3dac36c6e661cd27.jpg'],
            ['Madu Pahit Premium', 'Madu', 'Madu pahit dari hutan pilihan.', 'ea431c13f3309400ea29abd87b031c14.jpeg'],
            ['Haviza Oil', 'Herbal', 'Minyak herbal sinergi habbatussauda, VCO, dan zaitun.', '9abaddf3350f663fa099d67c61c0f96f.jpeg'],
            ["Dawaa'u Syifa", 'Herbal', 'Ramuan herbal pilihan.', '24c29950c881ca80c6728eb66083fe27.jpeg'],
            ['Minyak Herba Siaga Tangguh', 'Herbal', 'Minyak herbal untuk berbagai keperluan.', 'b6b3aa537822186c143d0db7df933b1e.jpeg'],
            ['MPK R-05', 'Herbal', 'Minuman herbal pilihan.', '9b40a599dbc9fcaf6c99e2ae9aa068a8.jpeg'],
            ['Qowi 99', 'Herbal', 'Minuman herbal untuk stamina.', '19e37fd40ca3ba16dc8d971240880ae6.jpg'],
            ['MPK Water', 'Minuman', 'Air minum beroksigen kemasan.', '2cfe590bd2ca50a55194ff7e9f46fcea.jpg'],
            ['Madu Ternak Endemik', 'Madu', 'Madu ternak dari lebah endemik.', '200b1f7c69e908dd90b806151a5836d3.jpg'],
            ['Qtuk Ikan Gabus', 'Herbal', 'Kapsul ekstrak ikan gabus.', '7da4ee3fabf925d74870f548cdfc5392.jpeg'],
            ['MPK Skincare Day Cream', 'Skincare', 'Krim siang rangkaian MPK Skincare.', 'baff137e7fcc37108de43c001faf8ca1.jpg'],
            ['MPK Skincare Face Wash', 'Skincare', 'Sabun pembersih wajah rangkaian MPK Skincare.', '9f450e8b37b61234795598003494b317.jpg'],
            ['MPK Skincare Jelly Booster', 'Skincare', 'Jelly booster rangkaian MPK Skincare.', '16b1dcad1fc80cf1b1d5a1103309e59e.jpg'],
            ['MPK Skincare Toner', 'Skincare', 'Toner rangkaian MPK Skincare.', '00c40b360e91e43e5813ff7916643ba7.jpg'],
        ];

        foreach ($products as $i => [$name, $cat, $desc, $img]) {
            Product::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'category' => $cat,
                'description' => $desc,
                'image' => $old.$img,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
