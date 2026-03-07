<?php
include_once 'connectdb.php';
session_start();

include_once "header.php";

if(isset($_POST['btnsave'])) {
    $category = $_POST['txtcategory'];
    $vat      = $_POST['txtvat'];
    if(empty($category)) {
        $_SESSION['status'] = "Category field is empty";
        $_SESSION['status_code'] = "warning";
    } else {
        $insert = $pdo->prepare("INSERT INTO tbl_category (category, vat) VALUES (:cat, :vat)");
        $insert->bindParam(':cat', $category);
        $insert->bindParam(':vat', $vat);
        if ($insert->execute()) {
            $_SESSION['status'] = "Category added into the database";
            $_SESSION['status_code'] = "success";
        } else {
            $_SESSION['status'] = "Error adding category into the database";
            $_SESSION['status_code'] = "error";
        }
    }
}

if(isset($_POST['btnupdate'])) {
    $category = $_POST['txtcategory'];
    $vat      = $_POST['txtvat'];
    $id       = $_POST['txtcatid'];
    if(empty($category)) {
        $_SESSION['status'] = "Category field is empty";
        $_SESSION['status_code'] = "warning";
    } else {
        $update = $pdo->prepare("UPDATE tbl_category SET category=:cat, vat=:vat WHERE catid=:id");
        $update->bindParam(':cat', $category);
        $update->bindParam(':vat', $vat);
        $update->bindParam(':id',  $id);
        if ($update->execute()) {
            $_SESSION['status'] = "Category updated successfully";
            $_SESSION['status_code'] = "success";
        } else {
            $_SESSION['status'] = "Category not updated successfully";
            $_SESSION['status_code'] = "error";
        }
    }
}

if(isset($_POST['btndelete'])) {
    $delete=$pdo->prepare("DELETE FROM tbl_category WHERE catid=:catid");
    $delete->bindParam(':catid', $_POST['btndelete']);
    if($delete->execute()) {
        $_SESSION['status'] = "Deleted successfully";
        $_SESSION['status_code'] = "success";
    } else {
        $_SESSION['status'] = "Deleted not successfully";
        $_SESSION['status_code'] = "error";
    }
}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Category</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">

                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h5 class="m-0">Category Form</h5>
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="row">
                            <?php
                            $vatOptions = [0, 10, 20, 30, 40, 50];
                            if(isset($_POST['btnedit'])) {
                                $select=$pdo->prepare("SELECT * FROM tbl_category WHERE catid = :catid");
                                $select->bindParam(':catid', $_POST['btnedit']);
                                $select->execute();
                                if($select) {
                                    $row=$select->fetch(PDO::FETCH_OBJ);
                                    $vatSel = '';
                                    foreach($vatOptions as $v) {
                                        $sel = ((int)$row->vat == $v) ? 'selected' : '';
                                        $vatSel .= '<option value="'.$v.'" '.$sel.'>'.$v.'%</option>';
                                    }
                                    echo '<div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Category</label>
                                            <input type="hidden" class="form-control" value="'.$row->catid.'" name="txtcatid">
                                            <input type="text" class="form-control" placeholder="Enter Category" value="'.$row->category.'" name="txtcategory">
                                        </div>
                                        <div class="form-group">
                                            <label>VAT Rate</label>
                                            <select class="form-control" name="txtvat">
                                                '.$vatSel.'
                                            </select>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-info" name="btnupdate">Update</button>
                                        </div>
                                    </div>';
                                }
                            } else {
                                $vatOpts = '';
                                foreach($vatOptions as $v) {
                                    $vatOpts .= '<option value="'.$v.'">'.$v.'%</option>';
                                }
                                echo '<div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Category</label>
                                            <input type="text" class="form-control" placeholder="Enter Category" name="txtcategory">
                                        </div>
                                        <div class="form-group">
                                            <label>VAT Rate</label>
                                            <select class="form-control" name="txtvat">
                                                '.$vatOpts.'
                                            </select>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-warning" name="btnsave">Save</button>
                                        </div>
                                    </div>';
                            }
                            ?>
                            <div class="col-md-8">
                                <table id="table_category" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <td>#</td>
                                            <td>Category</td>
                                            <td>VAT Rate</td>
                                            <td>Edit</td>
                                            <td>Delete</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $select = $pdo->prepare("SELECT * FROM tbl_category ORDER BY catid ASC");  
                                        $select->execute();
                                        while($row=$select->fetch(PDO::FETCH_OBJ)) {
                                            echo '
                                                <tr>
                                                    <td>'.$row->catid.'</td>
                                                    <td>'.$row->category.'</td>
                                                    <td><span class="badge badge-info">'.(int)$row->vat.'%</span></td>
                                                    <td>
                                                        <button type="submit" class="btn btn-primary btn-edit" value="'.$row->catid.'" name="btnedit">Edit</button>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-delete" value="'.$row->catid.'" name="btndelete">Delete</button>
                                                    </td>
                                                </tr>';
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>#</td>
                                            <td>Category</td>
                                            <td>VAT Rate</td>
                                            <td>Edit</td>
                                            <td>Delete</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
include_once "footer.php";
if(isset($_SESSION['status']) && $_SESSION['status']!='') {
    echo "<script>
        Swal.fire({
            icon: '".$_SESSION['status_code']."',
            title: '".$_SESSION['status']."'
        });
    </script>";
    unset($_SESSION['status']);
}
?>
<script>
    $(document).ready(function() {
        $('.btn-delete').click(function() {
            var catId = $(this).val();
            Swal.fire({
                title: 'Are you sure you want to delete this item?',
                text: 'You will not be able to recover this item',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d63032',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Deleted Now!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('form').append('<input type="hidden" name="btndelete" value="'+catId+'">').submit();
                }
            });
        });
    });
</script>
<script>
    $(document).ready( function () {
        $('#table_category').DataTable();
    });
</script>
