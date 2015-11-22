<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title><?php echo $full_name; ?> | statstrac - open NFL statistics platform</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />

	<link rel="shortcut icon" href="assets/img/favicon.png" type="image/vnd.microsoft.icon" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	
	<!-- ================== BEGIN BASE CSS STYLE ================== -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
	<link href="assets/plugins/jquery-ui/themes/base/minified/jquery-ui.min.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
	<link href="assets/css/animate.min.css" rel="stylesheet" />
	<link href="assets/css/style.min.css" rel="stylesheet" />
	<link href="assets/css/style-responsive.min.css" rel="stylesheet" />
	<link href="assets/css/theme/default.css" rel="stylesheet" id="theme" />
	<!-- ================== END BASE CSS STYLE ================== -->
	
	<!-- ================== BEGIN PAGE LEVEL CSS STYLE ================== -->
    <link href="assets/plugins/morris/morris.css" rel="stylesheet" />
    <link href="assets/css/custom/player.css" rel="stylesheet" />
	<!-- ================== END PAGE LEVEL CSS STYLE ================== -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="assets/plugins/pace/pace.min.js"></script>
	<!-- ================== END BASE JS ================== -->
</head>
<body>
	<!-- begin #page-loader -->
	<div id="page-loader" class="fade in"><span class="spinner"></span></div>
	<!-- end #page-loader -->
	
	<!-- begin #page-container -->
	<div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
		<!-- begin #header -->
		<div id="header" class="header navbar navbar-default navbar-fixed-top navbar-inverse">
			<!-- begin container-fluid -->
			<div class="container-fluid">
				<!-- begin mobile sidebar expand / collapse button -->
				<div class="navbar-header">
					<a href="/" class="navbar-brand" style="line-height:17px;"><i class="fa fa-line-chart"></i> statstrac <small style="font-size: 10px;color: #FF7676;font-weight: bold;">BETA</small></a><small style="left: 20px;color: #fff;top: 30px;text-shadow: 1px 1px 1px #000;position: absolute;">open NFL statistics platform</small>
					<button type="button" class="navbar-toggle" data-click="sidebar-toggled">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<!-- end mobile sidebar expand / collapse button -->

				<div class="collapse navbar-collapse pull-left" id="top-navbar">
                    <ul class="nav navbar-nav">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <?php echo $season_type; ?> <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <?php echo $seasontypedropdown; ?>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <?php echo $year; ?> <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <?php echo $yearsdropdown; ?>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                Week <?php echo $week; ?> <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <?php echo $weeksdropdown; ?>
                            </ul>
                        </li>
                    </ul>
                </div>
				<!-- end navbar-collapse -->
			</div>
			<!-- end container-fluid -->
		</div>
		<!-- end #header -->
		
		<!-- begin #sidebar -->
		<div id="sidebar" class="sidebar">
			<!-- begin sidebar scrollbar -->
			<div data-scrollbar="true" data-height="100%">
				<!-- begin sidebar nav -->
				<ul class="nav">
					<li class="has-sub">
						<a href="javascript:;">
							<b class="caret pull-right"></b>
							<i class="fa fa-check-square-o"></i>
							<span>Scores</span>
						</a>
						<ul class="sub-menu">
							<li><a href="/">Current Week</a></li>
							<?php if ($lastweek) : ?>
								<li><a href="<?php echo $lastweek; ?>">Last Week</a></li>
							<?php endif; ?>
							<?php if ($lyear) : ?>
								<li><a href="<?php echo $lyear; ?>">Last Season</a></li>
							<?php endif; ?>
						</ul>
					</li>
					<li class="has-sub">
						<a href="javascript:;">
							<b class="caret pull-right"></b>
							<i class="fa fa-bar-chart-o"></i> 
							<span>Stats</span>
						</a>
						<ul class="sub-menu">
							<li><a href="stats.php?view=passing">Passing Yards</a></li>
							<li><a href="stats.php?view=rushing">Rushing Yards</a></li>
							<li><a href="stats.php?view=receiving">Receiving Yards</a></li>
						</ul>
					</li>
					<li class="has-sub">
						<a href="javascript:;">
							<b class="caret pull-right"></b>
						    <i class="fa fa-trophy"></i>
						    <span>Standings</span>
					    </a>
					    <ul class="sub-menu">
							<li class="has-sub">
								<a href="javascript:;">
						            <b class="caret pull-right"></b>
						            AFC East
						        </a>
								<ul class="sub-menu">
									<?php echo $AFCEa; ?>
								</ul>
							</li>
							<li class="has-sub">
								<a href="javascript:;">
						            <b class="caret pull-right"></b>
						            AFC North
						        </a>
								<ul class="sub-menu">
									<?php echo $AFCNa; ?>
								</ul>
							</li>
							<li class="has-sub">
								<a href="javascript:;">
						            <b class="caret pull-right"></b>
						            AFC South
						        </a>
								<ul class="sub-menu">
									<?php echo $AFCSa; ?>
								</ul>
							</li>
							<li class="has-sub">
								<a href="javascript:;">
						            <b class="caret pull-right"></b>
						            AFC West
						        </a>
								<ul class="sub-menu">
									<?php echo $AFCWa; ?>
								</ul>
							</li>
							<li class="has-sub">
								<a href="javascript:;">
						            <b class="caret pull-right"></b>
						            NFC East
						        </a>
								<ul class="sub-menu">
									<?php echo $NFCEa; ?>
								</ul>
							</li>
							<li class="has-sub">
								<a href="javascript:;">
						            <b class="caret pull-right"></b>
						            NFC North
						        </a>
								<ul class="sub-menu">
									<?php echo $NFCNa; ?>
								</ul>
							</li>
							<li class="has-sub">
								<a href="javascript:;">
						            <b class="caret pull-right"></b>
						            NFC South
						        </a>
								<ul class="sub-menu">
									<?php echo $NFCSa; ?>
								</ul>
							</li>
							<li class="has-sub">
								<a href="javascript:;">
						            <b class="caret pull-right"></b>
						            NFC West
						        </a>
								<ul class="sub-menu">
									<?php echo $NFCWa; ?>
								</ul>
							</li>
						</ul>
					</li>
					<li class="has-sub active">
						<a href="javascript:;">
							<b class="caret pull-right"></b>
							<i class="fa fa-group"></i> 
							<span>Teams</span>
						</a>
						<ul class="sub-menu">
					    	<?php echo $teamnav; ?>
						</ul>
					</li>
					<!-- begin sidebar minify button -->
					<li><a href="javascript:;" class="sidebar-minify-btn" data-click="sidebar-minify"><i class="fa fa-angle-double-left"></i></a></li>
			        <!-- end sidebar minify button -->
				</ul>
				<!-- end sidebar nav -->
			</div>
			<!-- end sidebar scrollbar -->
		</div>
		<div class="sidebar-bg"></div>
		<!-- end #sidebar -->
		
		<!-- begin #content -->
		<div id="content" class="content">
			<!-- end breadcrumb -->
			<?php foreach ($widget as $playerwidget) { echo $playerwidget; } ?>
			
		</div>
		<!-- end #content -->

		<!-- begin #footer -->
		<div id="footer" class="footer">
		    <a href="https://twitter.com/jasonbeee" target="_blank">jasonbeee</a> &copy; 2015 - statstrac is not affiliated with The NFL. All logos, names, and other trademarks are copyright of their respective owners. statstrac makes no guarantee about the accuracy or completeness of the information herein. <i style="font-weight: bold;"><?php echo $date_u; ?></i>
		</div>
		<!-- end #footer -->
		
		<!-- begin scroll to top btn -->
		<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
		<!-- end scroll to top btn -->
	</div>
	<!-- end page container -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="assets/plugins/jquery/jquery-1.9.1.min.js"></script>
	<script src="assets/plugins/jquery/jquery-migrate-1.1.0.min.js"></script>
	<script src="assets/plugins/jquery-ui/ui/minified/jquery-ui.min.js"></script>
	<script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
	<!--[if lt IE 9]>
		<script src="assets/crossbrowserjs/html5shiv.js"></script>
		<script src="assets/crossbrowserjs/respond.min.js"></script>
		<script src="assets/crossbrowserjs/excanvas.min.js"></script>
	<![endif]-->
	<script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
	<!-- ================== END BASE JS ================== -->
	
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="assets/plugins/DataTables/js/jquery.dataTables.js"></script>
	<script src="assets/plugins/DataTables/js/dataTables.tableTools.js"></script>
	<script src="assets/plugins/morris/raphael.min.js"></script>
    <script src="assets/plugins/morris/morris.js"></script>
	<script src="assets/js/apps.min.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->
	
	<script>
		$(document).ready(function() {
			App.init();
			<?php echo $score_morris_area_chart_js; ?>

			var handleDataTableDefault = function() {
				"use strict";
			    
			    if ($('table.display').length !== 0) {
			        $('table.display').DataTable({
			            "bPaginate": false,
			            "bFilter": false, 
			            "bInfo": false,
			            "aaSorting": [[1,'desc']]
			        });
			    }

			    if ($('table.display2').length !== 0) {
	        		$('table.display2').DataTable({
	            		"bPaginate": false,
	            		"bFilter": false, 
	            		"bInfo": false,
	            		"aaSorting": [],
	            		dom: 'T<"clear">lfrtip',
    					tableTools: {
    			    		"sSwfPath": "assets/plugins/DataTables/swf/copy_csv_xls_pdf.swf"
    					}
	        		});
	    		}
			};

			var TableManageDefault = function () {
				"use strict";
			    return {
			        //main function
			        init: function () {
			            handleDataTableDefault();
			        }
			    };
			}();

			TableManageDefault.init();
		});

		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', '<?php echo $uaid; ?>', 'auto');
		ga('send', 'pageview');
	</script>
</body>
</html>