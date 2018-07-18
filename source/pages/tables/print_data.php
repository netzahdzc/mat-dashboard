<?php 
  $patient_name = "[".$_GET['patient_id']."] ".$_GET['n'];
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
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>

    <script type="text/javascript">
        function getParamFromURL(name)
        {  
            name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");  
            var regexS = "[\\?&]"+name+"=([^&#]*)";  
            var regex = new RegExp( regexS );  
            var results = regex.exec(window.location.href);
            if(results == null)
                return "";  
            else    
                return results[1];
        }

/////////////////////////////////
/////////////////////////////////

$(function() {

  if(getParamFromURL("type") == "tug" || getParamFromURL("type") == "strenght"){
    variable = 'Axis processed';
    ext = '_'+getParamFromURL("type");

    if(getParamFromURL("fname").indexOf("Ori") > -1 ){
      names = ['azimuth','pitch','roll'];
    }else{
      names = ['Signal'];
    }
  }

  if( getParamFromURL("type").indexOf("balance") > -1 ){
    variable = 'Axis Y processed';
    ext = '_'+getParamFromURL("type");
    names = ['azimuth','pitch','roll'];
  }
  
  // if(getParamFromURL("type") == "steps"){
  //  variable = 'Axis Y processed';
  //  ext = '_steps';
  // }

  // if(getParamFromURL("type") == "fft"){
  //  variable = 'FFT of axis Y';
  //  ext = '_fft';
  // }

  // if(getParamFromURL("type") == "hr"){
  //  variable = 'HR average for windows';
  //  ext = '';
  // }

  // if(getParamFromURL("type") == "rms"){
  //  variable = 'RMS';
  //  ext = '_rms';
  // }

  var seriesOptions = [],
    yAxisOptions = [],
    seriesCounter = 0,
    colors = Highcharts.getOptions().colors;

  $.each(names, function(i, name) {
    //alert('./parsed_files/'+getParamFromURL("dir")+'/'+getParamFromURL("fname")+ext+'.json');
    $.getJSON('../../sqlite/parsed_files/'+getParamFromURL("dir")+'/'+getParamFromURL("fname")+ext+'.json', function(data) {
      seriesOptions[i] = {
        name: name,
        data: data[i].data
      };
      //console.log(data[i].data);
      
      // As we're loading the data asynchronously, we don't know what order it will arrive. So
      // we keep a counter and create the chart when all the data is loaded.
      seriesCounter++;

      if (seriesCounter == names.length) {
        createChart();
        //alert(seriesOptions);
      }
    });
  });



  // create the chart when all data is loaded
  function createChart() {

    $('#container').highcharts('StockChart', {
      global: {
            useUTC: false
        },

        chart: {
        },

        rangeSelector: {
            selected: 4
        },

        yAxis: {
          labels: {
            formatter: function() {
              return (this.value > 0 ? '+' : '') + this.value + 'm/s2';
            }
          },
          plotLines: [{
                value: parseFloat(getParamFromURL("sensibility")),
                color: 'green',
                dashStyle: 'shortdash',
                width: 2,
                label: {
                    text: ''
                }
              }, {
                value: -parseFloat(getParamFromURL("sensibility")),
                color: 'red',
                dashStyle: 'shortdash',
                width: 2,
                label: {
                    text: ''
                }
              }]
        },
        
        /*plotOptions: {
          series: {
            compare: 'percent'
          }
        },*/
        
        tooltip: {
          //pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%)<br/>',
          valueDecimals: 3
        },
        
        series: seriesOptions
    });
  }

});
    </script>
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
            <a href="./patientTests.php?patient_id=<?php echo $_GET["patient_id"]; ?>"> << <?php echo $patient_name; ?> </a>
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
                  <h3 class="box-title">Test visualization</h3>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                  

                <div id="container" style="height: 500px; min-width: 600px"></div>


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

    <!-- Chart libraries -->
    <script src="../../sqlite/js/highstock.js"></script>
    <script src="../../sqlite/js/modules/exporting.js"></script>
  </body>
</html>