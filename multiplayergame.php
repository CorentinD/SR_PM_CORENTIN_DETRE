
<html>
  <head>
    <title>Dat multiplayer game</title>
    <link rel="stylesheet" type="text/css" href="multiplayer.css">
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

    echo '</table> ';

    echo '<p id="score"> </p>';

    echo '<p id="log"> </p>';

  ?>
  
    <script src="multiplayergame.js"></script>



  </body>
</html>