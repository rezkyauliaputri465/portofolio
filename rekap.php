<?php

function aturanKategori($kategori)
{
    $aturan = [
        "mahasiswa" => [
            "nama" => "Mahasiswa",
            "batas" => 7,
            "diskon" => 50
        ],
        "dosen" => [
            "nama" => "Dosen",
            "batas" => 14,
            "diskon" => 25
        ],
        "umum" => [
            "nama" => "Umum",
            "batas" => 5,
            "diskon" => 0
        ]
    ];

    return $aturan[$kategori] ?? null;
}

function rupiah($angka)
{
    return "Rp " . number_format($angka, 0, ",", ".");
}

function aman($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, "UTF-8");
}


$tahap = 1;
$error = "";
$hasil = [];
$kategori = "";
$tarif = "";
$jumlahBuku = 0;

$totalDenda = 0;
$jumlahTerlambat = 0;
$jumlahTepatWaktu = 0;
$rataRata = 0;


if (isset($_POST["buat_form"])) {
    $jumlahBuku = filter_var(
        $_POST["jumlah_buku"] ?? "",
        FILTER_VALIDATE_INT
    );

    $kategori = $_POST["kategori"] ?? "";
    $tarifInput = trim($_POST["tarif"] ?? "");
    $aturan = aturanKategori($kategori);

    if (
        $jumlahBuku === false ||
        $jumlahBuku < 1 ||
        $jumlahBuku > 10
    ) {
        $error = "Jumlah buku harus berupa bilangan bulat antara 1 sampai 10.";
    } elseif ($aturan === null) {
        $error = "Kategori anggota tidak valid.";
    } elseif (
        $tarifInput === "" ||
        !is_numeric($tarifInput) ||
        !is_finite((float) $tarifInput) ||
        (float) $tarifInput < 0
    ) {
        $error = "Tarif denda harus berupa angka yang valid dan tidak boleh negatif.";
    } else {
        $tarif = (float) $tarifInput;
        $tahap = 2;
    }
}


