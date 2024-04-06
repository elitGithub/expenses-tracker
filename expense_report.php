<?php
require_once 'header.php';
?>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-cls-top " role="navigation" style="margin-bottom: 0; color:#FF0">
            <div class="navbar-header" >
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html">Expense Tracker</a>
            </div>
           <!--  dddddddddd -->
 <div style="color: white;padding: 15px 50px 5px 50px;float: right;font-size: 16px;">Expense Tracker &nbsp; <div class="btn-group pull-right">
                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <i class="glyphicon glyphicon-user"></i><span class="hidden-sm hidden-xs"> </span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li></li>
                    <li>
			  <a href="#"><span class="glyphicon glyphicon-log-out"> Logout</span></a></li>

            <li class="divider"></li>

               <li> <a href="#"><i class="glyphicon glyphicon-edit"> Change Password</i></a></li>
                </ul>
            </div>
          </div>
        </nav>
           <!-- /. NAV TOP  -->
                <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
				<li class="text-center">
                    <img src="assets/img/find_user.png" class="user-image img-responsive"/>
					</li>


                    <li>
                        <a   href=" "><i class="fa fa-dashboard fa-3x"></i> Dashboard</a>
                    </li>

                      <li  >
                        <?php
                      echo" <li><a  href='index.php'><i class='fa fa-keyboard-o fa-2x'></i>Expense</a></li>";
                      ?>

                    </li>
                    <li  >


                  <?php


		echo '<li><a  href="expense_category.php"><i class="fa fa-cog fa-2x" aria-hidden="true"></i>Create Expense</a></li>';


 ?>
                     </li>

					<?php




                    echo '<li>
                        <a class="active-menu" href="#"><i class="fa fa-list  fa-2x"></i>Expense Summery<span class="fa arrow"></span></a>
                          <ul class="nav nav-second-level">
             <li>
                                <a href="expense_report.php"><i class="fa fa-file"></i>Expense Report</a>
                            </li>
                            <li>
                                <a href=" "><i class="fa fa-check-circle"></i>  </a>


                                    </li>
										  <li>
                                <a href=" "><i class="fa fa-pause"></i> </a>


                                    </li>
                                </ul>

                            </li>';


                   ?>
                </ul>
                </li>
                </ul>
                </li>
                </li>
                </li>
                </ul>
                </li>
                </ul>

            </div>

        </nav>
        <!-- /. NAV SIDE  -->
        <div id="page-wrapper" >
            <div id="page-inner">

            <div class="row">
                <div class="col-md-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Expense Details <div class="pull-right">Filter Report using the search</div>
                        </div>
        <div class="panel-body">
            <div class="table-responsive">
               <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                    <thead>
              <!-- <tr> -->
              <th>Expense Name</th>
              <th>Amount Spent</th>
              <th>Description</th>
              <th>Date</th>
              <th>Action</th>
              </tr>
    </thead>
    <tbody>


  <?php
	$sql = mysqli_query($con, "SELECT *FROM expense_category_tbl i LEFT JOIN expense_tbl s ON i.expense_category_id= s.expense_category_id
  WHERE (s.expense_category_id>0 )");
							while($row = mysqli_fetch_array($sql))
			                   	{
                            ?>
							<tr>
							<td><?php echo $row['expense_category_name'];?></td>
							<td><?php echo $row['amount_spent'];?></td>
              <td><?php echo $row['expense_description'];?></td>
              <td><?php echo $row['expense_date'];?></td>
              <td>
              <button type="button" class="btn btn-danger btn-xs" data-target="#modal_delete<?php echo $row['expense_id']?>"data-toggle='modal'><span class='glyphicon glyphicon-trash'></span> Delete</button>
             </td>

             <div class="modal fade" id="modal_delete<?php echo $row['expense_id']?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h3 class="modal-title">System</h3>
                </div>
                <div class="modal-body">
                  <center><h4>Are you sure you want to delete this expense?</h4></center>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-dismiss="modal">No</button>
                  <a type="button" class="btn btn-danger" href="delete.php?expense_id=<?php echo $row['expense_id']?>">Yes</a>
                </div>
              </div>
            </div>
          </div>
            <?php }?>

            <?php


						 ob_flush();
             mysqli_close($con);
					?>




                        </tbody>
                    </table>




                            </div>

                        </div>
                    </div>
                    <!--End Advanced Tables -->
                </div>
            </div>





                <!-- /. ROW  -->
            <div class="row"><!-- /. PAGE INNER  -->
            </div>
         <!-- /. PAGE WRAPPER  -->
     <!-- /. WRAPPER  -->
    <!-- SCRIPTS -AT THE BOTOM TO REDUCE THE LOAD TIME-->
    <!-- JQUERY SCRIPTS -->
