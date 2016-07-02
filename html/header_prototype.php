<?php
  require_once('php/database_connect.php');
  require_once('php/config.php');
  require_once('php/functions.php');
  // $sql = "select * from class_list where class_group = 'Subject'";
  // $stmt = $dbh->query($sql);
  // foreach($stmt->fetchALL(POD::FETCH_ASSOC) as $subject){
  //   var_dump($subject['name']);
  // }
  $subjects_bsit = array();
  $sql = "select * from class_list where class_group = 'Subject' and BSIT = 1";
  foreach($dbh->query($sql) as $row){
    array_push($subjects_bsit,$row);
  }

  $subjects_bscs = array();
  $sql = "select * from class_list where class_group = 'Subject' and BSCS = 1";
  foreach($dbh->query($sql) as $row){
    array_push($subjects_bscs,$row);
  }

  $subjects_bsba = array();
  $sql = "select * from class_list where class_group = 'Subject' and BSBA = 1";
  foreach($dbh->query($sql) as $row){
    array_push($subjects_bsba,$row);
  }

  $subjects_bsis = array();
  $sql = "select * from class_list where class_group = 'Subject' and BSIS = 1";
  foreach($dbh->query($sql) as $row){
    array_push($subjects_bsis,$row);
  }
  
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Header Prototype</title>

    <!-- Bootstrap -->
    <!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css" />
    <link rel="stylesheet" href="styles/flatui.min.css" />
    <link rel="stylesheet" href="styles/magnific-popup.css"> -->
    <link rel="stylesheet" href="styles/reset.css"/>
    <link rel="stylesheet" href="styles/header_prototype.css"/>
    <link rel="stylesheet" href="styles/header_prototype_font.css"/>
    <link rel="stylesheet" href="styles/fontello.css"/>
    <link rel="stylesheet" href="styles/backtotop.css"/>
    
   

     <script src="js/jquery-1.11.1.min.js"></script>
     <script src="js/backtotop.js"></script>
     <script src="js/accordion1.js"></script>
     
     <!-- <script src="js/bootstrap.min.js"></script>
    // <script src="js/flatui_modernizer.js"></script>
    // <script src="js/flatui_jquery.js"></script>
    // <script src="js/jquery.cookie.js"></script>
    // <script src="js/flatui_foundation.min.js"></script>
    // <script src="js/magnific-popup.min.js"></script> 
    // <script src="bower_components/modernizr/modernizr.js"></script> -->

   
  </head>
  <body>
    
   
    
       <header>
    
        <nav id="mainnav">
          
          <ul>
            <a href="#"><img src="images/height80.png"/></a>
            <!-- <div class="sq"><p><li><a href="#">LOGOUT</a></li></p></div> -->
            <div class="notification"><li><a href="#" class="ic icon_notification"></a></li></div>
            <div class="sq"><li><a href="#" class="ic icon_mylist">MY LIST</a></li></div>
            <div class="sq"><li><a href="#" class="ic icon_personal">PERSONAL</a></li></div>
            <div class="active sq"><li><a href="#" class="ic icon_home">HOME</a></li></div>
          </ul>
        </nav>
      </header>
     <div id="wrapper">
        <div id="side1">
          <div id="crumbs_container">
            <div id="crumbs">
              <ul>
                <li><a href="#"><p class="icon_home_white"></p></a></li>
               <!--  <li><a href="#">Welcome Page</a></li> -->
                <li><a href="#">welcome page</a></li>
              </ul>

            </div>
          </div>
          
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean ac lacus in odio pretium venenatis a in diam. Aliquam eget vehicula mauris, sed condimentum orci. Sed commodo et justo eu aliquet. Integer et pulvinar odio, ac pulvinar nisi. Donec luctus interdum purus, at pretium diam porttitor non. Aliquam non risus sed nunc vestibulum elementum nec accumsan orci. Morbi ultrices tortor quam, sit amet blandit neque eleifend ac. Maecenas pretium bibendum dolor, a adipiscing eros sollicitudin sit amet. Nullam pretium gravida aliquet. Aenean nulla felis, faucibus quis iaculis et, rhoncus faucibus ligula. Sed tristique placerat velit, non auctor lorem hendrerit ut. In ornare turpis ac diam dictum facilisis.</p><br>

            <p>Cras bibendum, dolor lacinia accumsan faucibus, est felis facilisis diam, faucibus auctor eros mauris sed erat. Nullam tempor lectus ut gravida consequat. Etiam facilisis dolor sed risus sagittis porta. Pellentesque consectetur varius porttitor. Nam diam urna, scelerisque non condimentum eget, ultrices nec dui. Morbi ornare ac est eget ultricies. Nulla non eleifend purus, vitae venenatis lorem. Phasellus tincidunt a est tempor pellentesque. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse sapien enim, feugiat id nisl vel, viverra posuere justo. Fusce eros turpis, vestibulum ut lorem vel, suscipit hendrerit risus. Nullam tempus massa ac neque auctor, eu imperdiet sapien bibendum. Proin a lectus ac orci tincidunt vestibulum non eget ipsum. Nullam volutpat vehicula suscipit. Vivamus sem mauris, ullamcorper rhoncus est in, malesuada malesuada sem. Donec id fringilla turpis.</p><br>

            <p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Duis vehicula semper viverra. Curabitur malesuada faucibus tincidunt. Curabitur fringilla justo id quam facilisis luctus. Maecenas felis neque, suscipit at aliquet nec, congue ut nunc. Vestibulum tincidunt massa sed mi sagittis, vitae ultricies lorem molestie. Maecenas malesuada faucibus libero, eget venenatis nisl varius a. Donec suscipit ligula quis pretium egestas. Ut nec sollicitudin mi, nec lacinia lacus. Nulla sed arcu ultrices, eleifend magna ac, adipiscing diam. Maecenas a varius odio, non volutpat nisl. Etiam volutpat mi urna, sed ullamcorper nunc pellentesque sit amet. Maecenas ac magna neque.</p><br>
            
        </div>
        <div id="side2">
          <ul id="accordionBox">
              <li class="mainTrigger"><h3>Course</h3>
                  <dl class="subList">
                      <dt class="subTrigger">BSIT</dt>
                      <dd><?php foreach($subjects_bsit as $subject) : ?>
                          <p><a href="#"><?php echo h($subject['class_name']); ?></a></p><br>

                          <?php endforeach; ?>
                      </dd>
                      
                      <dt class="subTrigger">BSCS</dt>
                     <dd><?php foreach($subjects_bscs as $subject) : ?>
                         <p><a href="#"><?php echo h($subject['class_name']); ?></a></p><br>

                         <?php endforeach; ?>
                     </dd>
                      <dt class="subTrigger">BSIS</dt>
                     <dd><?php foreach($subjects_bsis as $subject) : ?>
                         <p><a href="#"><?php echo h($subject['class_name']); ?></a></p><br>

                         <?php endforeach; ?>
                     </dd>
                      <dt class="subTrigger">BSBA</dt>
                      <dd><?php foreach($subjects_bsba as $subject) : ?>
                          <p><a href="#"><?php echo h($subject['class_name']); ?></a></p><br>

                          <?php endforeach; ?>
                      </dd>
                  </dl>
              </li>
              <li class="mainTrigger"><h3>Category</h3>
                  <dl class="subList">
                      <dt class="subTrigger">Sub Category 1</dt>
                      <dd><p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                      </dd>
                      <dt class="subTrigger">Sub Category 2</dt>
                      <dd><p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                      </dd>
                      <dt class="subTrigger">Sub Category 3</dt>
                      <dd><p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                          <p>Lorem ipsum dolor</p>
                      </dd>
                  </dl>
              </li>
          </ul>
          <!-- <button class="cd-popup-trigger">Click Me</button>
          <div class="cd-popup" role="alert">
              <div class="cd-popup-container">
                  <p>Are you sure you want to delete this element?</p>
                  <ul class="cd-buttons">
                      <li><a href="#0">Yes</a></li>
                      <li><a href="#0">No</a></li>
                  </ul>
                  <a href="#0" class="cd-popup-close img-replace">Close</a>
              </div> <!-- cd-popup-container -->
          </div> <!-- cd-popup --> 

        </div>
          
     
    </div>
    <footer><p>Informatics International College - Cainta Library<br><br>@ 2004 all right reserved</p></footer>
    
  

  

  <a href="#0" class="cd-top">Top</a>
  
   <script>
    $(document).ready(function(){

      // var newCrumb = $("<li><a href='#'>welcome page</a></li>");
      // newCrumb.addClass("a b c").insertAfter($("#crumbs ul:last-child"));
  

      $(".sq").mouseover(function (){
          $(this).children().css("color","white");

        });
      $(".sq").mouseleave(function(){
        $(this).css("color","rgba(41,41,41,0.85)");
      });

      //accordion
    $('.accordion dt').click(function() {
        $(this).next('dd').slideToggle();
        $(this).next('dd').siblings('dd').slideUp();
        $(this).toggleClass('open');
        $(this).siblings('dt').removeClass('open');
      });
      //---accordion end
      

    });
    </script>
  </body>
</html>