if (isset($_POST["hitung_rekap"])) {
    $tahap = 3;

    $jumlahBuku = filter_var(
        $_POST["jumlah_buku"] ?? "",
        FILTER_VALIDATE_INT
    );

    $kategori = $_POST["kategori"] ?? "";
    $tarifInput = trim($_POST["tarif"] ?? "");
    $aturan = aturanKategori($kategori);

    $judulBuku = $_POST["judul_buku"] ?? [];
    $lamaPinjam = $_POST["lama_pinjam"] ?? [];
    $hargaBuku = $_POST["harga_buku"] ?? [];

    
    if (
        $jumlahBuku === false ||
        $jumlahBuku < 1 ||
        $jumlahBuku > 10
    ) {
        $error = "Jumlah buku tidak valid.";
    } elseif ($aturan === null) {
        $error = "Kategori anggota tidak valid.";
    } elseif (
        $tarifInput === "" ||
        !is_numeric($tarifInput) ||
        !is_finite((float) $tarifInput) ||
        (float) $tarifInput < 0
    ) {
        $error = "Tarif denda tidak valid.";
    } elseif (
        !is_array($judulBuku) ||
        !is_array($lamaPinjam) ||
        !is_array($hargaBuku) ||
        count($judulBuku) !== $jumlahBuku ||
        count($lamaPinjam) !== $jumlahBuku ||
        count($hargaBuku) !== $jumlahBuku
    ) {
        $error = "Data buku tidak lengkap. Silakan mulai rekap baru.";
    } else {
        $tarif = (float) $tarifInput;

        
        for ($i = 0; $i < $jumlahBuku; $i++) {
            $judul = trim((string) ($judulBuku[$i] ?? ""));
            $lama = filter_var(
                $lamaPinjam[$i] ?? "",
                FILTER_VALIDATE_INT
            );
            $hargaInput = trim((string) ($hargaBuku[$i] ?? ""));

            
            if ($judul === "") {
                $error = "Judul buku nomor " . ($i + 1) . " wajib diisi.";
                break;
            }

            if ($lama === false || $lama < 1) {
                $error = "Lama pinjam buku nomor " . ($i + 1)
                    . " harus berupa bilangan bulat minimal 1 hari.";
                break;
            }

            if (
                $hargaInput === "" ||
                !is_numeric($hargaInput) ||
                !is_finite((float) $hargaInput) ||
                (float) $hargaInput <= 0
            ) {
                $error = "Harga buku nomor " . ($i + 1)
                    . " harus berupa angka lebih dari 0.";
                break;
            }

            $harga = (float) $hargaInput;

            
            $batas = $aturan["batas"];
            $persenDiskon = $aturan["diskon"];

            $hariTerlambat = max(0, $lama - $batas);

           
            $dendaAwal = $hariTerlambat * $tarif;
            $potongan = $dendaAwal * ($persenDiskon / 100);
            $dendaSetelahDiskon = $dendaAwal - $potongan;

            
            $dendaAkhir = min($dendaSetelahDiskon, $harga);

            
            if ($hariTerlambat == 0) {
                $status = "Tepat waktu";
                $jumlahTepatWaktu++;
            } else {
                $status = "Terlambat";
                $jumlahTerlambat++;
            }

            
            $hasil[] = [
                "judul" => $judul,
                "lama" => $lama,
                "terlambat" => $hariTerlambat,
                "denda" => $dendaAkhir,
                "status" => $status
            ];

            $totalDenda += $dendaAkhir;
        }

        
        if ($error !== "") {
            $hasil = [];
            $totalDenda = 0;
            $jumlahTerlambat = 0;
            $jumlahTepatWaktu = 0;
        } else {
            $rataRata = $totalDenda / $jumlahBuku;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Denda Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
           <img class=gambar-header src="img/perpustakaan.jpeg" alt="foto perpustakaan">
    <div class="header-content">
        <h1>Portal Layanan Perpustakaan</h1>
        <p>Rekap Denda Peminjaman Buku</p>
    </div>
</header>

<main>
    <section class="card">
        <h2>Rekap Denda Buku</h2>

        <?php if ($error !== ""): ?>
            <p class="pesan-error">
                <?= aman($error) ?>
            </p>
        <?php endif; ?>

        <?php if ($tahap === 1): ?>

            <!-- TAHAP 1 -->
            <p>Masukkan jumlah buku dan informasi denda untuk membuat form rekap.</p>

            <form action="rekap.php" method="post">
                <div class="form-group">
                    <label for="jumlah_buku">Jumlah Buku (1–10)</label>
                    <input
                        type="number"
                        id="jumlah_buku"
                        name="jumlah_buku"
                        min="1"
                        max="10"
                        step="1"
                        required
                        value="<?= aman($_POST["jumlah_buku"] ?? "") ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori Anggota</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
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
                    </select>
                </div>

                <div class="form-group">
                    <label for="tarif">Tarif Denda per Hari (Rp)</label>
                    <input
                        type="number"
                        id="tarif"
                        name="tarif"
                        min="0"
                        step="any"
                        required
                        value="<?= aman($_POST["tarif"] ?? "") ?>"
                    >
                </div>

                <button type="submit" name="buat_form">
                    Buat Form Buku
                </button>
            </form>

        <?php elseif ($tahap === 2): ?>

            <!-- TAHAP 2 -->
            <p>
                Kategori:
                <strong><?= aman($aturan["nama"]) ?></strong>
            </p>

            <p>
                Batas pinjam:
                <strong><?= aman($aturan["batas"]) ?> hari</strong>
            </p>

            <form action="rekap.php" method="post">
                <input
                    type="hidden"
                    name="jumlah_buku"
                    value="<?= aman($jumlahBuku) ?>"
                >

                <input
                    type="hidden"
                    name="kategori"
                    value="<?= aman($kategori) ?>"
                >

                <input
                    type="hidden"
                    name="tarif"
                    value="<?= aman($tarif) ?>"
                >

                <?php for ($i = 0; $i < $jumlahBuku; $i++): ?>
                    <fieldset class="buku-form">
                        <legend>Buku <?= $i + 1 ?></legend>

                        <div class="form-group">
                            <label for="judul_<?= $i ?>">
                                Judul Buku
                            </label>
                            <input
                                type="text"
                                id="judul_<?= $i ?>"
                                name="judul_buku[]"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="lama_<?= $i ?>">
                                Lama Pinjam (hari)
                            </label>
                            <input
                                type="number"
                                id="lama_<?= $i ?>"
                                name="lama_pinjam[]"
                                min="1"
                                step="1"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="harga_<?= $i ?>">
                                Harga Buku (Rp)
                            </label>
                            <input
                                type="number"
                                id="harga_<?= $i ?>"
                                name="harga_buku[]"
                                min="0.01"
                                step="any"
                                required
                            >
                        </div>
                    </fieldset>
                <?php endfor; ?>

                <button type="submit" name="hitung_rekap">
                    Hitung Rekap Denda
                </button>
            </form>

        <?php elseif ($tahap === 3 && $error === ""): ?>

            <h3>Hasil Rekap Denda</h3>

            <p>
                Kategori anggota:
                <strong><?= aman($aturan["nama"]) ?></strong>
            </p>

            <p>
                Tarif denda per hari:
                <strong><?= rupiah($tarif) ?></strong>
            </p>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Judul Buku</th>
                            <th>Lama Pinjam</th>
                            <th>Hari Terlambat</th>
                            <th>Denda Akhir</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($hasil as $index => $buku): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= aman($buku["judul"]) ?></td>
                                <td><?= aman($buku["lama"]) ?> hari</td>
                                <td><?= aman($buku["terlambat"]) ?> hari</td>
                                <td><?= rupiah($buku["denda"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h3>Ringkasan</h3>

            <dl class="ringkasan">
                <dt>Total denda</dt>
                <dd><?= rupiah($totalDenda) ?></dd>

                <dt>Jumlah buku terlambat</dt>
                <dd><?= $jumlahTerlambat ?> buku</dd>

                <dt>Jumlah buku tepat waktu</dt>
                <dd><?= $jumlahTepatWaktu ?> buku</dd>

                <dt>Rata-rata denda per buku</dt>
                <dd><?= rupiah(round($rataRata)) ?></dd>
            </dl>

            <p class="pesan-sukses">
                Rekap denda berhasil dihitung.
            </p>

            <a class="tombol-link" href="rekap.php?baru=1">
                Mulai Rekap Baru
            </a>

        <?php endif; ?>
    </section>

    <section class="card">
        <h3>Aturan Denda</h3>

        <ul>
            <li>
                <strong>Mahasiswa:</strong>
                batas 7 hari, diskon denda 50%.
            </li>
            <li>
                <strong>Dosen:</strong>
                batas 14 hari, diskon denda 25%.
            </li>
            <li>
                <strong>Umum:</strong>
                batas 5 hari, tanpa diskon.
            </li>
        </ul>

        <h3>Rumus Perhitungan</h3>

        <p>
            Hari terlambat = maksimum dari 0 dan
            (lama pinjam − batas pinjam).
        </p>

        <p>
            Denda awal = hari terlambat × tarif harian.
        </p>

        <p>
            Potongan = denda awal × persentase diskon.
        </p>

        <p>
            Denda akhir = nilai terkecil antara denda setelah diskon
            dan harga buku.
        </p>
    </section>
</main>

<footer>
    <p>Portal Layanan Perpustakaan Kampus</p>
</footer>

</body>
</html>