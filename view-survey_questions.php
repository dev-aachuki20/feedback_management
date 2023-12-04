
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.min.js"></script>
<?php
//dbRowDelete('questions_detail', $filter);
if (isset($_GET['deleted'])) {
  $questionid = $_GET['deleted'];
  $order = $_GET['order'];

  record_set("get_order_question", "select * from conditional_logic_questions where skip_to_question_id=$order");
  if($totalRows_get_order_question > 0){
    if($_GET['status'] ==1){
      // record_set("get_questions", "select * from questions where surveyid= ".$_REQUEST['surveyid']." order by id asc");
      // $i=1;
      // while ($row_get_questions = mysqli_fetch_assoc($get_questions)) {
      //      $data =  array(
      //         "order_no" =>  $i,
      //       );
      //     $updte =  dbRowUpdate("questions", $data, "where id=" . $row_get_questions['id'] . " and surveyid=" . $_REQUEST['surveyid']);
      //     $i++;
      // }
      $filter = " questionid in($questionid)";
      $dWhere = " skip_to_question_id in ($order)";
      dbRowDelete('conditional_logic_questions', $dWhere);
      dbRowDelete('questions_detail', $filter);
      dbRowDelete('questions', " id=$questionid");
      $msg = "Question deleted Successfully";
      reDirect("?page=view-survey_questions&surveyid=" . $_REQUEST['surveyid'] . "&msg=" . $msg);
    }
    confirmBoxForConditionalQuestion();
    die();
  }
  $filter = " questionid in($questionid)";
  dbRowDelete('conditional_logic_questions', $filter);
  dbRowDelete('questions_detail', $filter);
  dbRowDelete('questions', " id=$questionid");
  $msg = "Question deleted Successfully";
  reDirect("?page=view-survey_questions&surveyid=" . $_REQUEST['surveyid'] . "&msg=" . $msg);
}
?>

<section class="content-header">
  <h1>SURVEY QUESTIONS</h1>
  <a href="?page=add-survey_questions&surveyid=<?php echo $_REQUEST['surveyid']; ?>" class="btn btn-success pull-right bg-success" style="margin-top:-25px">Add Question</a>
  <a href="?page=view-survey" class="btn btn-primary pull-right" style="margin-top:-25px">View Surveys</a>
