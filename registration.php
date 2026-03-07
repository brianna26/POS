<?php
include_once 'connectdb.php';
session_start();

// Check if user is Admin
$isAdmin = isset($_SESSION['useremail']) && $_SESSION['role'] === "Admin";

/* DELETE (only Admins) */
if($isAdmin && isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    $delete = $pdo->prepare("DELETE FROM tbl_user WHERE userid=:id");
    $delete->bindParam(':id', $delete_id);

    if($delete->execute()){
        $_SESSION['status'] = [
            'type' => 'success',
            'title' => 'Deleted!',
            'message' => 'User deleted successfully'
        ];
        header("Location: registration.php");
        exit;
    }
}

/* INSERT / UPDATE */
if(isset($_POST['btnsave'])){

    $username     = $_POST['txtname'];
    $useremail    = $_POST['txtemail'];
    $userpassword = $_POST['txtpassword'];
    $useraddress  = $_POST['txtaddress'];
    $userage      = $_POST['txtage'];
    $usercontact  = $_POST['txtcontact'];
    $role         = $_POST['txtrole'];

    $imgName = null;

    if(isset($_FILES['txtimage']) && $_FILES['txtimage']['error'] == 0){
        $imgTmp  = $_FILES['txtimage']['tmp_name'];
        $imgName = time().'_'.basename($_FILES['txtimage']['name']);
        $imgPath = "uploads/".$imgName;

        if(!is_dir("uploads")){
            mkdir("uploads", 0777, true);
        }
        move_uploaded_file($imgTmp, $imgPath);
    }

    if(isset($_POST['userid']) && !empty($_POST['userid'])){
        // Only Admins can update users
        if(!$isAdmin){
            $_SESSION['status'] = [
                'type' => 'error',
                'title' => 'Access Denied',
                'message' => 'You are not allowed to edit users'
            ];
            header("Location: registration.php");
            exit;
        }

        if($imgName != null){
            $update = $pdo->prepare("UPDATE tbl_user SET 
                username=:name,useremail=:email,userpassword=:password,
                useraddress=:address,userage=:age,usercontact=:contact,
                role=:role,userimage=:image WHERE userid=:id");
            $update->bindParam(':image',$imgName);
        } else {
            $update = $pdo->prepare("UPDATE tbl_user SET 
                username=:name,useremail=:email,userpassword=:password,
                useraddress=:address,userage=:age,usercontact=:contact,
                role=:role WHERE userid=:id");
        }

        $update->bindParam(':name',$username);
        $update->bindParam(':email',$useremail);
        $update->bindParam(':password',$userpassword);
        $update->bindParam(':address',$useraddress);
        $update->bindParam(':age',$userage);
        $update->bindParam(':contact',$usercontact);
        $update->bindParam(':role',$role);
        $update->bindParam(':id',$_POST['userid']);

        if($update->execute()){
            $_SESSION['status'] = [
                'type' => 'success',
                'title' => 'Updated!',
                'message' => 'User updated successfully'
            ];
            header("Location: registration.php");
            exit;
        }

    } else {
        // Anyone can register
        $check = $pdo->prepare("SELECT * FROM tbl_user WHERE useremail=:email");
        $check->bindParam(':email', $useremail);
        $check->execute();

        if($check->rowCount() > 0){
            $_SESSION['status'] = [
                'type' => 'error',
                'title' => 'Oops!',
                'message' => 'This email is already registered'
            ];
            header("Location: registration.php");
            exit;
        } else {
            if($imgName == null){ $imgName = "default.png"; }

            $insert = $pdo->prepare("INSERT INTO tbl_user 
            (username,useremail,userpassword,useraddress,userage,usercontact,role,userimage) 
            VALUES (:name,:email,:password,:address,:age,:contact,:role,:image)");

            $insert->bindParam(':name',$username);
            $insert->bindParam(':email',$useremail);
            $insert->bindParam(':password',$userpassword);
            $insert->bindParam(':address',$useraddress);
            $insert->bindParam(':age',$userage);
            $insert->bindParam(':contact',$usercontact);
            $insert->bindParam(':role',$role);
            $insert->bindParam(':image',$imgName);

            if($insert->execute()){
                $_SESSION['status'] = [
                    'type' => 'success',
                    'title' => 'Registered!',
                    'message' => 'User registered successfully'
                ];
                header("Location: registration.php");
                exit;
            }
        }
    }
}

include_once "header.php";
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">User Registration</h1>
                    <hr>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="fas fa-users mr-2"></i>User List</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addUserModal">
                        <i class="fas fa-user-plus mr-1"></i> Register New User
                    </button>
                </div>
                <div class="card-body">
                    <?php if($isAdmin): ?>
                    <table id="table_users" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $stmt = $pdo->prepare("SELECT userid, username, useremail, role, userimage, useraddress, userage, usercontact, userpassword FROM tbl_user ORDER BY userid DESC");
                        $stmt->execute();
                        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach($users as $user):
                            $imagePath = !empty($user['userimage']) && file_exists("uploads/".$user['userimage'])
                                ? "uploads/".$user['userimage']
                                : "uploads/default.png";
                            $roleBadge = $user['role'] === 'Admin' ? 'badge-danger' : 'badge-info';
                        ?>
                        <tr>
                            <td><?php echo $user['userid']; ?></td>
                            <td>
                                <img src="<?php echo $imagePath; ?>" class="img-circle elevation-1" width="40" height="40" style="object-fit:cover;" alt="User Image">
                            </td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['useremail']); ?></td>
                            <td><span class="badge <?php echo $roleBadge; ?>"><?php echo $user['role']; ?></span></td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm btn-edit-user"
                                    data-id="<?php echo $user['userid']; ?>"
                                    data-name="<?php echo htmlspecialchars($user['username']); ?>"
                                    data-email="<?php echo htmlspecialchars($user['useremail']); ?>"
                                    data-password="<?php echo htmlspecialchars($user['userpassword']); ?>"
                                    data-address="<?php echo htmlspecialchars($user['useraddress']); ?>"
                                    data-age="<?php echo $user['userage']; ?>"
                                    data-contact="<?php echo htmlspecialchars($user['usercontact']); ?>"
                                    data-role="<?php echo $user['role']; ?>"
                                    data-image="<?php echo $imagePath; ?>"
                                    data-toggle="modal" data-target="#editUserModal">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-delete-user" data-id="<?php echo $user['userid']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-lock mr-2"></i> Only administrators can view the user list.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================== ADD USER MODAL ===================== -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-user-plus mr-2"></i>Register New User
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Full Name</label>
                            <input type="text" class="form-control" name="txtname" placeholder="Full name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email Address</label>
                            <input type="email" class="form-control" name="txtemail" placeholder="Email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Password</label>
                            <input type="password" class="form-control" name="txtpassword" placeholder="Password" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Address</label>
                            <input type="text" class="form-control" name="txtaddress" placeholder="Address" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Age</label>
                            <input type="number" class="form-control" name="txtage" placeholder="Age" min="1" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Contact Number</label>
                            <input type="text" class="form-control" name="txtcontact" placeholder="Contact no." required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Role</label>
                            <select class="form-control" name="txtrole" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="User">User</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Profile Image <small class="text-muted">(optional)</small></label>
                        <input type="file" class="form-control-file" name="txtimage" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="btnsave">
                        <i class="fas fa-save mr-1"></i>Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== EDIT USER MODAL ===================== -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-user-edit mr-2"></i>Edit User
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="userid" id="edit_userid">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Full Name</label>
                            <input type="text" class="form-control" name="txtname" id="edit_name" placeholder="Full name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email Address</label>
                            <input type="email" class="form-control" name="txtemail" id="edit_email" placeholder="Email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Password</label>
                            <input type="password" class="form-control" name="txtpassword" id="edit_password" placeholder="Password" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Address</label>
                            <input type="text" class="form-control" name="txtaddress" id="edit_address" placeholder="Address" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Age</label>
                            <input type="number" class="form-control" name="txtage" id="edit_age" placeholder="Age" min="1" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Contact Number</label>
                            <input type="text" class="form-control" name="txtcontact" id="edit_contact" placeholder="Contact no." required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Role</label>
                            <select class="form-control" name="txtrole" id="edit_role" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="User">User</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Profile Image <small class="text-muted">(leave blank to keep current)</small></label>
                        <div class="d-flex align-items-center">
                            <img id="edit_img_preview" src="" class="img-circle mr-3" width="45" height="45" style="object-fit:cover; border:2px solid #ddd;" alt="Current">
                            <input type="file" class="form-control-file" name="txtimage" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" name="btnsave">
                        <i class="fas fa-save mr-1"></i>Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // Fill edit modal
    $('.btn-edit-user').on('click', function() {
        $('#edit_userid').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_email').val($(this).data('email'));
        $('#edit_password').val($(this).data('password'));
        $('#edit_address').val($(this).data('address'));
        $('#edit_age').val($(this).data('age'));
        $('#edit_contact').val($(this).data('contact'));
        $('#edit_role').val($(this).data('role'));
        $('#edit_img_preview').attr('src', $(this).data('image'));
    });

    // Delete confirmation
    $('.btn-delete-user').on('click', function() {
        var userId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This user will be permanently deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'registration.php?delete=' + userId;
            }
        });
    });

});

// SweetAlert flash messages
<?php if(isset($_SESSION['status'])): ?>
Swal.fire({
    icon: '<?php echo $_SESSION['status']['type']; ?>',
    title: '<?php echo $_SESSION['status']['title']; ?>',
    text: '<?php echo $_SESSION['status']['message']; ?>',
    showConfirmButton: true,
    confirmButtonColor: '<?php echo $_SESSION['status']['type']=="success"?"#28a745":"#dc3545"; ?>',
    timer: 1500,
    timerProgressBar: true
});
<?php unset($_SESSION['status']); endif; ?>
</script>
