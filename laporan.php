<?php
require 'koneksi.php';
harus_login();

// Laporan 1: Rekap peserta per jadwal (pakai function)
$q1 = mysqli_query($conn, "
    SELECT j.id_jadwal, mk.nama_mk, j.ruang, j.kuota,
           fn_jumlah_peserta(j.id_jadwal) AS peserta
    FROM jadwal j
    JOIN mata_kuliah mk ON mk.kode_mk = j.kode_mk
");

// Laporan 2: Mahasiswa per prodi
$q2 = mysqli_query($conn, "
    SELECT prodi, COUNT(*) AS jumlah
    FROM mahasiswa GROUP BY prodi
");
?>
<h3>Laporan</h3>
<h4>Peserta per Jadwal</h4>
<table border="1" cellpadding="6">
<tr><th>Jadwal</th><th>Mata Kuliah</th><th>Ruang</th><th>Peserta</th><th>Kuota</th></tr>
<?php while ($r = mysqli_fetch_assoc($q1)) { ?>
<tr>
  <td><?= $r['id_jadwal'] ?></td>
  <td><?= $r['nama_mk'] ?></td>
  <td><?= $r['ruang'] ?></td>
  <td><?= $r['peserta'] ?></td>
  <td><?= $r['kuota'] ?></td>
</tr>
<?php } ?>
</table>

<h4>Mahasiswa per Prodi</h4>
<table border="1" cellpadding="6">
<tr><th>Prodi</th><th>Jumlah</th></tr>
<?php while ($r = mysqli_fetch_assoc($q2)) { ?>
<tr><td><?= $r['prodi'] ?></td><td><?= $r['jumlah'] ?></td></tr>
<?php } ?>
</table>