</section>
<section class="content">
  <div class="box box-solid">
    <?php
    record_set("get_surveys", "select * from surveys where id='" . $_REQUEST['surveyid'] . "'");
    $row_get_surveys = mysqli_fetch_assoc($get_surveys);
    $filter = "";
    if ($row_get_surveys['departments']) {
      $filter = "where id IN (" . $row_get_surveys['departments'] . ")";
    }
    record_set("dname", "select * from departments $filter");
    $allDepartment = array();
    while ($row_dname = mysqli_fetch_assoc($dname)) {
      $allDepartment[] = $row_dname['name'];
    }
    ?>
    <div class="box-header with-border">
      <?php
      if (isset($_GET['msg'])) {
      ?>
        <div class="alert alert-success" role="alert">
          <?php echo $_GET['msg']; ?>
        </div>
      <?php
      }
      ?>
      <i class="fa fa-text-width"></i>

      <h3 class="box-title">SURVEY NAME - <?php echo $row_get_surveys['name'] ?></h3>
    </div>
    <div class="box-body">
      <p><strong>Client Department</strong> - <?php foreach ($allDepartment as $dept) { ?>
          <label for="" class="btn btn-xs btn-info" style=" cursor: unset;"> <?= $dept ?></label>
        <?php } ?>
      </p>
    </div>
  </div>
  <div class="box">
    <div class="box-body">
      <table id="common-tabless" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Questions</th>
            <th>Questions No.</th>
            <th>Type</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $filter = '';
          if ($_SESSION['user_type'] > 2) {
            $filter = " and cby='" . $_SESSION['user_id'] . "'";
          }
          record_set("get_questions", "select * from questions where surveyid=" . $_REQUEST['surveyid'] .  $filter." order by id desc");
          while ($row_get_questions = mysqli_fetch_assoc($get_questions)) {
            $label = '';
            $ordNumber = $row_get_questions['order_no'];
            record_set("get_questions_dependent", "select * from conditional_logic_questions where skip_to_question_id = $ordNumber");

            if($totalRows_get_questions_dependent > 0){
              $label = '<span class="label label-success">D</span>';
            }

            if ($row_get_questions['conditional_logic'] > 0) {
              $label = '<span class="label label-primary">C</span>';
            }
            record_set("get_questions_conditional_detail", "select * from questions_detail where   surveyid='" . $_REQUEST['surveyid'] . "'  and skip_to_question_id ='" . $row_get_questions['id'] . "'");
            if($totalRows_get_questions_conditional_detail > 0){
              while ($row_get_questions_conditional_detail = mysqli_fetch_assoc($get_questions_conditional_detail)) {
                if ($row_get_questions_conditional_detail['skip_to_question_id'] == $row_get_questions['id']) {
                    $label = '<span class="label label-success">D</span>';
                }
              }
            }
          ?>
            <tr>
              <td>
                <?php if (empty($row_get_questions['parendit'])) { ?><strong><?php } ?><?php echo $row_get_questions['question']; ?><?php if (empty($row_get_questions['parendit'])) { ?></strong> <?php }
                echo $label; ?> <?= ($row_get_questions['ifrequired'] == 1) ? '' : '<strong>(Optional)</strong>'?>
                </td>
              <td><?php echo $row_get_questions['order_no']; ?></td>
              <td><?php echo question_type_name($row_get_questions['answer_type']); ?></td>
              <td><?= ($row_get_questions['cstatus'] == 1) ? '<span class="label label-success">' . status_data($row_get_questions['cstatus']) . '</span>' : '<span class="label label-danger">' . status_data($row_get_questions['cstatus']) . '</span>' ?></td>
              <td>
                <a class="btn btn-xs btn-info" href="?page=edit-survey_questions&surveyid=<?php echo $_REQUEST['surveyid']; ?>&questionid=<?php echo $row_get_questions['id']; ?>">Edit</a>
                <?php if ($_SESSION['user_type'] == 1) { ?>
                  <!-- <a class="btn btn-xs btn-danger" href="?page=view-survey_questions&surveyid=<?php echo $_REQUEST['surveyid']; ?>&deleted=<?= $row_get_questions['id'] ?>">Delete</a> -->
                  <a class="btn btn-xs btn-danger" href="javascript:void(0)" onclick="sweetAlertConfirmBox(<?=$row_get_questions['id']?>, <?=$row_get_questions['order_no']?>)">Delete</a>
                <?php } ?>
              </td>
            </tr>
          <?php  } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>


   <?php function confirmBoxForConditionalQuestion(){ ?>
    <script>
      swal({
          title: 'Are you sure?',
          text: "This Question is related to the Conditional  logic, if you delete this conditional logic will not apply",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'
        },
        function(inputValue){
          console.log(inputValue,'inputValue');
          if(inputValue){
            console.log("Question deleted");
            window.location = "?page=view-survey_questions&surveyid=<?php echo $_REQUEST['surveyid']; ?>&deleted=<?=$_GET['deleted']?>&order=<?=$_GET['order']?>&status=1";
          }else{
            console.log("Question not deleted");
            window.location = "?page=view-survey_questions&surveyid=<?php echo $_REQUEST['surveyid']; ?>";
          }
      });

    </script>
  <?php  } ?>
<script>
  function sweetAlertConfirmBox(id,q_order){
    swal({
      title: "Are you sure want to delete this?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, delete it!"
    },function(inputValue){
          console.log(inputValue,'inputValue');
          if(inputValue){
            console.log("Question deleted");
            window.location = "?page=view-survey_questions&surveyid=<?php echo $_REQUEST['surveyid']; ?>&deleted="+id+"&order="+q_order;
          }
      });
  }
  $(document).ready( function() {
    $('#common-tabless').dataTable({
        "aaSorting": []
    });
})
</script>