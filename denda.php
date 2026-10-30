<?php
$pesan = "";
$hasil = false;

$nama = "";
$judul = "";
$kategori = "";
$lamaPinjam = "";
$tarif = "";
$harga = "";

function aturanKategori($kategori) {
    $aturan = [
        "mahasiswa" => [
            "batas" => 7,
            "keringanan" => 50
        ],
        "dosen" => [
            "batas" => 14,
            "keringanan" => 25
        ],
        "umum" => [
            "batas" => 5,
            "keringanan" => 0
        ]
    ];

    return $aturan[$kategori] ?? null;
}

function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ",", ".");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $judul = trim($_POST["judul_buku"] ?? "");
    $kategori = $_POST["kategori"] ?? "";

    $lamaPinjam = filter_var(
        $_POST["lama_pinjam"] ?? "",
        FILTER_VALIDATE_INT
    );

    $tarifInput = trim($_POST["tarif"] ?? "");
    $harga = filter_var(
        $_POST["harga_buku"] ?? "",
        FILTER_VALIDATE_FLOAT
    );

    $tarifValid = is_numeric($tarifInput)
        && (float)$tarifInput >= 0;

    if ($nama === "" || $judul === "") {
        $pesan = "Nama peminjam dan judul buku wajib diisi.";
    } elseif (aturanKategori($kategori) === null) {
        $pesan = "Kategori anggota tidak valid.";
    } elseif ($lamaPinjam === false || $lamaPinjam < 1) {
        $pesan = "Lama pinjam harus berupa bilangan bulat minimal 1 hari.";
    } elseif (!$tarifValid) {
        $pesan = "Tarif denda harus berupa angka dan tidak boleh negatif.";
    } elseif ($harga === false || $harga <= 0) {
        $pesan = "Harga buku harus berupa angka lebih dari 0.";
    } else {
        $aturan = aturanKategori($kategori);

        $batasPinjam = $aturan["batas"];
        $persenKeringanan = $aturan["keringanan"];

        $hariTerlambat = max(0, $lamaPinjam - $batasPinjam);

        $dendaDasar = $hariTerlambat * (float)$tarifInput;

        $keringanan = $dendaDasar
            * ($persenKeringanan / 100);

        $dendaSebelumBatas = $dendaDasar - $keringanan;

        $dendaAkhir = min($dendaSebelumBatas, $harga);

        if ($hariTerlambat === 0) {
            $status = "Tepat waktu";
        } elseif ($dendaAkhir >= $harga) {
            $status = "Terlambat — denda dibatasi harga buku";
        } else {
            $status = "Terlambat";
        }

        $hasil = true;
    }
}

function aman($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Denda Buku</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<header>
            <img class=gambar-header src="img/perpustakaan.jpeg" alt="foto perpustakaan">
    <div>
        <h1>Kalkulator Denda Buku</h1>
        <p>Portal Layanan Perpustakaan Kampus</p>
    </div>
</header>

<main class="dua-kolom">
    <section class="kartu">
        <h2>Form Peminjaman</h2>

        <?php if ($pesan !== ""): ?>
            <p class="pesan-error"><?= aman($pesan) ?></p>
        <?php endif; ?>

        <form action="denda.php" method="post">
            <p>
                <label for="nama">Nama Peminjam</label><br>
                <input type="text" id="nama" name="nama"
                   value="<?= aman($nama) ?>" required>
            </p>

            <p>
            <label for="judul_buku">Judul Buku</label><br>
            <input type="text" id="judul_buku" name="judul_buku"
                   value="<?= aman($judul) ?>" required>
            </p>

            <p>
                 <label for="kategori">Kategori Anggota</label><br>
            <select id="kategori" name="kategori" required>
                <option value="">Pilih Kategori</option>
                <option value="mahasiswa"
                    <?= $kategori === "mahasiswa" ? "selected" : "" ?>>
                    Mahasiswa
                </option>
                <option value="dosen"
                    <?= $kategori === "dosen" ? "selected" : "" ?>>
                    Dosen
                </option>
                <option value="umum"
                    <?= $kategori === "umum" ? "selected" : "" ?>>
                    Umum
                </option>
            </p>
            </select>

            <p>
                <label for="lama_pinjam">Lama Pinjam (hari)</label><br>
                <input type="number" id="lama_pinjam" name="lama_pinjam"
                   min="1" step="1"
                   value="<?= aman($lamaPinjam === false ? "" : $lamaPinjam) ?>"
                   required>
            </p>

            <p>
                <label for="tarif">Tarif Denda per Hari (Rp)</label><br>
                <input type="number" id="tarif" name="tarif"
                   min="0" step="any"
                   value="<?= aman($tarif) ?>" required>
            </p>

            <p>
                <label for="harga_buku">Harga Buku (Rp)</label><br>
                <input type="number" id="harga_buku" name="harga_buku"
                   min="0.01" step="any"
                   value="<?= aman($harga === false ? "" : $harga) ?>"
                   required>
            </p>

            <button type="submit">Hitung Denda</button>
            <button type="reset" class="tombol-reset">
                Kosongkan Form
            </button>
        </form>
    </section>

    <section class="kartu keterangan">
        <h2>Aturan Peminjaman</h2>

        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Batas Hari</th>
                    <th>Keringanan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Mahasiswa</td>
                    <td>7 hari</td>
                    <td>50%</td>
                </tr>
                <tr>
                    <td>Dosen</td>
                    <td>14 hari</td>
                    <td>25%</td>
                </tr>
                <tr>
                    <td>Umum</td>
                    <td>5 hari</td>
                    <td>0%</td>
                </tr>
            </tbody>
        </table>

        <h3>Rumus Denda</h3>
        <p>Hari terlambat = max(0, lama pinjam − batas pinjam)</p>
        <p>Denda dasar = hari terlambat × tarif per hari</p>
        <p>Keringanan = denda dasar × persentase keringanan</p>
        <p>Denda akhir = min(denda dasar − keringanan, harga buku)</p>
    </section>

    <?php if ($hasil): ?>
        <section class="kartu hasil">
            <h2>Hasil Perhitungan</h2>

            <dl>
                <dt>Nama Peminjam</dt>
                <dd><?= aman($nama) ?></dd>

                <dt>Judul Buku</dt>
                <dd><?= aman($judul) ?></dd>

                <dt>Kategori Anggota</dt>
                <dd><?= aman(ucfirst($kategori)) ?></dd>

                <dt>Lama Pinjam</dt>
                <dd><?= aman($lamaPinjam) ?> hari</dd>

                <dt>Batas Pinjam</dt>
                <dd><?= $batasPinjam ?> hari</dd>

                <dt>Hari Terlambat</dt>
                <dd><?= $hariTerlambat ?> hari</dd>

                <dt>Denda Dasar</dt>
                <dd><?= rupiah($dendaDasar) ?></dd>

                <dt>Keringanan (<?= $persenKeringanan ?>%)</dt>
                <dd><?= rupiah($keringanan) ?></dd>

                <dt>Denda Akhir</dt>
                <dd><strong><?= rupiah($dendaAkhir) ?></strong></dd>

                <dt>Status</dt>
                <dd><?= aman($status) ?></dd>
            </dl>

            <a href="denda.php">Simulasi Baru</a>
        </section>
    <?php endif; ?>
</main>

<footer>
    <p>Pemrograman Web Kelas A @2026</p>
</footer>
</body>
</html>