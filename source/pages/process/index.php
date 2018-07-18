<?php 
$output = "Maybe an error just happened, please contact the development team.";

if(!empty($_GET)){
  if($_GET["q"]=="clean"){
    $cleanFlag = true;
    $command = escapeshellcmd('python ../../sqlite/clean_db_files.py');
    // $command = escapeshellcmd('python /var/www/html/sqlite/clean_db_files.py');
  }
  if($_GET["q"]=="extract"){
    $extractFlag = true;
    $command = escapeshellcmd('python ../../sqlite/files_handler.py');
    // $command = escapeshellcmd('python /var/www/html/sqlite/files_handler.py');
  }
  if($_GET["q"]=="build"){
    $buildFlag = true;
    include_once('../../sqlite/class.mysqli.php'); 
    // Open database
    $db = new MySQL("fiware_matest");
        
    // Read data
    $consulta = $db->consulta("SELECT * FROM tests");

    // Create files
    while($row = $db->fetch_array($consulta)){
      $queryAcc = "SELECT  `x`, `y`, `z`, UNIX_TIMESTAMP(`created`) AS `created` FROM `sensor_linear_acceleration` WHERE `patient_id` LIKE '".$row['patient_id']."' AND `test_id` LIKE '".$row['test_id']."' INTO OUTFILE '/var/www/html/sqlite/parsed_files/acc/".$row['patient_id']."@T".$row['test_id']."Acc.txt'; ";
      $db->consulta($queryAcc);

      $queryOri = "SELECT  `azimuth`, `pitch`, `roll`, UNIX_TIMESTAMP(`created`) AS `created` FROM `sensor_orientation` WHERE `patient_id` LIKE '".$row['patient_id']."' AND `test_id` LIKE '".$row['test_id']."' INTO OUTFILE '/var/www/html/sqlite/parsed_files/orient/".$row['patient_id']."@T".$row['test_id']."Ori.txt'; ";
      $db->consulta($queryOri);
    }

    $db->close();

    $output = "</br>Process finished.</br></br>Continue by consulting section: Collected data.";
  }
}
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
            <li class="active treeview">
              <a href="./index.php">
                <i class="fa fa-gear"></i>
                <span>Process</span>
              </a>
            </li>
            <li class="treeview">
              <a href="../tables/index.php">
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
            Procedure to prepare collected data
            <small>(3 steps mechanism)</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="../dashboard/index.html"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Process</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- row -->
          <div class="row">
            <div class="col-md-12">
              <!-- The time line -->
              <ul class="timeline">
                <!-- timeline time label -->
                <li class="time-label">
                  <span class="bg-blue">
                    Beginning
                  </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                  <i class="fa bg-blue">1</i>
                  <div class="timeline-item">
                    <h3 class="timeline-header"><a href="#" style="cursor:default">Clean database & prepare database</a></h3>
                    <?php if($cleanFlag){ $output = shell_exec($command); ?>
                    <div class="timeline-body">
                      <?php echo $output; ?>
                    </div>
                    <div class='timeline-footer'>
                      <?php if(strpos($output, 'finished')){?> 
                      <a class="btn bg-blue btn-xs" href="#" disabled>Start</a>
                      <?php }else{ ?>
                      <a class="btn bg-blue btn-xs" href="index.php?q=clean"><i class="fa fa-fw fa-rotate-right"></i> Repeat</a>
                      <?php } ?>
                    </div>
                    <?php } ?>
                    <?php if(!$cleanFlag){ ?>
                    <div class="timeline-body">
                      This step will clean the database to retrieve only the newest data.
                    </div>
                    <div class='timeline-footer'>
                      <a class="btn bg-blue btn-xs" href="index.php?q=clean">Start</a>
                    </div>
                    <?php } ?>
                  </div>
                </li>
                <!-- END timeline item -->
                <!-- timeline item -->
                <li>
                  <i class="fa bg-aqua">2</i>
                  <div class="timeline-item">
                    <h3 class="timeline-header"><a href="#">Extract files</a></h3>
                    <?php if($extractFlag){ $output = shell_exec($command); ?>
                    <div class="timeline-body">
                      <?php echo $output; ?>
                    </div>
                    <div class='timeline-footer'>
                      <a class="btn bg-aqua btn-xs" href="#" disabled>Start</a>
                    </div>
                    <?php } ?>
                    <?php if(!$extractFlag){ ?>
                    <div class="timeline-body">
                      File retrieve from the repository side.
                    </div>
                    <div class='timeline-footer'>
                      <a class="btn bg-aqua btn-xs" href="index.php?q=extract">Start</a>
                    </div>
                    <?php } ?>
                  </div>
                </li>
                <!-- END timeline item -->
                <!-- timeline item -->
                <li>
                  <i class="fa bg-yellow">3</i>
                  <div class="timeline-item">
                    <h3 class="timeline-header"><a href="#">File creation</a></h3>
                    <?php if($buildFlag){ ?>
                    <div class="timeline-body">
                      <?php echo $output; ?>
                    </div>
                    <div class='timeline-footer'>
                      <a class="btn btn-warning btn-flat btn-xs" href="#" disabled>Start</a>
                    </div>
                    <?php } ?>
                    <?php if(!$buildFlag){ ?>
                    <div class="timeline-body">
                      Temporal files will be created to facilitate the graphical visualization.
                    </div>
                    <div class='timeline-footer'>
                      <a class="btn btn-warning btn-flat btn-xs" href="index.php?q=build">Start</a>
                    </div>
                    <?php } ?>
                  </div>
                </li>
                <!-- END timeline item -->
                <li>
                  <i class="fa fa-flag-o bg-gray"></i>
                </li>
              </ul>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b>Draft version</b> 0.1
        </div>
        <strong>Copyright &copy; 2016-2018 <a href="https://www.smartsdk.eu">FIWARE's SmartSDK project</a>.</strong> All rights reserved.
      </footer>
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