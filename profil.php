<?php

date_default_timezone_set("Asia/Makassar");
$tanggal = date("d-m-Y");
$jam = date("H:i:s");

$pesan = "";
$data_lengkap = true;
$nama = "";
$nomor_anggota = "";
$email = "";
$program_studi = "";
$kategori = "";
$kategori_diizinkan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $nomor_anggota = trim($_POST["nomor_anggota"] ?? "");
    $program_studi = trim($_POST["program_studi"] ?? "");
    $email = filter_var($_POST["email"] ?? "", FILTER_VALIDATE_EMAIL);
    $kategori = $_POST["kategori"] ?? "";

    if ($kategori != "mahasiswa" &&
        $kategori != "dosen" &&
        $kategori != "umum")
        {
        $pesan = "Kategori anggota tidak valid!";
    }
}

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Anggota</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
                <img class=gambar-header src="img/perpustakaan.jpeg" alt="foto perpustakaan">
        <div>
            <h1>Profil Keanggotaan Perpustakaan</h1>
            <p>Berikut Rincian Keanggotaan Anda ! </p>
        </div>
    </header>
    <main>
        <?php if($data_lengkap): ?>
            <div class="profil">
                <img class="foto-profil" src="img/gambar-perpus.jpeg" alt="gambar perpustakaan">
                <dl>
                    <dt>Nama:</dt>
                    <dd><?= htmlspecialchars($nama, ENT_QUOTES, "UTF-8") ?></dd>
                    <dt>Nomor Anggota / NIM:</dt>
                    <dd><?= htmlspecialchars($nomor_anggota, ENT_QUOTES, "UTF-8") ?></dd>
                    <dt>Program Studi:</dt>
                    <dd><?= htmlspecialchars($program_studi, ENT_QUOTES, "UTF-8") ?></dd>
                    <dt>Email:</dt>
                    <dd><?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?></dd>
                    <dt>Kategori:</dt>
                    <dd><?= htmlspecialchars($kategori, ENT_QUOTES, "UTF-8") ?></dd>

                    <dt>Tanggal Pemrosesan:</dt>
                    <dd><?= $tanggal?></dd>
                    <dt>Jam Pemrosesan:</dt>
                    <dd><?= $jam?> WITA</dd>
                </dl>
            </div>
            <?php else: ?>
                <p>Isi dengan benar</p>
            <?php endif; ?>
            <p><a class="kembali" href="form-anggota.html">Kembali Ke halaman form</a></p>
    </main>
    <footer>
        <p>Pemrograman Web Kelas C @2026</p>
    </footer>
    
</body>
</html>