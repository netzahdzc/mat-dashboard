<?php
include_once('../../sqlite/class.mysqli.php');  

// Open database
$db = new MySQL("fiware_matest");
    
// Read data
$consulta = $db->consulta("SELECT * FROM participants WHERE id LIKE ".$_GET["participant_id"]."");

$files = array();
while($row=$db->fetch_array($consulta)){
  $patient_name = utf8_encode($row['name'])." ".utf8_encode($row['surname']);
}

$db->close();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>oHealth-Context | Dashboard</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../../dist/css/main.min.css" rel="stylesheet" type="text/css" />
    <!-- oHealth-Context Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="../../dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="skin-blue">
    <div class="wrapper">
      <header class="main-header">
        <!-- Logo -->
        <a href="#" style="cursor:default" class="logo"><b>oHealth</b>-Context</a> 
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <!-- Sidebar toggle button-->
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="../../dist/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                  <span class="hidden-xs">Username</span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="../../dist/img/user2-160x160.jpg" class="img-circle" alt="User Image" />
                    <p>
                      Username - Temporal role
                      <small>Member since September. 2017</small>
                    </p>
                  </li>
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="../profile/index.html" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="../../index.html" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- Sidebar user panel -->
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            <li class="header">MAIN NAVIGATION</li>
            <li class="treeview">
              <a href="../dashboard/index.html">
                <i class="fa fa-dashboard"></i> <span>Dashboard</span> 
              </a>
            </li>
            <li class="treeview">
              <a href="../process/index.php">
                <i class="fa fa-gear"></i>
                <span>Process</span>
              </a>
            </li>
            <li class="active treeview">
              <a href="./index.php">
                <i class="fa fa-table"></i> <span>Collected data</span>
              </a>
            </li>
          </ul>
          <ul class="sidebar-menu">
            <li class="header">OTHER</li>
            <li class="treeview">
              <a href="../others/maintenance.php">
                <i class="fa fa-wrench"></i> <span>Maintenance</span> 
              </a>
            </li>
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            <a href="index.php"> << <?php echo "[".$_GET["participant_id"]."] ".$patient_name; ?> </a>
          </h1>
          <ol class="breadcrumb">
            <li><a href="../dashboard/index.html"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Collected data</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Performed test</h3>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">

                  <?php
                  //Open database
                  $db = new MySQL("fiware_matest");
                      
                  //Reading data
                  $consulta = $db->consulta("SELECT * FROM tests WHERE participant_id LIKE ".$_GET["participant_id"]."");

                  $files = array();
                  echo "<table class='table table-hover'>
                    <tr>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>ID</font></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>TEST TYPE</font></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>DATE</font></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q1</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(No alteración de marcha)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q2</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Ritmo lento)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q3</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Pérdida balance)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q4</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Gira en su punto)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q5</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Balance de brazos)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q6</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Estabilización con paredes)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q7</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Arrastra pies)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q8</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Pasos cortos)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>Q9</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(No usa dispositivo correctamente)</span></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>EVALUATION</br></font></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>DESCRIPTION</br></font></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>STATUS</br></font></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>PARAMETERS</font></th>
                      <th style='text-align:center !important' bgcolor='#5D7B9D'><font color='#fff'>SIGNAL</font></th>
                    </tr>";

                  $i = 0;
                  while($row=$db->fetch_array($consulta)){
                    $testData = getTestType($row["type_test"], $row["test_option"]);

                      if($i%2) $bgcolor = 'dee4eb'; else $bgcolor = 'fff';
                      if($row["status"]!="testCompleted") $fontcolor = 'c7cdd3'; else $fontcolor = '000';

                    echo "
                      <tr bgcolor='#".$bgcolor."'>
                          <td align='left'><font color='#".$fontcolor."'>P".$row["participant_id"]."T".$row["id"]."</font></td>
                          <td align='left'><font color='#".$fontcolor."'>".$testData[0]." (".$testData[1].")</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".date('d-m-Y H:i:s', strtotime($row["beginning_sensor_collection_timestamp"]))."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q1"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q2"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q3"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q4"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q5"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q6"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q7"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q8"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["q9"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["data_evaluation_score"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["data_evaluation_description"]."</font></td>
                          <td align='center'><font color='#".$fontcolor."'>".$row["status"]."</font></td>
                      ";

                      //********************************************************************
                      //********************************************************************
                      $sensibility_tug = 1.9;
                      $sensibility_strength = 2.0;
                      $sensibility = array($sensibility_tug, $sensibility_strength);
                      //********************************************************************
                      //********************************************************************
                      
                      if($row["status"]=="testCompleted"){

                          if($row["type_test"] == 1)
                              $duration = "&duracion=".date('s', (strtotime($row["finishing_sensor_collection_timestamp"]) - strtotime($row["beginning_sensor_collection_timestamp"])) );
                          else
                              $duration = "";

                          echo "<td align='center'><font color='#".$fontcolor."'><a target='_self' href='./eventsDetector.php?n=".$patient_name."&participant_id=".$row["participant_id"]."&test_id=".$row["test_id"]."&type_test=".$row["type_test"]."&test_option=".$row["test_option"].$duration."'>Ver</a></font></td>";
                          
                          if($row["type_test"]=="1" || $row["type_test"]=="2"){
                              echo "<td align='center'><font color='#".$fontcolor."'><a target='_self' href='./print_data.php?n=".$patient_name."&participant_id=".$row["participant_id"]."&fname=pre_cleaning_".$row["participant_id"]."@T".$row["test_id"]."Acc&type=".getTestTypeId($row["type_test"])."&dir=acc'>pre</a>&nbsp;&nbsp;";
                              echo "<a target='_self' href='./print_data.php?n=".$patient_name."&participant_id=".$row["participant_id"]."&fname=post_cleaning_".$row["participant_id"]."@T".$row["test_id"]."Acc&type=".getTestTypeId($row["type_test"])."&dir=acc&sensibility=".getSensibility($row["type_test"],$sensibility)."'>post</a></font></td>";
                          }else{
                              echo "<td align='center'><font color='#".$fontcolor."'>---</font></td>";
                          }

                          echo "</tr>";
                      }else{
                          echo "<td align='center'><font color='#".$fontcolor."'>---</font></td>";
                          echo "<td align='center'><font color='#".$fontcolor."'>---</font></td>";
                          echo "</tr>";
                      }

                      $i++;
                  }

                  echo "</table>";

                  $db->close();
                  ?>
                  
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b>Draft version</b> 0.1
        </div>
        <strong>Copyright &copy; 2016-2018 <a href="https://www.smartsdk.eu">FIWARE's SmartSDK project</a>.</strong> All rights reserved.
      </footer>
    </div><!-- ./wrapper -->
    <!-- jQuery 2.1.3 -->
    <script src="../../plugins/jQuery/jQuery-2.1.3.min.js"></script>
    <!-- jQuery UI 1.11.2 -->
    <script src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="../../bootstrap/js/bootstrap.min.js" type="text/javascript"></script>    
    <!-- Sparkline -->
    <script src="../../plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
    <!-- jvectormap -->
    <script src="../../plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
    <script src="../../plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
    <!-- daterangepicker -->
    <script src="../../plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <!-- datepicker -->
    <script src="../../plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="../../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
    <!-- iCheck -->
    <script src="../../plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    <!-- Slimscroll -->
    <script src="../../plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='../../plugins/fastclick/fastclick.min.js'></script>
    <!-- oHealth-Context App -->
    <script src="../../dist/js/app.min.js" type="text/javascript"></script>
  </body>
</html>

<?php

function getTestType($type_test, $test_option){
  $outcome = "";
  $outcome2 = "N/A";

  if($type_test==1) $outcome = "Timed Up and Go";
  if($type_test==2) $outcome = "Strength";
  if($type_test==3) {
    $outcome = "Balance";

    if($test_option==1) $outcome2 = "Tandem";
    if($test_option==2) $outcome2 = "Semi-tandem";
    if($test_option==3) $outcome2 = "Feet together";
    if($test_option==4) $outcome2 = "One leg";
  }

  return [$outcome, $outcome2];
}

function getTestTypeId($type_test){
    $outcome = "";

    if($type_test==1)   $outcome = "tug";
    if($type_test==2)   $outcome = "strenght";

    return $outcome;
}

function getSensibility($type_test, $sensibility){
    $outcome = 0;

    if($type_test==1) $outcome = $sensibility[0];
    if($type_test==2) $outcome = $sensibility[1];

    return $outcome;
}

?>