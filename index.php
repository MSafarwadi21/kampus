<?php
require 'koneksi.php';
harus_login();

$user = $_SESSION['user'];
$role = $user['role'];
$pesan = "";

// ==================================================
// BAGIAN PROSES (kalau user klik tombol / submit form)
// ==================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ---------- ADMIN: Setujui / Tolak KRS ----------
    if ($role == 'admin') {
        $id   = $_POST['id_krs'];
        $aksi = $_POST['aksi'];

        $query = "UPDATE krs SET status='$aksi' WHERE id_krs=$id";
        mysqli_query($conn, $query);
        $pesan = "KRS berhasil di-$aksi";
    }

    // ---------- MAHASISWA: Ambil Kelas ----------
    if ($role == 'mahasiswa') {
        // Cari NIM mahasiswa
        $q = mysqli_query($conn, "SELECT nim FROM mahasiswa WHERE id_user=" . $user['id']);
        $mhs = mysqli_fetch_assoc($q);
        $nim = $mhs['nim'];

        $id_kelas = $_POST['id_kelas'];

        // Coba insert KRS
        $query = "INSERT INTO krs (nim, id_kelas) VALUES ('$nim', $id_kelas)";
        if (mysqli_query($conn, $query)) {
            $pesan = "KRS berhasil diajukan";
        } else {
            $pesan = "Gagal: mata kuliah ini sudah kamu ambil";
        }
    }

    // ---------- DOSEN: Input Nilai ----------
    if ($role == 'dosen') {
        $id_krs = $_POST['id_krs'];
        $angka  = $_POST['angka'];

        // Tentukan huruf & bobot
        if ($angka >= 85)      { $huruf = 'A'; $bobot = 4; }
        elseif ($angka >= 75)  { $huruf = 'B'; $bobot = 3; }
        elseif ($angka >= 65)  { $huruf = 'C'; $bobot = 2; }
        elseif ($angka >= 50)  { $huruf = 'D'; $bobot = 1; }
        else                   { $huruf = 'E'; $bobot = 0; }

        // Cek tombol yang ditekan (publish atau draft)
        if (isset($_POST['publish'])) {
            $publish = 1;
            $pesan = "Nilai berhasil dipublish";
        } else {
            $publish = 0;
            $pesan = "Nilai disimpan sebagai draft";
        }

        // Cek apakah nilai sudah ada
        $cek = mysqli_query($conn, "SELECT id_nilai FROM nilai WHERE id_krs=$id_krs");

        if (mysqli_num_rows($cek) > 0) {
            // Sudah ada → UPDATE
            $query = "UPDATE nilai SET angka=$angka, huruf='$huruf', bobot=$bobot, publish=$publish
                      WHERE id_krs=$id_krs";
        } else {
            // Belum ada → INSERT
            $query = "INSERT INTO nilai (id_krs, angka, huruf, bobot, publish)
                      VALUES ($id_krs, $angka, '$huruf', $bobot, $publish)";
        }
        mysqli_query($conn, $query);

        // Kalau publish, hitung ulang IPK mahasiswa
        if ($publish == 1) {
            $query = "
                SELECT k.nim,
                       SUM(mk.sks * n.bobot) / SUM(mk.sks) AS ipk_baru
                FROM krs k
                JOIN kelas kls      ON kls.id_kelas = k.id_kelas
                JOIN matakuliah mk  ON mk.kode = kls.kode_mk
                JOIN nilai n        ON n.id_krs = k.id_krs
                WHERE k.id_krs = $id_krs
                GROUP BY k.nim
            ";
            $hasil = mysqli_query($conn, $query);
            $data  = mysqli_fetch_assoc($hasil);

            if ($data) {
                $ipk = round($data['ipk_baru'], 2);
                mysqli_query($conn, "UPDATE mahasiswa SET ipk=$ipk WHERE nim='{$data['nim']}'");
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SIMKAM</title>
</head>
<body>
    <h2>SIMKAM - Halo <?= e($user['nama']) ?> (<?= $role ?>)</h2>
    <a href="logout.php">Logout</a>
    <hr>

    <?php if ($pesan != "") { ?>
        <p style="color:green"><b><?= $pesan ?></b></p>
    <?php } ?>

<?php
// ==================================================
// BAGIAN TAMPILAN BERDASARKAN ROLE
// ==================================================

// ---------------- ADMIN ----------------
if ($role == 'admin') {
    $query = "
        SELECT k.id_krs, m.nim, mk.nama AS nama_mk, mk.sks, k.status
        FROM krs k
        JOIN mahasiswa m    ON m.nim = k.nim
        JOIN kelas kls      ON kls.id_kelas = k.id_kelas
        JOIN matakuliah mk  ON mk.kode = kls.kode_mk
        ORDER BY k.id_krs DESC
    ";
    $hasil = mysqli_query($conn, $query);
    ?>
    <h3>Verifikasi KRS</h3>
    <table border="1" cellpadding="6">
        <tr>
            <th>NIM</th>
            <th>Mata Kuliah</th>
            <th>SKS</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>
        <tr>
            <td><?= $row['nim'] ?></td>
            <td><?= $row['nama_mk'] ?></td>
            <td><?= $row['sks'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <?php if ($row['status'] == 'DIAJUKAN') { ?>
                <form method="post" style="display:inline">
                    <input type="hidden" name="id_krs" value="<?= $row['id_krs'] ?>">
                    <button name="aksi" value="DISETUJUI">Setujui</button>
                    <button name="aksi" value="DITOLAK">Tolak</button>
                </form>
                <?php } else { echo "-"; } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php
}

// ---------------- MAHASISWA ----------------
elseif ($role == 'mahasiswa') {
    // Data mahasiswa
    $q = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE id_user=" . $user['id']);
    $mhs = mysqli_fetch_assoc($q);

    // Daftar kelas tersedia
    $q = mysqli_query($conn, "
        SELECT kls.id_kelas, mk.nama AS nama_mk, mk.sks
        FROM kelas kls
        JOIN matakuliah mk ON mk.kode = kls.kode_mk
    ");

    // KRS saya
    $q2 = mysqli_query($conn, "
        SELECT mk.nama AS nama_mk, mk.sks, k.status
        FROM krs k
        JOIN kelas kls      ON kls.id_kelas = k.id_kelas
        JOIN matakuliah mk  ON mk.kode = kls.kode_mk
        WHERE k.nim = '{$mhs['nim']}'
    ");

    // Nilai saya (yang sudah dipublish)
    $q3 = mysqli_query($conn, "
        SELECT mk.nama AS nama_mk, n.angka, n.huruf, n.bobot
        FROM krs k
        JOIN kelas kls      ON kls.id_kelas = k.id_kelas
        JOIN matakuliah mk  ON mk.kode = kls.kode_mk
        JOIN nilai n        ON n.id_krs = k.id_krs
        WHERE k.nim = '{$mhs['nim']}' AND n.publish = 1
    ");
    ?>
    <p>
        <b>NIM:</b> <?= $mhs['nim'] ?> &nbsp;|&nbsp;
        <b>IPK:</b> <?= $mhs['ipk'] ?>
    </p>

    <h3>Kelas Tersedia</h3>
    <table border="1" cellpadding="6">
        <tr><th>Mata Kuliah</th><th>SKS</th><th>Aksi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($q)) { ?>
        <tr>
            <td><?= $row['nama_mk'] ?></td>
            <td><?= $row['sks'] ?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="id_kelas" value="<?= $row['id_kelas'] ?>">
                    <button type="submit">Ambil</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>

    <h3>KRS Saya</h3>
    <table border="1" cellpadding="6">
        <tr><th>Mata Kuliah</th><th>SKS</th><th>Status</th></tr>
        <?php while ($row = mysqli_fetch_assoc($q2)) { ?>
        <tr>
            <td><?= $row['nama_mk'] ?></td>
            <td><?= $row['sks'] ?></td>
            <td><?= $row['status'] ?></td>
        </tr>
        <?php } ?>
    </table>

    <h3>KHS (Nilai yang Sudah Publish)</h3>
    <table border="1" cellpadding="6">
        <tr><th>Mata Kuliah</th><th>Angka</th><th>Huruf</th><th>Bobot</th></tr>
        <?php while ($row = mysqli_fetch_assoc($q3)) { ?>
        <tr>
            <td><?= $row['nama_mk'] ?></td>
            <td><?= $row['angka'] ?></td>
            <td><?= $row['huruf'] ?></td>
            <td><?= $row['bobot'] ?></td>
        </tr>
        <?php } ?>
    </table>
    <?php
}

// ---------------- DOSEN ----------------
elseif ($role == 'dosen') {
    // Cari NIDN dosen
    $q = mysqli_query($conn, "SELECT nidn FROM dosen WHERE id_user=" . $user['id']);
    $data = mysqli_fetch_assoc($q);
    $nidn = $data['nidn'];

    // Daftar KRS yang harus dinilai
    $query = "
        SELECT k.id_krs, m.nim, mk.nama AS nama_mk, mk.sks,
               n.angka, n.huruf, n.publish
        FROM krs k
        JOIN kelas kls      ON kls.id_kelas = k.id_kelas
        JOIN mahasiswa m    ON m.nim = k.nim
        JOIN matakuliah mk  ON mk.kode = kls.kode_mk
        LEFT JOIN nilai n   ON n.id_krs = k.id_krs
        WHERE kls.nidn = '$nidn' AND k.status = 'DISETUJUI'
    ";
    $hasil = mysqli_query($conn, $query);
    ?>
    <h3>Input Nilai</h3>
    <table border="1" cellpadding="6">
        <tr>
            <th>NIM</th>
            <th>Mata Kuliah</th>
            <th>SKS</th>
            <th>Nilai</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>
        <tr>
            <td><?= $row['nim'] ?></td>
            <td><?= $row['nama_mk'] ?></td>
            <td><?= $row['sks'] ?></td>
            <td>
                <?php
                if ($row['angka'] != null) {
                    echo $row['angka'] . " (" . $row['huruf'] . ")";
                } else {
                    echo "-";
                }
                ?>
            </td>
            <td>
                <?php
                if ($row['publish'] == 1) echo "Publish";
                else echo "Draft";
                ?>
            </td>
            <td>
                <form method="post">
                    <input type="hidden" name="id_krs" value="<?= $row['id_krs'] ?>">
                    <input type="number" name="angka" min="0" max="100" required style="width:60px">
                    <button name="draft" type="submit">Draft</button>
                    <button name="publish" type="submit">Publish</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php
}
?>

</body>
</html>