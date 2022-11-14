
<?php
  if(isset($_REQUEST['bupload'])){
    upload_excel();
  }
?>
<section class="content-header">
  <h1> IMPORT USERS</h1>
</section>
<section class="content">
  <!-- Start Department Table-->
  <div class="box box-secondary">
    <div class="row">
      <div class="col-md-12">
        <div class="box-body">
            <form action="" method="post" enctype="multipart/form-data" name="myForm">
              <div class="form-group">
                <label>Upload CSV File</label>
                <input type="file" class="form-control" name="tfile" id="title">
              </div>
              <div class="form-group">
                <button class="btn btn-primary pull-right" name="bupload">Upload</button>
                <a class="label label-info" href='sample_csv/user.xlsx' download>Download sample</a>
              </div>
            </form>
        </div>
      </div>
    </div>
  </div>
  <!-- End Department Table-->
</section>


