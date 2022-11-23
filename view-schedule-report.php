
<section class="content-header">
  <h1>VIEW SCHEDULE</h1>
</section>
<section class="content">
  <div class="box">
    <div class="box-body table-responsive">
      <table id="example1" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Report Name</th>
                <th>Created By</th>
                <th>Report Type</th>
                <th>Schedule Date</th>
                <th>Interval</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        record_set('template', 'select * from schedule_report_new where id !="" order by id desc');
        while($rpt = mysqli_fetch_assoc($template)){ 
            $filter = json_decode($rpt['filter'],1);
        ?>
            <tr>
              <td><?=$i?></td>
              <td><?=$rpt['temp_name']?></td>
              <td><?=get_user_datails($rpt['cby'])['name']?></td>
              <td><?=ucfirst($filter['data_type_hidden'])?></td>
              <td><?=$rpt['schedule_date']?></td>
              <td><?=($rpt['intervals']/24)?$rpt['intervals']/'24'.' days': '' ?></td>
            </tr> 
        <?php
        $i++;
          }
        ?>    
        </tbody>
        
      </table>
    </div>
  </div>
</section>
