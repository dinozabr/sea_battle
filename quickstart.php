<?php


    $i=0;


    $act = json_decode($_POST['data']);
    $get = $_POST['action'];
    if($act->action == 'login'){
        $a = $act->name;
          $dbconn = pg_connect("host=pg.sweb.ru port=5432 dbname=yacik1996 user=yacik1996 password=ssMorg1tt");
          if(!$dbconn){
              echo "Произошла ошибка";
              exit;
          }
          $result = pg_query_params($dbconn,'Select * FROM players WHERE  players.name = $1',array($a));
             $b = pg_num_rows ($result);

            if($b <= 0){//если юзер новый
                $result=pg_query_params($dbconn,'INSERT INTO players(name) VALUES ($1);',array($a));
                $result = pg_query_params($dbconn,'Select id,name FROM players WHERE  players.name = $1',array($a));
                $row = pg_fetch_row($result);
                $t = array(
                    'id' => $row[0],
                    'name' => $row[1]
                );
                echo json_encode($t);
            }
            else{
                $row = pg_fetch_row($result);
                $t = array(
                    'id' => $row[0],
                    'name' => $row[1]
                );
                echo json_encode($t);
            }


    }
    if($get == 'getGames'){
        $dbconn = pg_connect("host=pg.sweb.ru port=5432 dbname=yacik1996 user=yacik1996 password=ssMorg1tt");
        if(!$dbconn){
            echo "Произошла ошибка";
            exit;
        }
        $result = pg_query($dbconn,'Select id_game,name_game,status,players.name FROM games,players WHERE games.current_player_id = players.id');

        $sql =  pg_query_params($dbconn,'Select id_player1,id_player2 FROM game_players WHERE id_player1!=$1 AND id_player2!=$2',array(0,0));
        echo "<tr><td> Имя игры</td>   <td>Статус игры</td> <td>Действие</td> <td>Создатель</td></tr>";
        if(pg_num_rows($sql)>0) {

            while ($row = pg_fetch_row($result)) {

                if ($row[2] == "Play") {
                    $d = "<input type='button' value='Наблюдать' id=$row[0] class='connect' onclick = connect_to_view($row[0]) >";
                } else {
                    $d = "<input type='button' value='Играть' id=$row[0] class='connect' onclick = connect_to_game($row[0])>";
                }

                echo "<tr><td> $row[1]</td>   <td>$row[2]</td> <td>$d</td> <td>$row[3]</td></tr>";
                echo "<br />\n";
                $i++;
            }
        }else {

            while ($row = pg_fetch_row($result)) {
                if ($row[2] == "Play") {
                    $d = "<input type='button' value='Наблюдать' id=$row[0] class='connect' >";
                } else {
                    $d = "<input type='button' value='Играть' id=$row[0] class='connect' onclick = connect_to_game($row[0])>";
                }

                echo "<tr><td> $row[1]</td>   <td>$row[2]</td> <td>$d</td> <td>$row[3]</td></tr>";
                echo "<br />\n";
                $i++;
            }
        }


    }
    if($act->action == 'createGame') {


        $dbconn = pg_connect("host=pg.sweb.ru port=5432 dbname=yacik1996 user=yacik1996 password=ssMorg1tt");
        //берём id игрока, которые создал игру
        if (!$dbconn) {
            echo "Произошла ошибка";
            exit;
        }
        $result1 = pg_query_params($dbconn, 'INSERT INTO games(name_game,status,current_player_id) VALUES ($1,$2,$3);', array($act->nameGame, 'Waiting',$act->idUser));
        $result2 = pg_query_params($dbconn, 'Select id_game FROM games WHERE games.current_player_id=$1',array($act->idUser));
        $row = pg_fetch_row($result2);
        $f = '0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
        $result3 = pg_query_params($dbconn, 'INSERT INTO game_players(game_id,field,id_player1) VALUES ($1,$2,$3);', array($row[0],$f,$act->idUser));
        $t = array(
            "id_game" => $row[0],
            "f" => $f
        );
        echo json_encode($t);
        $i++;
    }
    if($act->action == 'connect') {
        $dbconn = pg_connect("host=pg.sweb.ru port=5432 dbname=yacik1996 user=yacik1996 password=ssMorg1tt");



        if (!$dbconn) {
            echo "Произошла ошибка";
            exit;
         }
        $sql = pg_query_params($dbconn,'UPDATE game_players SET id_player2=$1 WHERE game_id=$2',array($act->idUser,$act->id_game));
        $sql = pg_query_params($dbconn,'UPDATE games SET status=$1 WHERE id_game=$2',array('Play',$act->id_game));
        $res = pg_query_params($dbconn,'Select field FROM game_players WHERE game_id=$1',array($act->id_game));
        $r = pg_query_params($dbconn,'Select name FROM game_players,players WHERE game_id=$1 AND id_player1=players.id',array($act->id_game));

        $row = pg_fetch_row($res);
        $result = pg_fetch_row($r);
        $t =array(
            'id_game' => $act->id_game,
            'f' => $row[0],
            'idPlayer1' => $result[0]
        );
        echo  json_encode($t);

    }
    if($act->action == 'shot') {
        $dbconn = pg_connect("host=pg.sweb.ru port=5432 dbname=yacik1996 user=yacik1996 password=ssMorg1tt");
        $f = $act->field;
        $w = $act->coord;
        $player = $act->secondplayer;
        //если стреляет второй игрок
        if($player == "true" || $player == "TRUE"){
            $f[$w] = "2";
        }else {
            $f[$w] = "1";
        }
        $sql = pg_query_params($dbconn,'UPDATE game_players SET field=$2 WHERE game_id=$1',array($act->id_game,$f));
        $res = pg_query_params($dbconn, 'Select field FROM game_players WHERE game_id=$1',array($act->id_game));

        $row = pg_fetch_row($res);
        $t =array(
            'id_game' => $act->id_game,
            'f' => $f

        );
        echo json_encode($t);



    }
    if($act->action == 'updateField') {
        $dbconn = pg_connect("host=pg.sweb.ru port=5432 dbname=yacik1996 user=yacik1996 password=ssMorg1tt");
        $res = pg_query_params($dbconn, 'SELECT field FROM game_players WHERE game_id=$1', array($act->id_game));
        $name1 = pg_query_params($dbconn, 'SELECT name FROM game_players,players WHERE game_id=$1 AND id_player1 = players.id', array($act->id_game));
        $name2 = pg_query_params($dbconn, 'SELECT name FROM game_players,players WHERE game_id=$1 AND id_player2=players.id', array($act->id_game));
        $row = pg_fetch_row($res);
        $n1= pg_fetch_row($name1);
        $n2= pg_fetch_row($name2);
        $t =array(
            'id_game' => $act->id_game,
            'f' => $row[0],
            'idPlayer1' => $n1[0],
            'idPlayer2' => $n2[0]
        );
        echo json_encode($t);
    }
    if($act->action == 'connect_to_view') {
        $dbconn = pg_connect("host=pg.sweb.ru port=5432 dbname=yacik1996 user=yacik1996 password=ssMorg1tt");
        $res = pg_query_params($dbconn, 'SELECT field FROM game_players WHERE game_id=$1', array($act->id_game));
        $name1 = pg_query_params($dbconn, 'SELECT name FROM game_players,players WHERE game_id=$1 AND id_player1 = players.id', array($act->id_game));
        $name2 = pg_query_params($dbconn, 'SELECT name FROM game_players,players WHERE game_id=$1 AND id_player2=players.id', array($act->id_game));
        $n1= pg_fetch_row($name1);
        $n2= pg_fetch_row($name2);
        $row = pg_fetch_row($res);
        $t =array(
            'id_game' => $act->id_game,
            'f' => $row[0],
            'name1' => $n1[0],
            'name2' => $n2[0]
        );
    echo json_encode($t);
}



pg_close($dbconn);
?>
