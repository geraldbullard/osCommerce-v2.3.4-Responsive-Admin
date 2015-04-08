<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/
?>
          </div>
        </div><!-- .row -->
      </div><!-- .container-fluid -->
    </div><!-- #page-wrapper -->
  </div><!-- #wrapper -->  
  <?php include(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- Bootstrap Core JavaScript -->
  <script src="assets/js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function() {
      $("#sideDiv li").click(function(e) {
        $("#sideDiv li ul").hide();
        $("#sideDiv li a").removeClass("bgblack");
        $(this).find("ul").toggle();
        //$("#sideDiv").animate({scrollTop: ($(this).offset().top)-51}, 800);
      });
      $("#sideDiv").each(function(){
        $(this).find('li ul li a').each(function(){
          var uri = '<?php echo $_SERVER["REQUEST_URI"]; ?>';
          var current = uri.split("/").pop();
          var thishref = $(this).attr("href").split("/").pop();
          // alert(current.indexOf("module=")); 
          // we need to allow for "set" to be found before "module" and act accordingly
          // also add a "not" check to the single module and set checks below. Or move
          // them into their own check afterwards (possibly more accurate)
          if ((current.indexOf("cPath=") > -1)  
            || (current.indexOf("page=") > -1)
            || (current.indexOf("module=") > -1)
            || (current.indexOf("status=") > -1)
            || (current.indexOf("oID=") > -1)
            || (current.indexOf("search=") > -1)
            || (current.indexOf("action=") > -1)
            || (current.indexOf("lngdir=") > -1)
            || (current.indexOf("aID=") > -1)
          ) {           
            var current = current.split("?")[0];          
          } else if (((current.indexOf("gID=") > -1) && (current.indexOf("&") > -1)) 
            || ((current.indexOf("set=") > -1) && (current.indexOf("&") > -1))
            || ((current.indexOf("module=") > -1) && (current.indexOf("&") > -1))
            || ((current.indexOf("oID=") > -1) && (current.indexOf("&") > -1))
            || ((current.indexOf("search=") > -1) && (current.indexOf("&") > -1))
            || ((current.indexOf("lngdir=") > -1) && (current.indexOf("&") > -1))
            || ((current.indexOf("aID=") > -1) && (current.indexOf("&") > -1))
          ) {
            var current = current.split("&")[0];
          }
          if (thishref === current) {
            $(this).closest("ul").parent().find("a:first").addClass("bgblack");
            $(this).closest("ul").removeClass("collapse").show();
            $(this).addClass("bgblack");
          }
        });
      });
    });
  </script>
</body>
</html>