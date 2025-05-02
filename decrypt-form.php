<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

?>

<?php
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-center align-items-center w-100 " style="height: 100vh">
    <div class="content" style="margin-top: 1rem; display: flex;"> <!--  ini buat css ya nanti taro di atas-->
        <div class="card" style="width: 40rem; display: flex; align-items: center;background: rgba(255,255,255,0.5)">
            <h1 style="padding:1rem; color:black;">Cek Hasil Ujian Psikotes</h1>
            <div class="card-body">
                <form action="decrypt-hasil.php" method="post" enctype="multipart/form-data">
                    <table class="table table-borderless">
                        <tr>
                            <td style="color:black;">Kata Sandi</td>
                            <td><input type="text" name="kunci" class="form-control"></td>
                        </tr>
                        <tr>
                            <td style="color:black;">Unggah Dokumen</td>
                            <td>
                                <input type="file" class="form-control-file" id="doc" name="doc">
                            </td>
                        </tr>
                    </table>
                    <button type="submit" class="btn btn-sm btn-primary">Cek Hasil</button>
                    <div>
                        <br>
                        <p style="color:black;">Kembali Kehalaman Login <a href="index.php" style="color:#ffd638;">Disini.</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';
?>