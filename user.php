<div class="container">
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-secondary mb-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg"></i> Tambah User
    </button>
    <div class="row">
        <div class="table-responsive" id="user_data">
            
        </div>

        <!-- Awal Modal Tambah-->
        <div class="modal fade" id="modalTambah" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Tambah User</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="formGroupExampleInput" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" placeholder="Tuliskan Username" required>
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput" class="form-label">Password</label>
                                <input type="text" class="form-control" name="password" placeholder="Tuliskan Password" required>
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput2" class="form-label">Foto</label>
                                <input type="file" class="form-control" name="foto">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <input type="submit" value="simpan" name="simpan" class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Akhir Modal Tambah-->
    </div>
</div>

<script>
$(document).ready(function(){
    load_data();
    function load_data(hlm){
        $.ajax({
            url : "user_data.php",
            method : "POST",
            data : {
					            hlm: hlm
				           },
            success : function(data){
                    $('#user_data').html(data);
            }
        })
    } 

    $(document).on('click', '.halaman', function(){
    var hlm = $(this).attr("id");
    load_data(hlm);
    });
});
</script>

<?php
include "upload_foto.php";

//jika tombol simpan diklik
if (isset($_POST['simpan'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $foto = '';
    $nama_foto = $_FILES['foto']['name'];

    if ($nama_foto != '') {
        $cek_upload = upload_foto($_FILES["foto"]);
        if ($cek_upload['status']) {
            $foto = $cek_upload['message'];
        } else {
            echo "<script>
                alert('" . $cek_upload['message'] . "');
                document.location='admin.php?page=user';
            </script>";
            die;
        }
    }

    // Cek apakah ada ID (update) atau tidak (insert)
    if (isset($_POST['id'])) {
        // Update data
        $id = $_POST['id'];
        if (empty($_POST['password'])) {
            $password = $_POST['password_lama'];
        }

        if ($nama_foto == '') {
            $foto = $_POST['foto_lama'];
        } else {
            unlink("img/" . $_POST['foto_lama']);
        }

        $stmt = $conn->prepare("UPDATE user SET username =?, password =?, foto =? WHERE id =?");
        $stmt->bind_param("sssi", $username, $password, $foto, $id);
        $simpan = $stmt->execute();
    } else {
        // Tambah data baru
        $stmt = $conn->prepare("INSERT INTO user (username, password, foto) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $foto);
        $simpan = $stmt->execute();
    }

    if ($simpan) {
        echo "<script>
            alert('Simpan data sukses');
            document.location='admin.php?page=user';
        </script>";
    } else {
        echo "<script>
            alert('Simpan data gagal: " . $stmt->error . "');
        </script>";
    }

    $stmt->close();
    $conn->close();
}

//jika tombol hapus diklik
if (isset($_POST['hapus'])) {
    $id = $_POST['id'];
    $gambar = $_POST['foto'];

    if ($foto != '') {
        //hapus file gambar
        unlink("img/" . $foto);
    }

    $stmt = $conn->prepare("DELETE FROM user WHERE id =?");

    $stmt->bind_param("i", $id);
    $hapus = $stmt->execute();

    if ($hapus) {
        echo "<script>
            alert('Hapus data sukses');
            document.location='admin.php?page=user';
        </script>";
    } else {
        echo "<script>
            alert('Hapus data gagal');
            document.location='admin.php?page=user';
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>