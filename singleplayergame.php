
<html>
  <head>
    <title>Hello World!</title>
  </head>

  <body> 

  <?php 
    echo '<table id="gameTable" style="width:100%">';

    $size = 15;

    for ($i = 0; $i<$size; $i++) {

      echo '<tr>';

      for ($j = 0; $j<$size; $j++) {
        echo '<td class="nothing" id='.($i*15+$j).'  style="width:50px">'.'.'.'</td>' ;
      }

      echo '</tr>';

    }

    echo '</table> <p id="sweets" nb=1></p> <p id="debug"></p>';

  ?>
  
    <script src="singleplayergame.js"></script>



  </body>
</html>